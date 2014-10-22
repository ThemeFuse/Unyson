<?php if (!defined('FW')) die('Forbidden');

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
			if (is_child_theme()) {
				return get_stylesheet_directory() . '/framework-customizations' . $rel_path;
			} else {
				// check is_child_theme() before using this function
				return null;
			}
		}

		/**
		 * URI to the child-theme/framework-customizations directory
		 */
		function fw_get_stylesheet_customizations_directory_uri($rel_path = '') {
			if (is_child_theme()) {
				return get_stylesheet_directory_uri() . '/framework-customizations' . $rel_path;
			} else {
				// check is_child_theme() before using this function
				return null;
			}
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
			return apply_filters('fw_framework_directory', get_template_directory() .'/framework') . $rel_path;
		}

		/**
		 * URI to the parent-theme/framework directory
		 */
		function fw_get_framework_directory_uri($rel_path = '') {
			return apply_filters('fw_framework_directory_uri', get_template_directory_uri() .'/framework') . $rel_path;
		}
	}
}