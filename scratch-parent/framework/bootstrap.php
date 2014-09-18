<?php if (!defined('WP_DEBUG')) die('Forbidden');
/**
 * Loads the framework
 * Include this file in theme/functions.php
 */

if (defined('FW')) {
	/**
	 * The framework is already loaded.
	 */
	return;
} else {
	/**
	 * Tells that the framework is loaded.
	 * You can check if this constant is defined to be sure the file is not accessed directly from browser.
	 */
	define('FW', true);
}

if (!function_exists('_action_init_framework')):

	add_action('after_setup_theme', '_action_init_framework');

	function _action_init_framework() {
		remove_action('after_setup_theme', '_action_init_framework');

		include dirname(__FILE__) .'/bootstrap-helpers.php';
		include dirname(__FILE__) .'/deprecated.php';

		/**
		 * Load core
		 */
		{
			require fw_get_framework_directory('/core/Fw.php');

			fw();
		}

		/**
		 * Load helpers
		 */
		foreach (
			array(
				'meta',
				'class-fw-access-key',
				'class-fw-dumper',
				'general',
				'class-fw-wp-filesystem',
				'class-fw-cache',
				'class-fw-form',
				'class-fw-request',
				'class-fw-session',
				'class-fw-wp-option',
				'class-fw-wp-post-meta',
				'database',
				'class-fw-flash-messages',
				'class-fw-resize',
			)
			as $file
		) {
			require fw_get_framework_directory('/helpers/'. $file .'.php');
		}

		/**
		 * Load (includes) other functionality
		 */
		foreach (
			array(
				'hooks',
				'option-types',
			)
			as $file
		) {
			require fw_get_framework_directory('/includes/'. $file .'.php');
		}

		/**
		 * Init components
		 */
		foreach (fw() as $component_name => $component) {
			if ($component_name === 'manifest')
				continue;

			/** @var FW_Component $component */
			$component->_call_init();
		}

		/**
		 * For Flash Message Helper:
		 * just start session before headers sent
		 * to prevent: Warning: session_start(): Cannot send session cookie - headers already sent, if flash added to late
		 */
		FW_Session::get(-1);

		do_action('fw_init');
	}

endif;
