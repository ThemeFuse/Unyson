<?php if (!defined('FW')) die('Forbidden');

/**
 * Helper functions used while loading the framework
 */

/**
 * Convert to Unix style directory separators
 */
function fw_fix_path($path) {
	$fixed_path = untrailingslashit( str_replace(array('//', '\\'), array('/', '/'), $path) );

	if (empty($fixed_path) && !empty($path)) {
		$fixed_path = '/';
	}

	return $fixed_path;
}

/**
 * Relative path of the framework customizations directory
 * @param string $append
 * @return string
 */
function fw_get_framework_customizations_dir_rel_path($append = '') {
	static $cache = null;

	if ($cache === null) {
		$cache = apply_filters('fw_framework_customizations_dir_rel_path', '/framework-customizations');
	}

	return $cache . $append;
}

/** Child theme related functions */
{
	/**
	 * Full path to the child-theme framework customizations directory
	 * @param string $rel_path
	 * @return null|string
	 */
	function fw_get_stylesheet_customizations_directory($rel_path = '') {
		if (is_child_theme()) {
			return get_stylesheet_directory() . fw_get_framework_customizations_dir_rel_path($rel_path);
		} else {
			// check is_child_theme() before using this function
			return null;
		}
	}

	/**
	 * URI to the child-theme framework customizations directory
	 * @param string $rel_path
	 * @return null|string
	 */
	function fw_get_stylesheet_customizations_directory_uri($rel_path = '') {
		if (is_child_theme()) {
			return get_stylesheet_directory_uri() . fw_get_framework_customizations_dir_rel_path($rel_path);
		} else {
			// check is_child_theme() before using this function
			return null;
		}
	}
}

/** Parent theme related functions */
{
	/**
	 * Full path to the parent-theme framework customizations directory
	 * @param string $rel_path
	 * @return string
	 */
	function fw_get_template_customizations_directory($rel_path = '') {
		static $cache = null;

		if ($cache === null) {
			$cache = get_template_directory() . fw_get_framework_customizations_dir_rel_path();
		}

		return $cache . $rel_path;
	}

	/**
	 * URI to the parent-theme framework customizations directory
	 * @param string $rel_path
	 * @return string
	 */
	function fw_get_template_customizations_directory_uri($rel_path = '') {
		static $cache = null;

		if ($cache === null) {
			$cache = get_template_directory_uri() . fw_get_framework_customizations_dir_rel_path();
		}

		return $cache . $rel_path;
	}
}

/** Framework related functions */
{
	/**
	 * Full path to the parent-theme/framework directory
	 * @param string $rel_path
	 * @return string
	 */
	function fw_get_framework_directory($rel_path = '') {
		static $cache = null;

		if ($cache === null) {
			$cache = apply_filters('fw_framework_directory', dirname(__FILE__));
		}

		return $cache . $rel_path;
	}

	/**
	 * URI to the parent-theme/framework directory
	 * @param string $rel_path
	 * @return string
	 */
	function fw_get_framework_directory_uri($rel_path = '') {
		static $cache = null;

		if ($cache === null) {
			$cache = apply_filters('fw_framework_directory_uri', get_template_directory_uri() . '/framework');
		}

		return $cache . $rel_path;
	}
}
