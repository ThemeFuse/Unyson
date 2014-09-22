<?php if (!defined('FW')) die('Forbidden');

/**
 * @internal
 */
class _FW_Shortcodes_Loader
{
	/** @var FW_Shortcode[] $shortcodes */
	private static $shortcodes = array();

	public static function load()
	{
		self::load_from_extensions();

		// framework/shortcodes
		self::load_from_shortcodes_folder(array(
			'path'  => fw_get_framework_directory('/shortcodes'),
			'uri'   => fw_get_framework_directory_uri('/shortcodes')
		));

		// parent theme: framework-customizations/shortcodes
		self::load_from_shortcodes_folder(array(
			'path'  => fw_get_template_customizations_directory('/theme/shortcodes'),
			'uri'   => fw_get_template_customizations_directory_uri('/theme/shortcodes')
		));

		// child theme: framework-customizations/shortcodes
		if (is_child_theme()) {
			self::load_from_shortcodes_folder(array(
				'path'  => fw_get_stylesheet_customizations_directory('/theme/shortcodes'),
				'uri'   => fw_get_stylesheet_customizations_directory_uri('/theme/shortcodes')
			));
		}

		return self::$shortcodes;
	}

	/**
	 * Loads all shortcodes that are located in extensions
	 */
	private static function load_from_extensions()
	{
		self::load_from_extensions_recursive(fw()->extensions->get_tree());
	}

	private static function load_from_extensions_recursive($extensions)
	{
		/*
		 * Loop each extension
		 * if it has a shortcodes folder load the shortcodes from it
		 * if an extension has subextensions then recursion
		 */
		foreach ($extensions as $ext_name => $children) {
			$extension = fw()->extensions->get($ext_name);
			$rel_path  = $extension->get_rel_path();

			// framework
			$fw_path = fw_get_framework_directory('/extensions'. $rel_path . '/shortcodes');
			if (file_exists($fw_path)) {
				self::load_from_shortcodes_folder(array(
					'path'  => $fw_path,
					'uri'   => fw_get_framework_directory_uri('/extensions'. $rel_path . '/shortcodes')
				));
			}

			// parent theme framework-customizations
			$parent_fws_path = fw_get_template_customizations_directory('/extensions'. $rel_path . '/shortcodes');
			if (file_exists($parent_fws_path)) {
				self::load_from_shortcodes_folder(array(
					'path'  => $parent_fws_path,
					'uri'   => fw_get_template_customizations_directory_uri('/extensions'. $rel_path . '/shortcodes')
				));
			}

			// child theme framework-customizations
			if (is_child_theme()) {
				$child_fws_path = fw_get_stylesheet_customizations_directory('/extensions'. $rel_path . '/shortcodes');
				if (file_exists($child_fws_path)) {
					self::load_from_shortcodes_folder(array(
						'path'  => $child_fws_path,
						'uri'   => fw_get_stylesheet_customizations_directory_uri('/extensions' . $rel_path . '/shortcodes')
					));
				}
			}

			if (!empty($children)) {
				self::load_from_extensions_recursive($children);
			}
		}
	}

	/**
	 * Loads all shortcodes from a shortcodes folder located at $paths
	 *
	 * @param Array $paths  The path and uri to the shortcodes folder
	 */
	private static function load_from_shortcodes_folder($paths)
	{
		if ($dirs = glob($paths['path'] .'/*', GLOB_ONLYDIR)) {
			foreach ($dirs as $shortcode_path) {
				self::load_shortcode(array(
					'path'  => $shortcode_path,
					'uri'   => $paths['uri'] . '/' . basename($shortcode_path)
				));
			}
		}
	}

	/**
	 * Adds appropriate info about the shortcode to self::$shortcodes
	 *
	 * @param Array $paths The paths of the
	 */
	private static function load_shortcode($paths)
	{
		$path        = $paths['path'];
		$dir_name    = strtolower(basename($path));
		$class_file  = "$path/class-fw-shortcode-$dir_name.php";
		$tag         = str_replace('-', '_', $dir_name);

		// do nothing if a shortcode with the same tag already is loaded
		if (isset(self::$shortcodes[$tag])) {
			trigger_error(
				sprintf(
					__('Duplicate shortcode tag for %s found at shortcode %s', 'fw'),
					$path, self::$shortcodes[$tag]->get_path()
				),
				E_USER_WARNING
			);
			return;
		}

		$args = array(
			'tag'    => $tag,
			'path'   => $path,
			'uri'    => $paths['uri']
		);
		$custom_class_found = false;

		// try to find a custom class for the shortcode
		if (file_exists($class_file)) {
			require $class_file;

			$class_name = explode('_', $tag);
			$class_name = array_map('ucfirst', $class_name);
			$class_name = 'FW_Shortcode_' . implode('_', $class_name);

			if (!class_exists($class_name)) {
				trigger_error(
					sprintf(__('Class file found for shortcode %s but no class %s found', 'fw'), $tag, $class_name),
					E_USER_WARNING
				);
			} elseif (!is_subclass_of($class_name, 'FW_Shortcode')) {
				trigger_error(
					sprintf(__('The class %s must extend from FW_Shortcode', 'fw'), $class_name),
					E_USER_WARNING
				);
			} else {
				$shortcode_instance  = new $class_name($args);
				$custom_class_found  = true;
			}
		}

		// if no custom shortcode class found instantiate a default one
		if (!$custom_class_found) {
			$shortcode_instance = new FW_Shortcode($args);
		}

		self::$shortcodes[$tag] = $shortcode_instance;
	}
}
