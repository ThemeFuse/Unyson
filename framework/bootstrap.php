<?php if (!defined('ABSPATH')) die('Forbidden');

if (defined('FW')) {
	/**
	 * The framework is already loaded.
	 */
} else {
	define('FW', true);

	/**
	 * Load the framework on 'after_setup_theme' action when the theme information is available
	 * To prevent `undefined constant TEMPLATEPATH` errors when the framework is used as plugin
	 */
	add_action('after_setup_theme', '_action_init_framework');

	function _action_init_framework() {
		if (did_action('fw_init')) {
			return;
		}

		do_action('fw_before_init');

		$fw_dir = dirname(__FILE__);

		include $fw_dir .'/bootstrap-helpers.php';

		// these are required when fw() is executed below
		{
			require $fw_dir .'/helpers/class-fw-dumper.php';
			require $fw_dir .'/helpers/general.php';
			require $fw_dir .'/helpers/class-fw-cache.php';
		}

		// Load core
		{
			require $fw_dir .'/core/Fw.php';

			fw();
		}

		// Helpers
		{
			// Autoload helper classes
			function _fw_autoload_helper_classes($class) {
				static $class_to_file = array(
					'FW_Access_Key' => 'class-fw-access-key',
					'FW_WP_Filesystem' => 'class-fw-wp-filesystem',
					'FW_File_Cache' => 'class-fw-file-cache',
					'FW_Form' => 'class-fw-form',
					'FW_Settings_Form' => 'class-fw-settings-form',
					'FW_Request' => 'class-fw-request',
					'FW_Session' => 'class-fw-session',
					'FW_WP_Option' => 'class-fw-wp-option',
					'FW_WP_Meta' => 'class-fw-wp-meta',
					'FW_Db_Options_Model' => 'class-fw-db-options-model',
					'FW_Flash_Messages' => 'class-fw-flash-messages',
					'FW_Resize' => 'class-fw-resize',
					'FW_WP_List_Table' => 'class-fw-wp-list-table',
					'FW_Type' => 'type/class-fw-type',
					'FW_Type_Register' => 'type/class-fw-type-register',
				);

				if (isset($class_to_file[$class])) {
					require dirname(__FILE__) .'/helpers/'. $class_to_file[$class] .'.php';
				}
			}
			spl_autoload_register('_fw_autoload_helper_classes');

			// Load helper functions
			foreach (array('meta', 'fw-storage', 'database') as $file) {
				require $fw_dir .'/helpers/'. $file .'.php';
			}
		}

		require $fw_dir .'/includes/hooks.php';

		/**
		 * Init components
		 */
		{
			$components = array(
				/**
				 * Load the theme's hooks.php first, to give users the possibility to add_action()
				 * for `extensions` and `backend` components actions that can happen while their initialization
				 */
				'theme',
				/**
				 * Load extensions before backend, to give extensions the possibility to add_action()
				 * for the `backend` component actions that can happen while its initialization
				 */
				'extensions',
				'backend'
			);

			foreach ($components as $component) {
				fw()->{$component}->_init();
			}

			foreach ($components as $component) {
				fw()->{$component}->_after_components_init();
			}
		}

		/**
		 * The framework is loaded
		 */
		do_action('fw_init');
	}
}
