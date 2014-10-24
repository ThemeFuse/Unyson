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
	 * Where this extension was declared: 'framework', 'parent' or 'child'
	 * @var string
	 */
	private $declared_source;

	/**
	 * Full path to directory where the extension was first found/declared
	 * (Search direction: framework -> parent theme -> child theme)
	 * @var string
	 */
	private $declared_dir;

	/**
	 * URI to directory where extension was first found/declared
	 * @var string
	 */
	private $declared_URI;

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
	 * If _init() was called
	 * @var bool
	 */
	private $initialized = false;

	final public function __construct(&$parent, $declared_dir, $declared_source, $URI, $depth)
	{
		if (!self::$access_key) {
			self::$access_key = new FW_Access_Key('extension');
		}

		$this->parent          =& $parent;
		$this->declared_source = $declared_source;
		$this->declared_dir    = $declared_dir;
		$this->declared_URI    = $URI . $this->get_rel_path(); // ! set $this->parent before calling get_rel_path()
		$this->depth           = $depth;

		{
			$manifest = array();

			if (file_exists($this->declared_dir .'/manifest.php')) {
				$variables = fw_get_variables_from_file($this->declared_dir .'/manifest.php', array('manifest' => array()));
				$manifest  = $variables['manifest'];
				unset($variables);
			}

			if (empty($manifest['name'])) {
				$manifest['name'] = fw_id_to_title($this->get_name());
			}

			$this->manifest = new FW_Extension_Manifest($manifest);
		}
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
	 * @return string HTML
	 */
	final protected function render_view($name, $view_variables = array())
	{
		$full_path = $this->locate_path('/views/'. $name .'.php');

		if (!$full_path) {
			trigger_error('Extension view not found: '. $name, E_USER_WARNING);
			return;
		}

		return fw_render_view($full_path, $view_variables);
	}

	/**
	 * @internal
	 */
	final public function _call_init()
	{
		if ($this->initialized) { // todo: use private key
			trigger_error('Extension already initialized: '. $this->get_name(), E_USER_ERROR);
		} else {
			$this->initialized = true;
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
	 * @return false|string    '/var/www/wordpress/wp-content/themes/theme-name/framework/extensions/<extension>/views/test.php'
	 */
	final public function locate_path($rel_path)
	{
		list(
			$search_in_framework,
			$search_in_parent_theme,
			$search_in_child_theme
		) = $this->correct_search_in_locations(
			true,
			true,
			true
		);

		$rel_path = $this->get_rel_path() . $rel_path;

		return fw()->extensions->locate_path($rel_path,
			$search_in_framework,
			$search_in_parent_theme,
			$search_in_child_theme
		);
	}

	/**
	 * @param string $rel_path E.g. '/static/js/scripts.js'
	 * @return string URI E.g. 'http: //wordpress.com/wp-content/themes/theme-name/framework/extensions/<extension>/static/js/scripts.js'
	 */
	final public function locate_URI($rel_path)
	{
		list(
			$search_in_framework,
			$search_in_parent_theme,
			$search_in_child_theme
		) = $this->correct_search_in_locations(
			true,
			true,
			true
		);

		$rel_path = $this->get_rel_path() . $rel_path;

		return fw()->extensions->locate_path_URI($rel_path,
			$search_in_framework,
			$search_in_parent_theme,
			$search_in_child_theme
		);
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
			$this->name = explode('/', $this->get_declared_path());
			$this->name = array_pop($this->name);
		}

		return $this->name;
	}

	/**
	 * @return string
	 */
	final public function get_declared_source()
	{
		return $this->declared_source;
	}

	/**
	 * @param string $append_rel_path E.g. '/includes/something.php'
	 * @return string
	 */
	final public function get_declared_path($append_rel_path = '')
	{
		return $this->declared_dir . $append_rel_path;
	}

	/**
	 * @param string $append_rel_path E.g. '/includes/foo/bar/script.js'
	 * @return string
	 */
	final public function get_declared_URI($append_rel_path = '')
	{
		return $this->declared_URI . $append_rel_path;
	}

	/**
	 * Used to determine where to search files (options, views, static files)
	 * Because there is no sense to search (one level up) in framework, if extension is declared in parent theme
	 * @param bool $search_in_framework
	 * @param bool $search_in_parent_theme
	 * @param bool $search_in_child_theme
	 * @return array
	 */
	final public function correct_search_in_locations($search_in_framework, $search_in_parent_theme, $search_in_child_theme)
	{
		$source = $this->get_declared_source();

		if ($source == 'parent') {
			$search_in_framework = false;
		} elseif ($source == 'child') {
			$search_in_framework = $search_in_parent_theme = false;
		}

		if (!is_child_theme()) {
			$search_in_child_theme = false;
		}

		return array($search_in_framework, $search_in_parent_theme, $search_in_child_theme);
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

		return $result;
	}

	/**
	 * Get path relative to parent "extensions" directory where all extensions are located
	 * E.g.: /foo/extensions/bar/extensions/hello
	 * @return string
	 */
	final public function get_rel_path()
	{
		$cache_key = $this->get_cache_key() .'/rel_path';

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$rel_path = array($this->get_name());

			// balk back in parents and generate array(child, ..., parent)
			$parent_walker = $this;
			while ($parent_walker = $parent_walker->get_parent()) {
				$rel_path[] = $parent_walker->get_name();
			}
			unset($parent_walker);

			$rel_path = '/'. implode('/extensions/', array_reverse($rel_path));

			FW_Cache::set($cache_key, $rel_path);

			return $rel_path;
		}
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
			list(
				$search_in_framework,
				$search_in_parent_theme,
				$search_in_child_theme
			) = $this->correct_search_in_locations(
				true,
				true,
				true
			);

			$rel_path = $this->get_rel_path() .'/config.php';
			$config   = array();
			$paths    = array();

			if ($search_in_framework) {
				$paths[] = fw_get_framework_directory('/extensions'. $rel_path);
			}
			if ($search_in_parent_theme) {
				$paths[] = fw_get_template_customizations_directory('/extensions'. $rel_path);
			}
			if ($search_in_child_theme) {
				$paths[] = fw_get_stylesheet_customizations_directory('/extensions'. $rel_path);
			}

			foreach ($paths as $path) {
				if (file_exists($path)) {
					$variables = fw_get_variables_from_file($path, array('cfg' => null));

					if (!empty($variables['cfg'])) {
						$config = array_merge($config, $variables['cfg']);
						unset($variables);
					}
				}
			}

			unset($path);

			FW_Cache::set($cache_key, $config);
		}

		return $key === null ? $config : fw_akg($key, $config);
	}

	/**
	 * Return array with options from specified name/path
	 * @param string $name Examples: 'framework', 'posts/portfolio'
	 * @return array
	 */
	final public function get_options($name)
	{
		$path = $this->locate_path('/options/'. $name .'.php');

		if (!$path) {
			return array();
		}

		$variables = fw_get_variables_from_file($path, array('options' => array()));

		return $variables['options'];
	}

	final public function get_settings_options()
	{
		return $this->get_options('settings');
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
}
