<?php if (!defined('ABSPATH')) die('Forbidden');

if ( defined( 'WP_CLI' ) && WP_CLI && ! isset( $_SERVER['HTTP_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = 'unyson.io';
	$_SERVER['SERVER_NAME'] = 'unyson';
	$_SERVER['SERVER_PORT'] = '80';
}

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

		$dir = dirname(__FILE__);

		require $dir .'/autoload.php';

		// Load helper functions
		foreach (array('general', 'meta', 'fw-storage', 'database') as $file) {
			require $dir .'/helpers/'. $file .'.php';
		}

		// Load core
		{
			require $dir .'/core/Fw.php';

			fw();
		}

		require $dir .'/includes/hooks.php';

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
