<?php if (!defined('FW')) die('Forbidden');

/**
 * Extensions component
 */
final class _FW_Component_Extensions
{
	/**
	 * All existing extensions
	 * @var FW_Extension[] { 'extension_name' => instance }
	 */
	private static $all_extensions = array();

	/**
	 * All existing extensions names arranged in hierarchical tree like they are in directories
	 * @var array
	 */
	private static $all_extensions_tree = array();

	/**
	 * Active extensions
	 *
	 * On every extension activation, it will be pushed at the end of this array.
	 * The extensions order is important when including files.
	 * If extension A requires extension B, extension B is activated before extension A,
	 * and all files of the extension B (hooks.php, static.php, etc.) must be included before extension A
	 * For e.g. extension A may have in static.php:
	 * wp_enqueue_script( 'ext-A-script', 'script.js', array( 'ext-B-script' ) );
	 * so 'ext-B-script' must be registered before 'ext-A-script'
	 *
	 * @var FW_Extension[] { 'extension_name' => instance }
	 */
	private static $active_extensions = array();

	/**
	 * Active extensions names arranged in hierarchical tree like they are in directories
	 * @var array
	 */
	private static $active_extensions_tree = array();

	/**
	 * @var array { 'extension_name' => array('required_by', 'required_by') }
	 */
	private static $extensions_required_by_extensions = array();

	/**
	 * @var array { 'extension_name' => &array() }
	 */
	private static $extension_to_all_tree = array();

	/**
	 * @var array { 'extension_name' => &array() }
	 */
	private static $extension_to_active_tree = array();

	/**
	 * @var FW_Access_Key
	 */
	private static $access_key;

	/**
	 * @var null|_FW_Extensions_Manager
	 */
	public $manager;

	/**
	 * Option name that stores the active extensions array
	 * @internal
	 */
	public function _get_active_extensions_db_option_name()
	{
		return 'fw_active_extensions';
	}

	/**
	 * @param null|string $extension_name Check if an extension is set as active in database
	 * @internal
	 * @return array|bool
	 */
	public function _get_db_active_extensions($extension_name = null)
	{
		$extensions = get_option($this->_get_active_extensions_db_option_name(), array());

		if ($extension_name) {
			return isset($extensions[$extension_name]);
		} else {
			return $extensions;
		}
	}

	public function __construct()
	{
		require dirname(__FILE__) .'/extensions/class-fw-extension-default.php';

		if (is_admin()) {
			require dirname(__FILE__) .'/extensions/manager/class--fw-extensions-manager.php';

			$this->manager = new _FW_Extensions_Manager();
		}
	}

	/**
	 * Load extension from directory
	 *
	 * @param array $data
	 */
	private static function load_extensions($data)
	{
		if (false) {
			$data = array(
				'rel_path' => '/extension',
				'path' => '/path/to/extension',
				'uri' => 'https://uri.to/extension',
				'customizations_locations' => array(
					'/path/to/parent/theme/customizations/extensions/ext/rel/path' => 'https://uri.to/customization/path',
					'/path/to/child/theme/customizations/extensions/ext/rel/path'  => 'https://uri.to/customization/path',
				),

				'all_extensions_tree' => array(),
				'all_extensions' => array(),
				'current_depth' => 1,
				'parent' => '&$parent_extension_instance',
			);
		}

		/**
		 * Do not check all keys
		 * if one not set, then sure others are not set (this is a private method)
		 */
		if (!isset($data['all_extensions_tree'])) {
			$data['all_extensions_tree'] = &self::$all_extensions_tree;
			$data['all_extensions'] = &self::$all_extensions;
			$data['current_depth'] = 1;
			$data['rel_path'] = '';
			$data['parent'] = null;
		}

		$dirs = glob($data['path'] .'/*', GLOB_ONLYDIR);

		if (empty($dirs)) {
			return;
		}

		if ($data['current_depth'] > 1) {
			$customizations_locations = array();

			foreach ($data['customizations_locations'] as $customization_path => $customization_uri) {
				$customizations_locations[ $customization_path .'/extensions' ] = $customization_uri .'/extensions';
			}

			$data['customizations_locations'] = $customizations_locations;
		}

		foreach ($dirs as $extension_dir) {
			$extension_name = basename($extension_dir);

			{
				$customizations_locations = array();

				foreach ($data['customizations_locations'] as $customization_path => $customization_uri) {
					$customizations_locations[ $customization_path .'/'. $extension_name ] = $customization_uri .'/'. $extension_name;
				}
			}

			if (isset($data['all_extensions'][$extension_name])) {
				if ($data['all_extensions'][$extension_name]->get_parent() !== $data['parent']) {
					// extension with the same name exists in another tree
					trigger_error(
						'Extension "'. $extension_name .'" is already defined '.
						'in "'. $data['all_extensions'][$extension_name]->get_declared_path() .'" '.
						'found again in "'. $extension_dir .'"',
						E_USER_ERROR
					);
				}

				// this is a directory with customizations for an extension

				self::load_extensions(array(
					'rel_path' => $data['rel_path'] .'/'. $extension_name .'/extensions',
					'path' => $data['path'] .'/'. $extension_name .'/extensions',
					'uri' => $data['uri'] .'/'. $extension_name .'/extensions',
					'customizations_locations' => $customizations_locations,

					'all_extensions_tree' => &$data['all_extensions_tree'][$extension_name],
					'all_extensions' => &$data['all_extensions'],
					'current_depth' => $data['current_depth'] + 1,
					'parent' => &$data['all_extensions'][$extension_name],
				));
			} else {
				$class_file_name = 'class-fw-extension-'. $extension_name .'.php';

				if (file_exists($extension_dir .'/manifest.php')) {
					$data['all_extensions_tree'][$extension_name] = array();

					self::$extension_to_all_tree[$extension_name] = &$data['all_extensions_tree'][$extension_name];

					if (fw_include_file_isolated($extension_dir .'/'. $class_file_name)) {
						$class_name = 'FW_Extension_'. fw_dirname_to_classname($extension_name);
					} else {
						$parent_class_name = get_class($data['parent']);
						// check if parent extension has been defined custom Default class for its child extensions
						if (class_exists($parent_class_name .'_Default')) {
							$class_name = $parent_class_name .'_Default';
						} else {
							$class_name = 'FW_Extension_Default';
						}
					}

					if (!is_subclass_of($class_name, 'FW_Extension')) {
						trigger_error('Extension "'. $extension_name .'" must extend FW_Extension class', E_USER_ERROR);
					}

					$data['all_extensions'][$extension_name] = new $class_name(array(
						'rel_path' => $data['rel_path'] .'/'. $extension_name,
						'path' => $data['path'] .'/'. $extension_name,
						'uri' => $data['uri'] .'/'. $extension_name,
						'parent' => $data['parent'],
						'depth' => $data['current_depth'],
						'customizations_locations' => $customizations_locations,
					));
				} else {
					/**
					 * The manifest file does not exist, do not load this extension.
					 * Maybe it's a directory with configurations for a not existing extension.
					 */
					continue;
				}

				self::load_extensions(array(
					'rel_path' => $data['all_extensions'][$extension_name]->get_rel_path() .'/extensions',
					'path' => $data['all_extensions'][$extension_name]->get_path() .'/extensions',
					'uri' => $data['all_extensions'][$extension_name]->get_uri() .'/extensions',
					'customizations_locations' => $customizations_locations,

					'parent' => &$data['all_extensions'][$extension_name],
					'all_extensions_tree' => &$data['all_extensions_tree'][$extension_name],
					'all_extensions' => &$data['all_extensions'],
					'current_depth' => $data['current_depth'] + 1,
				));
			}
		}
	}

	/**
	 * Include file from all extension's locations: framework, parent, child
	 * @param string|FW_Extension $extension
	 * @param string $file_rel_path
	 * @param bool $themeFirst
	 *        false - [framework, parent, child]
	 *        true  - [child, parent, framework]
	 * @param bool $onlyFirstFound
	 */
	private static function include_extension_file_all_locations($extension, $file_rel_path, $themeFirst = false, $onlyFirstFound = false)
	{
		if (is_string($extension)) {
			$extension = fw()->extensions->get($extension);
		}

		$paths = $extension->get_customizations_locations();
		$paths[$extension->get_path()] = $extension->get_uri();

		if (!$themeFirst) {
			$paths = array_reverse($paths);
		}

		foreach ($paths as $path => $uri) {
			if (fw_include_file_isolated($path . $file_rel_path)) {
				if ($onlyFirstFound) {
					return;
				}
			}
		}
	}

	/**
	 * Include all files from directory, from all extension's locations: framework, child, parent
	 * @param string|FW_Extension $extension
	 * @param string $dir_rel_path
	 * @param bool $themeFirst
	 *        false - [framework, parent, child]
	 *        true  - [child, parent, framework]
	 */
	private static function include_extension_directory_all_locations($extension, $dir_rel_path, $themeFirst = false)
	{
		if (is_string($extension)) {
			$extension = fw()->extensions->get($extension);
		}

		$paths = $extension->get_customizations_locations();
		$paths[$extension->get_path()] = $extension->get_uri();

		if (!$themeFirst) {
			$paths = array_reverse($paths);
		}

		foreach ($paths as $path => $uri) {
			if ($files = glob($path . $dir_rel_path .'/*.php')) {
				foreach ($files as $dir_file_path) {
					fw_include_file_isolated($dir_file_path);
				}
			}
		}
	}

	public function get_locations()
	{
		$cache_key = 'fw_extensions_locations';

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			/**
			 * { '/hello/world/extensions' => 'https://hello.com/world/extensions' }
			 */
			$custom_locations = apply_filters('fw_extensions_locations', array());

			{
				$customizations_locations = array();

				if (is_child_theme()) {
					$customizations_locations[fw_get_stylesheet_customizations_directory('/extensions')]
						= fw_get_stylesheet_customizations_directory_uri('/extensions');
				}

				$customizations_locations[fw_get_template_customizations_directory('/extensions')]
					= fw_get_template_customizations_directory_uri('/extensions');

				$customizations_locations += $custom_locations;
			}

			$locations = array();

			$locations[ fw_get_framework_directory('/extensions') ] = array(
				'path' => fw_get_framework_directory('/extensions'),
				'uri' => fw_get_framework_directory_uri('/extensions'),
				'customizations_locations' => $customizations_locations,
				'is' => array(
					'framework' => true,
					'custom' => false,
					'theme' => false,
				),
			);

			foreach ($custom_locations as $path => $uri) {
				unset($customizations_locations[$path]);
				$locations[ $path ] = array(
					'path' => $path,
					'uri' => $uri,
					'customizations_locations' => $customizations_locations,
					'is' => array(
						'framework' => false,
						'custom' => true,
						'theme' => false,
					),
				);
			}

			array_pop($customizations_locations);
			$locations[ fw_get_template_customizations_directory('/extensions') ] = array(
				'path' => fw_get_template_customizations_directory('/extensions'),
				'uri' => fw_get_template_customizations_directory_uri('/extensions'),
				'customizations_locations' => $customizations_locations,
				'is' => array(
					'framework' => false,
					'custom' => false,
					'theme' => true,
				),
			);

			if (is_child_theme()) {
				array_pop($customizations_locations);
				$locations[ fw_get_stylesheet_customizations_directory('/extensions') ] = array(
					'path' => fw_get_stylesheet_customizations_directory('/extensions'),
					'uri' => fw_get_stylesheet_customizations_directory_uri('/extensions'),
					'customizations_locations' => $customizations_locations,
					'is' => array(
						'framework' => false,
						'custom' => false,
						'theme' => true,
					),
				);
			}

			FW_Cache::set($cache_key, $locations);

			return $locations;
		}
	}

	private function load_all_extensions()
	{
		foreach ($this->get_locations() as $location) {
			self::load_extensions(array(
				'path' => $location['path'],
				'uri' => $location['uri'],
				'customizations_locations' => $location['customizations_locations'],
			));
		}
	}

	/**
	 * Activate extensions from given tree point
	 *
	 * @param null|string $parent_extension_name
	 */
	private function activate_extensions($parent_extension_name = null)
	{
		if ($parent_extension_name === null) {
			$all_tree =& self::$all_extensions_tree;
		} else {
			$all_tree =& self::$extension_to_all_tree[$parent_extension_name];
		}

		foreach ($all_tree as $extension_name => &$sub_extensions) {
			if (fw()->extensions->get($extension_name)) {
				// extension already active
				continue;
			}

			$extension =& self::$all_extensions[$extension_name];

			if ($extension->manifest->check_requirements()) {
				if (!$this->_get_db_active_extensions($extension_name)) {
					// extension is not set as active
				} elseif (
					$extension->get_parent()
					&&
					!$extension->get_parent()->_child_extension_is_valid($extension)
				) {
					// extension does not pass parent extension rules
					if (is_admin()) {
						// show warning only in admin side
						FW_Flash_Messages::add(
							'fw-invalid-extension',
							sprintf(__('Extension %s is invalid.', 'fw'), $extension->get_name()),
							'warning'
						);
					}
				} else {
					// all requirements met, activate extension
					$this->activate_extension($extension_name);
				}
			} else {
				// requirements not met, tell required extensions that this extension is waiting for them

				foreach ($extension->manifest->get_required_extensions() as $required_extension_name => $requirements) {
					if (!isset(self::$extensions_required_by_extensions[$required_extension_name])) {
						self::$extensions_required_by_extensions[$required_extension_name] = array();
					}

					self::$extensions_required_by_extensions[$required_extension_name][] = $extension_name;
				}
			}
		}
		unset($sub_extensions);
	}

	/**
	 * @param string $extension_name
	 * @return bool
	 */
	private function activate_extension($extension_name)
	{
		if (fw()->extensions->get($extension_name)) {
			// already active
			return false;
		}

		if (!self::$all_extensions[$extension_name]->manifest->requirements_met()) {
			trigger_error('Wrong '. __METHOD__ .' call', E_USER_WARNING);
			return false;
		}

		// add to active extensions so inside includes/ and extension it will be accessible from fw()->extensions->get(...)
		self::$active_extensions[$extension_name] =& self::$all_extensions[$extension_name];

		$parent = self::$all_extensions[$extension_name]->get_parent();

		if ($parent) {
			self::$extension_to_active_tree[ $parent->get_name() ][$extension_name] = array();
			self::$extension_to_active_tree[$extension_name] =& self::$extension_to_active_tree[ $parent->get_name() ][$extension_name];
		} else {
			self::$active_extensions_tree[$extension_name] = array();
			self::$extension_to_active_tree[$extension_name] =& self::$active_extensions_tree[$extension_name];
		}

		self::include_extension_directory_all_locations($extension_name, '/includes');
		self::include_extension_file_all_locations($extension_name, '/helpers.php');
		self::include_extension_file_all_locations($extension_name, '/hooks.php');

		if (self::$all_extensions[$extension_name]->_call_init(self::$access_key) !== false) {
			$this->activate_extensions($extension_name);
		}

		// check if other extensions are waiting for this extension and try to activate them
		if (isset(self::$extensions_required_by_extensions[$extension_name])) {
			foreach (self::$extensions_required_by_extensions[$extension_name] as $waiting_extension_name) {
				if (self::$all_extensions[$waiting_extension_name]->manifest->check_requirements()) {
					$waiting_extension = self::$all_extensions[$waiting_extension_name];

					if (!$this->_get_db_active_extensions($waiting_extension_name)) {
						// extension is set as active
					} elseif (
						$waiting_extension->get_parent()
						&&
						!$waiting_extension->get_parent()->_child_extension_is_valid($waiting_extension)
					) {
						// extension does not pass parent extension rules
						if (is_admin()) {
							// show warning only in admin side
							FW_Flash_Messages::add(
								'fw-invalid-extension',
								sprintf(__('Extension %s is invalid.', 'fw'), $waiting_extension_name),
								'warning'
							);
						}
					} else {
						$this->activate_extension($waiting_extension_name);
					}
				}
			}

			unset(self::$extensions_required_by_extensions[$extension_name]);
		}

		return true;
	}

	private function add_actions()
	{
		add_action('init',                  array($this, '_action_init'));
		add_action('wp_enqueue_scripts',    array($this, '_action_enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this, '_action_enqueue_scripts'));
	}

	/**
	 * Give extensions possibility to access their active_tree
	 * @internal
	 *
	 * @param FW_Access_Key $access_key
	 * @param $extension_name
	 *
	 * @return array
	 */
	public function _get_extension_tree(FW_Access_Key $access_key, $extension_name)
	{
		if ($access_key->get_key() !== 'extension') {
			trigger_error('Call denied', E_USER_ERROR);
		}

		return self::$extension_to_active_tree[$extension_name];
	}

	/**
	 * @internal
	 */
	public function _init()
	{
		self::$access_key = new FW_Access_Key('fw_extensions');

		$this->load_all_extensions();
		$this->add_actions();
	}

	/**
	 * @internal
	 */
	public function _after_components_init()
	{
		$this->activate_extensions();

		/**
		 * Extensions are activated
		 * Now $this->get_children() inside extensions is available
		 */
		do_action('fw_extensions_init');
	}

	public function _action_init()
	{
		foreach (self::$active_extensions as &$extension) {
			/** register posts and taxonomies */
			self::include_extension_file_all_locations($extension, '/posts.php');
		}
	}

	public function _action_enqueue_scripts()
	{
		foreach (self::$active_extensions as &$extension) {
			/** js and css */
			self::include_extension_file_all_locations($extension, '/static.php', true, true);
		}
	}

	/**
	 * @param string $extension_name returned by FW_Extension::get_name()
	 * @return FW_Extension|null
	 */
	public function get($extension_name)
	{
		if (isset(self::$active_extensions[$extension_name])) {
			return self::$active_extensions[$extension_name];
		} else {
			return null;
		}
	}

	/**
	 * Get all active extensions
	 * @return FW_Extension[]
	 */
	public function get_all()
	{
		return self::$active_extensions;
	}

	/**
	 * Get extensions tree (how they are arranged in directories)
	 * @return array
	 */
	public function get_tree()
	{
		return self::$active_extensions_tree;
	}

	/**
	 * @return false
	 * @deprecated Use $extension->locate_path()
	 */
	public function locate_path()
	{
		return false;
	}

	/**
	 * @return false
	 * @deprecated Use $extension->locate_URI()
	 */
	public function locate_path_URI()
	{
		return false;
	}
}
