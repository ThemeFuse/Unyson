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
	try {
		$dir = FW_Cache::get($cache_key = 'fw_customizations_dir_rel_path');
	} catch (FW_Cache_Not_Found_Exception $e) {
		FW_Cache::set(
			$cache_key,
			$dir = apply_filters('fw_framework_customizations_dir_rel_path', '/framework-customizations')
		);
	}

	return $dir . $append;
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
		try {
			$dir = FW_Cache::get($cache_key = 'fw_template_customizations_dir');
		} catch (FW_Cache_Not_Found_Exception $e) {
			FW_Cache::set(
				$cache_key,
				$dir = get_template_directory() . fw_get_framework_customizations_dir_rel_path()
			);
		}

		return $dir . $rel_path;
	}

	/**
	 * URI to the parent-theme framework customizations directory
	 * @param string $rel_path
	 * @return string
	 */
	function fw_get_template_customizations_directory_uri($rel_path = '') {
		try {
			$dir = FW_Cache::get($cache_key = 'fw_template_customizations_dir_uri');
		} catch (FW_Cache_Not_Found_Exception $e) {
			FW_Cache::set(
				$cache_key,
				$dir = get_template_directory_uri() . fw_get_framework_customizations_dir_rel_path()
			);
		}

		return $dir . $rel_path;
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
		try {
			$dir = FW_Cache::get($cache_key = 'fw_framework_dir');
		} catch (FW_Cache_Not_Found_Exception $e) {
			FW_Cache::set(
				$cache_key,
				$dir = apply_filters('fw_framework_directory', dirname(__FILE__))
			);
		}

		return $dir . $rel_path;
	}

	/**
	 * URI to the parent-theme/framework directory
	 * @param string $rel_path
	 * @return string
	 */
	function fw_get_framework_directory_uri($rel_path = '') {
		try {
			$dir = FW_Cache::get($cache_key = 'fw_framework_dir_uri');
		} catch (FW_Cache_Not_Found_Exception $e) {
			FW_Cache::set(
				$cache_key,
				$dir = apply_filters('fw_framework_directory_uri', get_template_directory_uri() . '/framework')
			);
		}

		return $dir . $rel_path;
	}
}
