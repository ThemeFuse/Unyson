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

		/**
		 * Load core
		 */
		{
			require $fw_dir .'/core/Fw.php';

			fw();
		}

		/**
		 * Load helpers
		 */
		foreach (
			array(
				'meta',
				'class-fw-access-key',
				// 'class-fw-dumper', // included below
				// 'general', // included below
				'class-fw-wp-filesystem',
				// 'class-fw-cache', // included below
				'class-fw-file-cache',
				'class-fw-form',
				'class-fw-request',
				'class-fw-session',
				'class-fw-wp-option',
				'class-fw-wp-meta',
				'class-fw-db-options-model',
				'fw-storage',
				'database',
				'class-fw-flash-messages',
				'class-fw-resize',
				'class-fw-wp-list-table',
				'type/class-fw-type',
				'type/class-fw-type-register',
			)
			as $file
		) {
			require $fw_dir .'/helpers/'. $file .'.php';
		}

		/**
		 * Load includes
		 */
		foreach (array('hooks') as $file) {
			require $fw_dir .'/includes/'. $file .'.php';
		}

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
