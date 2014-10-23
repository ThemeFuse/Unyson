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
	 * Only this extensions should be available for other, inactive extensions =
	 * @var FW_Extension[] { 'extension_name' => instance }
	 */
	private static $active_extensions = array();

	/**
	 * Active extensions names arranged in hierarchical tree like they are in directories
	 * @var array
	 */
	private static $active_extensions_tree = array();

	/**
	 * Used on extensions load
	 * Indicates from where are currently extensions loaded
	 * @var string|null framework|parent|child
	 */
	private static $current_declaring_source;

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

	private static $blacklist = array();

	/**
	 * Load extension from directory
	 *
	 * @param null|FW_Extension $parent
	 * @param array $all_extensions_tree
	 * @param FW_Extension[] $all_extensions
	 * @param string $dir
	 * @param string $URI
	 * @var int $current_depth
	 */
	private static function load_extensions($dir, &$parent, &$all_extensions_tree, &$all_extensions, $URI, $current_depth)
	{
		$dirs = glob($dir .'/*', GLOB_ONLYDIR);

		if (empty($dirs)) {
			return;
		}

		foreach ($dirs as $extension_dir) {
			$extension_name = basename($extension_dir);

			if (isset($all_extensions_tree[$extension_name])) {
				if ($all_extensions[$extension_name]->get_parent() !== $parent) {
					// extension with the same name exists in another tree
					trigger_error('Extension "'. $extension_name .'" already loaded', E_USER_ERROR);
				}

				// this is a directory with customizations for an extension

				self::load_extensions(
					$extension_dir .'/extensions',
					$all_extensions[$extension_name],
					$all_extensions_tree[$extension_name],
					$all_extensions,
					$URI,
					$current_depth + 1
				);
			} else {
				$class_file_name = 'class-fw-extension-'. $extension_name .'.php';

				if (file_exists($extension_dir .'/manifest.php')) {
					$all_extensions_tree[$extension_name] = array();

					self::$extension_to_all_tree[$extension_name] =& $all_extensions_tree[$extension_name];

					if (file_exists($extension_dir .'/'. $class_file_name)) {
						$class_name = 'FW_Extension_'. fw_dirname_to_classname($extension_name);

						require $extension_dir .'/'. $class_file_name;
					} else {
						$parent_class_name = get_class($parent);
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

					$all_extensions[$extension_name] = new $class_name(
						$parent,
						$extension_dir,
						self::$current_declaring_source,
						$URI,
						$current_depth
					);
				} else {
					/**
					 * The manifest file does not exist, do not load this extension.
					 * Maybe it's a directory with configurations for a not existing extension.
					 */
					continue;
				}

				self::load_extensions(
					$all_extensions[$extension_name]->get_declared_path() .'/extensions',
					$all_extensions[$extension_name],
					$all_extensions_tree[$extension_name],
					$all_extensions,
					$URI,
					$current_depth + 1
				);
			}
		}
	}

	/**
	 * Include file from all extension's locations: framework, child, parent
	 * @param string|FW_Extension $extension
	 * @param string $extension_file_rel_path
	 */
	private static function include_extension_file_all($extension, $extension_file_rel_path)
	{
		if (is_string($extension)) {
			$extension = fw()->extensions->get($extension);
		}

		list(
			$search_in_framework,
			$search_in_parent_theme,
			$search_in_child_theme
		) = $extension->correct_search_in_locations(
			true,
			true,
			true
		);

		$rel_path = $extension->get_rel_path() . $extension_file_rel_path;

		if ($search_in_framework) {
			fw_include_file_isolated(fw_get_framework_directory('/extensions'. $rel_path));
		}

		if ($search_in_child_theme) {
			fw_include_file_isolated(fw_get_stylesheet_customizations_directory('/extensions'. $rel_path));
		}

		if ($search_in_parent_theme) {
			fw_include_file_isolated(fw_get_template_customizations_directory('/extensions'. $rel_path));
		}
	}

	/**
	 * Include all files from directory, from all extension's locations: framework, child, parent
	 * @param string|FW_Extension $extension
	 * @param string $extension_dir_rel_path
	 */
	private static function include_extension_directory_all($extension, $extension_dir_rel_path)
	{
		if (is_string($extension)) {
			$extension = fw()->extensions->get($extension);
		}

		list(
			$search_in_framework,
			$search_in_parent_theme,
			$search_in_child_theme
		) = $extension->correct_search_in_locations(
			true,
			true,
			true
		);

		$rel_path = $extension->get_rel_path() . $extension_dir_rel_path;
		$paths    = array();

		if ($search_in_child_theme) {
			$paths[] = fw_get_stylesheet_customizations_directory('/extensions'. $rel_path);
		}

		if ($search_in_parent_theme) {
			$paths[] = fw_get_template_customizations_directory('/extensions'. $rel_path);
		}

		if ($search_in_framework) {
			$paths[] = fw_get_framework_directory('/extensions'. $rel_path);
		}

		foreach ($paths as $path) {
			if ($files = glob($path .'/*.php')) {
				foreach ($files as $dir_file_path) {
					fw_include_file_isolated($dir_file_path);
				}
			}
		}
	}

	private function load_all_extensions()
	{
		$parent = null;

		self::$current_declaring_source = 'framework';
		self::load_extensions(
			fw_get_framework_directory('/extensions'),
			$parent,
			self::$all_extensions_tree,
			self::$all_extensions,
			fw_get_framework_directory_uri('/extensions'),
			1
		);

		self::$current_declaring_source = 'parent';
		self::load_extensions(
			fw_get_template_customizations_directory('/extensions'),
			$parent,
			self::$all_extensions_tree,
			self::$all_extensions,
			fw_get_template_customizations_directory_uri('/extensions'),
			1
		);

		if (is_child_theme()) {
			self::$current_declaring_source = 'child';
			self::load_extensions(
				fw_get_stylesheet_customizations_directory('/extensions'),
				$parent,
				self::$all_extensions_tree,
				self::$all_extensions,
				fw_get_stylesheet_customizations_directory_uri('/extensions'),
				1
			);
		}

		self::$current_declaring_source = null;
	}

	/**
	 * Activate extensions from given tree point
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
				if (isset(self::$blacklist[$extension_name])) {
					// do not activate blacklisted extension
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

		self::include_extension_directory_all($extension_name,  '/includes');
		self::include_extension_file_all($extension_name,       '/helpers.php');
		self::include_extension_file_all($extension_name,       '/hooks.php');

		if (self::$all_extensions[$extension_name]->_call_init() !== false) {
			$this->activate_extensions($extension_name);
		}

		// check if other extensions are waiting for this extension and try to activate them
		if (isset(self::$extensions_required_by_extensions[$extension_name])) {
			foreach (self::$extensions_required_by_extensions[$extension_name] as $waiting_extension_name) {
				if (self::$all_extensions[$waiting_extension_name]->manifest->check_requirements()) {
					$waiting_extension = self::$all_extensions[$waiting_extension_name];

					if (isset(self::$blacklist[$waiting_extension_name])) {
						// do not activate blacklisted extensions
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
		$this->load_all_extensions();
		$this->add_actions();
	}

	/**
	 * @internal
	 */
	public function _after_components_init()
	{
		/** Prepare BlackList */
		{
			self::$blacklist = fw()->theme->get_config('extensions_blacklist');

			if (empty(self::$blacklist)) {
				self::$blacklist = array();
			} else {
				self::$blacklist = array_fill_keys(self::$blacklist, true);
			}
		}

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
			/** register posts */
			self::include_extension_file_all($extension, '/posts.php');
		}
	}

	public function _action_enqueue_scripts()
	{
		foreach (self::$active_extensions as &$extension) {
			/** js and css */
			self::include_extension_file_all($extension, '/static.php');
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
	 * Search relative path in: child theme -> parent theme -> framework extensions directory and return full path
	 *
	 * @param string $rel_path '/<extension>/path_to_dir' or '/<extension>/extensions/<another_extension>/path_to_file.php'
	 * @param   bool $search_in_framework
	 * @param   bool $search_in_parent_theme
	 * @param   bool $search_in_child_theme
	 * @return false|string Full path or false if not found
	 */
	public function locate_path($rel_path, $search_in_framework = true, $search_in_parent_theme = true, $search_in_child_theme = true)
	{
		if ($search_in_child_theme && is_child_theme()) {
			if (file_exists(fw_get_stylesheet_customizations_directory('/extensions'. $rel_path))) {
				return fw_get_stylesheet_customizations_directory('/extensions'. $rel_path);
			}
		}

		if ($search_in_parent_theme) {
			if (file_exists(fw_get_template_customizations_directory('/extensions'. $rel_path))) {
				return fw_get_template_customizations_directory('/extensions'. $rel_path);
			}
		}

		if ($search_in_framework) {
			if (file_exists(fw_get_framework_directory('/extensions'. $rel_path))) {
				return fw_get_framework_directory('/extensions'. $rel_path);
			}
		}

		return false;
	}

	/**
	 * Search relative path in: child theme -> parent theme -> framework extensions directory and return URI
	 *
	 * @param string $rel_path '/<extension>/path_to_dir' or '/<extension>/extensions/<another_extension>/path_to_file.php'
	 * @param   bool $search_in_framework
	 * @param   bool $search_in_parent_theme
	 * @param   bool $search_in_child_theme
	 * @return false|string URI or false if not found
	 */
	public function locate_path_URI($rel_path, $search_in_framework = true, $search_in_parent_theme = true, $search_in_child_theme = true)
	{
		if ($search_in_child_theme && is_child_theme()) {
			if (file_exists(fw_get_stylesheet_customizations_directory('/extensions'. $rel_path))) {
				return fw_get_stylesheet_customizations_directory_uri('/extensions' . $rel_path);
			}
		}

		if ($search_in_parent_theme) {
			if (file_exists(fw_get_template_customizations_directory('/extensions'. $rel_path))) {
				return fw_get_template_customizations_directory_uri('/extensions'. $rel_path);
			}
		}

		if ($search_in_framework) {
			if (file_exists(fw_get_framework_directory('/extensions'. $rel_path))) {
				return fw_get_framework_directory_uri('/extensions'. $rel_path);
			}
		}

		return false;
	}
}

/**
 * Instances of this class will be created for extensions without class
 */
class FW_Extension_Default extends FW_Extension
{
	protected function _init()
	{
	}
}
