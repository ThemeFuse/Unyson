<?php if (!defined('WP_DEBUG')) die('Forbidden');
/**
 * Loads the framework
 * Include this file in theme/functions.php
 */

/**
 * Tells that the framework is loaded.
 * You can check if this constant is defined to be sure the file is not accessed directly from browser.
 */
define('FW', true);

/**
 * Helper functions used while loading the framework
 */
{
	/**
	 * Convert to Unix style directory separators
	 */
	function fw_fix_path($path) {
		return str_replace(array('//', '\\'), array('/', '/'), $path);
	}

	/** Child theme related functions */
	{
		/**
		 * Full path to the child-theme/framework-customizations directory
		 */
		function fw_get_stylesheet_customizations_directory($rel_path = '') {
			return get_stylesheet_directory() .'/framework-customizations'. $rel_path;
		}

		/**
		 * URI to the child-theme/framework-customizations directory
		 */
		function fw_get_stylesheet_customizations_directory_uri($rel_path = '') {
			return get_stylesheet_directory_uri() .'/framework-customizations'. $rel_path;
		}
	}

	/** Parent theme related functions */
	{
		/**
		 * Full path to the parent-theme/framework-customizations directory
		 */
		function fw_get_template_customizations_directory($rel_path = '') {
			return get_template_directory() .'/framework-customizations'. $rel_path;
		}

		/**
		 * URI to the parent-theme/framework-customizations directory
		 */
		function fw_get_template_customizations_directory_uri($rel_path = '') {
			return get_template_directory_uri() .'/framework-customizations'. $rel_path;
		}
	}

	/** Framework related functions */
	{
		/**
		 * Full path to the parent-theme/framework directory
		 */
		function fw_get_framework_directory($rel_path = '') {
			return get_template_directory() .'/framework'. $rel_path;
		}

		/**
		 * URI to the parent-theme/framework directory
		 */
		function fw_get_framework_directory_uri($rel_path = '') {
			return get_template_directory_uri() .'/framework'. $rel_path;
		}
	}
}

include dirname(__FILE__) .'/deprecated-constants.php';

/**
 * Load core
 */
{
	require fw_get_framework_directory('/core/Fw.php');

	/**
	 * @return _FW Framework instance
	 */
	function fw() {
		static $FW = null; // cache

		if ($FW === null) {
			$FW = new _Fw();
		}

		return $FW;
	}

	fw();
}

/**
 * Load helpers
 */
foreach (
	array(
		'post-meta',
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
