<?php if (!defined('FW')) die('Forbidden');

/**
 * All framework extensions should extend this
 */
abstract class FW_Extension
{
	/**
	 * Called after all extensions instances was created
	 * @internal
	 */
	abstract protected function _init();

	/** @var FW_Extension_Manifest */
	public $manifest;

	/** @var string Key used in FW_Cache to store data about extensions */
	private static $cache_key = 'fw_ext';

	/** @var FW_Access_Key */
	private static $access_key;

	/**
	 * Extension name, equal to directory name
	 * @var string
	 */
	private $name;

	/**
	 * Parent extension instance
	 * @var FW_Extension|null
	 */
	private $parent;

	/**
	 * @var string
	 */
	private $rel_path;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string
	 */
	private $uri;

	/**
	 * On what directory depth is the extension
	 *
	 * 1 - Root extension
	 * 2 - Their children
	 * 3 - Sub children
	 * ...
	 *
	 * @var int
	 */
	private $depth;

	/**
	 * Locations where the extension can look for customizations (overwrite views, options; extend config)
	 * @var array {'/path' => 'https://uri.to/path'}
	 */
	private $customizations_locations;

	final public function __construct($data)
	{
		if (!self::$access_key) {
			self::$access_key = new FW_Access_Key('extension');
		}

		$this->rel_path = $data['rel_path'];
		$this->path     = $data['path'];
		$this->uri      = $data['uri'];
		$this->parent   = $data['parent'];
		$this->depth    = $data['depth'];
		$this->customizations_locations = $data['customizations_locations'];
		$this->manifest = _FW_Component_Extensions::_get_manifest($this->get_name(), self::$access_key);
	}

	/**
	 * Cache key for this extension
	 *
	 * Usage:
	 * FW_Cache::get( $this->get_cache_key('/some/key') )
	 *
	 * @param string $sub_key
	 * @return string
	 */
	final public function get_cache_key($sub_key = '')
	{
		return self::$cache_key .'/'. $this->get_name() . $sub_key;
	}

	/**
	 * @param string $name View file name (without .php) from <extension>/views directory
	 * @param  array $view_variables Keys will be variables names within view
	 * @param   bool $return In some cases, for memory saving reasons, you can disable the use of output buffering
	 * @return string HTML
	 */
	final protected function render_view($name, $view_variables = array(), $return = true)
	{
		$full_path = $this->locate_path('/views/'. $name .'.php');

		if (!$full_path) {
			trigger_error('Extension view not found: '. $name, E_USER_WARNING);
			return;
		}

		return fw_render_view($full_path, $view_variables, $return);
	}

	/**
	 * @internal
	 * @param FW_Access_Key $access_key
	 * @return mixed
	 */
	final public function _call_init($access_key)
	{
		if ($access_key->get_key() !== 'fw_extensions') {
			trigger_error(__METHOD__ .' denied', E_USER_ERROR);
		}

		return $this->_init();
	}

	/**
	 * Tree array with all sub extensions
	 * @return array
	 */
	final public function get_tree()
	{
		return fw()->extensions->_get_extension_tree(self::$access_key, $this->get_name());
	}

	/**
	 * @param string $rel_path '/views/test.php'
	 * @return false|string    '/var/www/.../extensions/<extension>/views/test.php'
	 */
	final public function locate_path($rel_path)
	{
		$locations = $this->customizations_locations;
		$locations[$this->get_path()] = $this->get_uri();

		foreach ($locations as $path => $uri) {
			if (file_exists($path . $rel_path)) {
				return $path . $rel_path;
			}
		}

		return false;
	}

	/**
	 * @param string $rel_path E.g. '/static/js/scripts.js'
	 * @return string URI E.g. 'http: //wordpress.com/.../extensions/<extension>/static/js/scripts.js'
	 */
	final public function locate_URI($rel_path)
	{
		$locations = $this->customizations_locations;
		$locations[$this->get_path()] = $this->get_uri();

		foreach ($locations as $path => $uri) {
			if (file_exists($path . $rel_path)) {
				return $uri . $rel_path;
			}
		}

		return false;
	}

	/**
	 * @return FW_Extension|null if has no parent extension
	 */
	final public function get_parent()
	{
		return $this->parent;
	}

	/**
	 * @return string
	 */
	final public function get_name()
	{
		if ($this->name === null) {
			$this->name = basename($this->path);
		}

		return $this->name;
	}

	/**
	 * @return string
	 * @deprecated
	 */
	final public function get_declared_source()
	{
		return 'deprecated';
	}

	/**
	 * @param string $append_rel_path E.g. '/includes/something.php'
	 * @return string
	 * @deprecated
	 */
	final public function get_declared_path($append_rel_path = '')
	{
		return $this->get_path($append_rel_path);
	}

	final public function get_path($append_rel_path = '')
	{
		return $this->path . $append_rel_path;
	}

	/**
	 * @param string $append_rel_path E.g. '/includes/foo/bar/script.js'
	 * @return string
	 * @deprecated
	 */
	final public function get_declared_URI($append_rel_path = '')
	{
		return $this->get_uri($append_rel_path);
	}

	/**
	 * @param string $append_rel_path E.g. '/includes/foo/bar/script.js'
	 * @return string
	 */
	final public function get_uri($append_rel_path = '')
	{
		return $this->uri . $append_rel_path;
	}

	/**
	 * @param string $child_extension_name
	 * @return FW_Extension|null
	 */
	final public function get_child($child_extension_name)
	{
		$active_tree = $this->get_tree();

		if (isset($active_tree[$child_extension_name])) {
			return fw()->extensions->get($child_extension_name);
		} else {
			return null;
		}
	}

	/**
	 * Return all child extensions
	 * Only one level, not all sub levels
	 * @return FW_Extension[]
	 */
	final public function get_children()
	{
		$active_tree = $this->get_tree();

		$result = array();

		foreach ($active_tree as $extension_name => &$sub_extensions) {
			$result[$extension_name] = fw()->extensions->get($extension_name);
		}
		unset($sub_extensions);

		return $result;
	}

	/**
	 * Return config key value, or entire config array
	 * Config array is merged from child configs
	 * @param string|null $key Multi key format accepted: 'a/b/c'
	 * @return mixed|null
	 */
	final public function get_config($key = null)
	{
		$cache_key = $this->get_cache_key() .'/config';

		try {
			$config = FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$config = array();

			$locations = $this->customizations_locations;
			$locations[$this->get_path()] = $this->get_uri();

			foreach (array_reverse($locations) as $path => $uri) {
				$config_path = $path .'/config.php';

				if (file_exists($config_path)) {
					$variables = fw_get_variables_from_file($config_path, array('cfg' => null));

					if (!empty($variables['cfg'])) {
						$config = array_merge($config, $variables['cfg']);
						unset($variables);
					}
				}
			}

			FW_Cache::set($cache_key, $config);
		}

		return $key === null ? $config : fw_call( fw_akg( $key, $config ) );
	}

	/**
	 * Return array with options from specified name/path
	 * @param string $name Examples: 'framework', 'posts/portfolio'
	 * @param array $variables These will be available in options file (like variables for view)
	 * @return array
	 */
	final public function get_options($name, array $variables = array())
	{
		try {
			return FW_Cache::get($cache_key = $this->get_cache_key('/options/'. $name));
		} catch (FW_Cache_Not_Found_Exception $e) {
			if ($path = $this->locate_path('/options/'. $name .'.php')) {
				$variables = fw_get_variables_from_file($path, array('options' => array()), $variables);
			} else {
				$variables = array('options' => array());
			}

			FW_Cache::set($cache_key, $variables['options']);

			return $variables['options'];
		}
	}

	final public function get_settings_options()
	{
		try {
			return FW_Cache::get($cache_key = $this->get_cache_key('/settings_options'));
		} catch (FW_Cache_Not_Found_Exception $e) {
			if (file_exists($path = $this->get_path('/settings-options.php'))) {
				$variables = fw_get_variables_from_file($path, array('options' => array()));
			} else {
				$variables = array('options' => array());
			}

			FW_Cache::set($cache_key, $variables['options']);

			return $variables['options'];
		}
	}

	/**
	 * @since 2.6.9
	 */
	final public function get_rendered_docs() {
		$docs_path = $this->get_path('/readme.md.php');

		if (! file_exists($docs_path)) {
			return false;
		}

		return fw()->backend->get_markdown_parser()->text(
			/**
			 * TODO: Perhaps send here some values in order to make extension docs
			 * more dynamic???
			 */
			fw_render_view($docs_path, array())
		);
	}

	/**
	 * Get extension's settings option value from the database
	 *
	 * @param string|null $option_id
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
	 *
	 * @return mixed|null
	 */
	final public function get_db_settings_option( $option_id = null, $default_value = null, $get_original_value = null ) {
		return fw_get_db_ext_settings_option( $this->get_name(), $option_id, $default_value, $get_original_value );
	}

	/**
	 * Set extension's setting option value in database
	 *
	 * @param string|null $option_id
	 * @param mixed $value
	 */
	final public function set_db_settings_option( $option_id = null, $value ) {
		fw_set_db_ext_settings_option( $this->get_name(), $option_id, $value );
	}

	/**
	 * Get extension's data from the database
	 *
	 * @param string|null $multi_key The key of the data you want to get. null - all data
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
	 *
	 * @return mixed|null
	 */
	final public function get_db_data( $multi_key = null, $default_value = null, $get_original_value = null ) {
		return fw_get_db_extension_data( $this->get_name(), $multi_key, $default_value, $get_original_value );
	}

	/**
	 * Set some extension's data in database
	 *
	 * @param string|null $multi_key The key of the data you want to set. null - all data
	 * @param mixed $value
	 */
	final public function set_db_data( $multi_key = null, $value ) {
		fw_set_db_extension_data( $this->get_name(), $multi_key, $value );
	}

	/**
	 * Get extension's data from user meta
	 *
	 * @param int $user_id
	 * @param string|null $keys
	 *
	 * @return mixed|null
	 */
	final public function get_user_data( $user_id, $keys = null ) {
		return fw_get_db_extension_user_data($user_id, $this->get_name(), $keys);
	}

	/**
	 * et some extension's data in user meta
	 *
	 * @param int $user_id
	 * @param mixed $value
	 * @param string|null $keys
	 *
	 * @return bool|int
	 */
	final public function set_user_data( $user_id, $value, $keys = null ) {
		return fw_set_db_extension_user_data($user_id, $this->get_name(), $value, $keys);
	}

	final public function get_post_options($post_type)
	{
		return $this->get_options('posts/'. $post_type);
	}

	final public function get_taxonomy_options($taxonomy)
	{
		return $this->get_options('taxonomies/'. $taxonomy);
	}

	/**
	 * @param string $name File name without extension, located in <extension>/static/js/$name.js
	 * @return string URI
	 */
	final public function locate_js_URI($name)
	{
		return $this->locate_URI('/static/js/'. $name .'.js');
	}

	/**
	 * @param string $name File name without extension, located in <extension>/static/js/$name.js
	 * @return string URI
	 */
	final public function locate_css_URI($name)
	{
		return $this->locate_URI('/static/css/'. $name .'.css');
	}

	/**
	 * @param string $name File name without extension, located in <extension>/views/$name.php
	 * @return false|string
	 */
	final public function locate_view_path($name)
	{
		return $this->locate_path('/views/'. $name .'.php');
	}

	final public function get_depth()
	{
		return $this->depth;
	}

	final public function get_customizations_locations()
	{
		return $this->customizations_locations;
	}

	final public function get_rel_path()
	{
		return $this->rel_path;
	}

	/**
	 * Check if child extension is valid
	 *
	 * Used for special cases when an extension requires its child extensions to extend some special class
	 *
	 * @param FW_Extension $child_extension_instance
	 * @return bool
	 * @internal
	 */
	public function _child_extension_is_valid($child_extension_instance)
	{
		return is_subclass_of($child_extension_instance, 'FW_Extension');
	}

	/**
	 * Get link to the page created by this extension in dashboard
	 * (Used on the extensions page)
	 * @internal
	 * @return string
	 */
	public function _get_link()
	{
		return false;
	}
}
