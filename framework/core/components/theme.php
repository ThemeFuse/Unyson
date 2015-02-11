<?php if (!defined('FW')) die('Forbidden');

/**
 * Theme Component
 * Works with framework-customizations/theme directory
 */
final class _FW_Component_Theme
{
	private static $cache_key = 'fw_theme';

	/**
	 * @var FW_Theme_Manifest
	 */
	public $manifest;

	public function __construct()
	{
		{
			$manifest = array();

			@include fw_get_template_customizations_directory('/theme/manifest.php');

			$this->manifest = new FW_Theme_Manifest($manifest);
		}
	}

	/**
	 * @internal
	 */
	public function _init()
	{
		add_action('fw_extensions_init', array($this, '_action_fw_extensions_init'));
	}

	/**
	 * @internal
	 */
	public function _after_components_init()
	{
	}

	/**
	 * Search relative path in: child theme -> parent "theme" directory and return full path
	 * @param string $rel_path
	 * @return false|string
	 */
	public function locate_path($rel_path)
	{
		if (is_child_theme() && file_exists(fw_get_stylesheet_customizations_directory('/theme'. $rel_path))) {
			return fw_get_stylesheet_customizations_directory('/theme'. $rel_path);
		}

		if (file_exists(fw_get_template_customizations_directory('/theme'. $rel_path))) {
			return fw_get_template_customizations_directory('/theme'. $rel_path);
		}

		return false;
	}

	/**
	 * Return array with options from specified name/path
	 * @param string $name
	 * @return array
	 */
	public function get_options($name)
	{
		$path = $this->locate_path('/options/'. $name .'.php');

		if (!$path) {
			return array();
		}

		$variables = fw_get_variables_from_file($path, array('options' => array()));

		return $variables['options'];
	}

	public function get_settings_options()
	{
		$cache_key = self::$cache_key .'/options/settings';

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$options = apply_filters('fw_settings_options', $this->get_options('settings'));

			FW_Cache::set($cache_key, $options);

			return $options;
		}
	}

	public function get_post_options($post_type)
	{
		$cache_key = self::$cache_key .'/options/posts/'. $post_type;

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$options = apply_filters('fw_post_options', $this->get_options('posts/'. $post_type), $post_type);

			FW_Cache::set($cache_key, $options);

			return $options;
		}
	}

	public function get_taxonomy_options($taxonomy)
	{
		$cache_key = self::$cache_key .'/options/taxonomies/'. $taxonomy;

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$options = apply_filters('fw_taxonomy_options', $this->get_options('taxonomies/'. $taxonomy),
				$taxonomy,
				null
			);

			FW_Cache::set($cache_key, $options);

			return $options;
		}
	}

	/**
	 * Return config key value, or entire config array
	 * Config array is merged from child configs
	 * @param string|null $key Multi key format accepted: 'a/b/c'
	 * @param mixed $default_value
	 * @return mixed|null
	 */
	final public function get_config($key = null, $default_value = null)
	{
		$cache_key = self::$cache_key .'/config';

		try {
			$config = FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$config = array(
				/** Toggle Theme Settings form ajax submit */
				'settings_form_ajax_submit' => true,
				/** Toggle Theme Settings side tabs */
				'settings_form_side_tabs' => false,
			);

			if (file_exists(fw_get_template_customizations_directory('/theme/config.php'))) {
				$variables = fw_get_variables_from_file(fw_get_template_customizations_directory('/theme/config.php'), array('cfg' => null));

				if (!empty($variables['cfg'])) {
					$config = array_merge($config, $variables['cfg']);
					unset($variables);
				}
			}

			if (is_child_theme() && file_exists(fw_get_stylesheet_customizations_directory('/theme/config.php'))) {
				$variables = fw_get_variables_from_file(fw_get_stylesheet_customizations_directory('/theme/config.php'), array('cfg' => null));

				if (!empty($variables['cfg'])) {
					$config = array_merge($config, $variables['cfg']);
					unset($variables);
				}
			}

			unset($path);

			FW_Cache::set($cache_key, $config);
		}

		return $key === null ? $config : fw_akg($key, $config, $default_value);
	}

	/**
	 * @internal
	 */
	public function _action_fw_extensions_init()
	{
		if (is_admin() && !fw()->theme->manifest->check_requirements()) {
			FW_Flash_Messages::add(
				'fw_theme_requirements',
				__('Theme requirements not met:', 'fw') .' '. fw()->theme->manifest->get_not_met_requirement_text(),
				'warning'
			);
		}
	}
}
