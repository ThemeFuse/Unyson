<?php if (!defined('FW')) die('Forbidden');

/**
 * Install/Activate/Deactivate/Remove Extensions
 * @internal
 */
final class _FW_Extensions_Manager
{
	/**
	 * @var FW_Form
	 */
	private $extension_settings_form;

	/**
	 * @var Parsedown
	 */
	private $markdown_parser;

	private $manifest_default_values = array(
		'display' => false,
		'standalone' => false,
	);

	private $download_timeout = 300;

	private $default_thumbnail = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIW2PUsHf9DwAC8AGtfm5YCAAAAABJRU5ErkJgggAA';

	public function __construct()
	{
		if (!is_admin()) {
			return;
		}

		// In any case/permission, make sure to not miss the plugin update actions to prevent extensions delete
		{
			add_action('fw_plugin_pre_update', array($this, '_action_plugin_pre_update'));
			add_action('fw_plugin_post_update', array($this, '_action_plugin_post_update'));
		}

		if (!$this->can_activate() && !$this->can_install()) {
			return;
		}

		/** Actions */
		{
			add_action('fw_init', array($this, '_action_fw_init'));
			add_action('admin_menu', array($this, '_action_admin_menu'));
			add_action('network_admin_menu', array($this, '_action_admin_menu'));
			add_action('admin_footer', array($this, '_action_admin_footer'));
			add_action('admin_enqueue_scripts', array($this, '_action_enqueue_menu_icon_style'));
			add_action('fw_after_plugin_activate', array($this, '_action_after_plugin_activate'), 100);
			add_action('after_switch_theme', array($this, '_action_theme_switch'));

			if ($this->can_install()) {
				add_action('wp_ajax_fw_extensions_check_direct_fs_access', array($this, '_action_ajax_check_direct_fs_access'));
			}
		}

		/** Filters */
		{
			add_filter('fw_plugin_action_list', array($this, '_filter_plugin_action_list'));
		}
	}

	/**
	 * If current user can:
	 * - activate extension
	 * - disable extensions
	 * - save extension settings options
	 * @return bool
	 */
	public function can_activate()
	{
		static $can_activate = null;

		if ($can_activate === null) {
			$can_activate = current_user_can('manage_options');

			if ($can_activate) {
				// also you can use this method to get the capability
				$can_activate = 'manage_options';
			}

			if (!$can_activate) {
				// make sure if can install, then also can activate. (can install) > (can activate)
				$can_activate = $this->can_install();
			}
		}

		return $can_activate;
	}

	/**
	 * If current user can:
	 * - install extensions
	 * - delete extensions
	 * @return bool
	 */
	public function can_install()
	{
		static $can_install = null;

		if ($can_install === null) {
			$can_install = current_user_can('install_plugins');

			if (is_multisite() && !is_network_admin()) {
				// only network admin can change files that affects the entire network
				$can_install = false;
			}

			if ($can_install) {
				// also you can use this method to get the capability
				$can_install = 'install_plugins';
			}
		}

		return $can_install;
	}

	public function get_page_slug()
	{
		return 'fw-extensions';
	}

	private function get_cache_key($sub_key)
	{
		return 'fw_extensions_manager/'. $sub_key;
	}

	private function get_uri($append = '')
	{
		return fw_get_framework_directory_uri('/core/components/extensions/manager'. $append);
	}

	private function get_markdown_parser()
	{
		if (!$this->markdown_parser) {
			if (!class_exists('Parsedown')) {
				require_once dirname(__FILE__) .'/includes/parsedown/Parsedown.php';
			}

			$this->markdown_parser = new Parsedown();
		}

		return $this->markdown_parser;
	}

	private function get_nonce($form) {
		switch ($form) {
			case 'install':
				return array(
					'name' => '_nonce_fw_extensions_install',
					'action' => 'install',
				);
			case 'delete':
				return array(
					'name' => '_nonce_fw_extensions_delete',
					'action' => 'delete',
				);
			case 'activate':
				return array(
					'name' => '_nonce_fw_extensions_activate',
					'action' => 'activate',
				);
			case 'deactivate':
				return array(
					'name' => '_nonce_fw_extensions_deactivate',
					'action' => 'deactivate',
				);
			default:
				return array(
					'name' => '_nonce_fw_extensions',
					'action' => 'default',
				);
		}
	}

	/**
	 * Extensions available for download
	 * @return array {name => data}
	 */
	private function get_available_extensions()
	{
		try {
			$cache_key = $this->get_cache_key( 'available_extensions' );

			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$vars = fw_get_variables_from_file( dirname( __FILE__ ) . '/available-extensions.php', array(
				'extensions' => array()
			) );

			FW_Cache::set($cache_key, $vars['extensions']);

			return $vars['extensions'];
		}
	}

	/**
	 * @internal
	 */
	public function _action_ajax_check_direct_fs_access()
	{
		if (!$this->can_install()) {
			// if can't install, no need to know if has access or not
			wp_send_json_error();
		}

		if (FW_WP_Filesystem::has_direct_access(fw_get_framework_directory('/extensions'))) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * @internal
	 */
	public function _action_after_plugin_activate()
	{
		$this->activate_theme_extensions();
		$this->activate_extensions(
			array_fill_keys(
				array_keys(fw()->theme->manifest->get('supported_extensions', array())),
				array()
			)
		);

		if ($this->can_install()) {
			if ($this->get_supported_extensions_for_install()) {
				$link = $this->get_link();

				wp_redirect($link . '&sub-page=install&supported');
				exit;
			}
		}
	}

	/**
	 * Copy all extensions to a temp backup directory
	 * @internal
	 */
	public function _action_plugin_pre_update()
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			return;
		}

		// a directory outside the plugin
		$tmp_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
			fw_fix_path(WP_CONTENT_DIR) .'/tmp/fw-plugin-update-extensions-backup'
		);
		$extensions_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
			fw_get_framework_directory('/extensions')
		);

		$error = false;

		do {
			if ($wp_filesystem->exists($tmp_dir)) {
				if (!$wp_filesystem->delete($tmp_dir, true, 'd')) {
					$error = __('Cannot remove the old extensions backup dir', 'fw');
					break;
				}
			}

			if (!FW_WP_Filesystem::mkdir_recursive($tmp_dir)) {
				$error = __('Cannot create the extensions backup dir', 'fw');
				break;
			}

			if (true !== copy_dir($extensions_dir, $tmp_dir)) {
				$error = __('Cannot backup the extensions', 'fw');
				break;
			}
		} while(false);

		if ($error) {
			trigger_error($error, E_USER_WARNING);

			$wp_filesystem->delete($tmp_dir, true, 'd');
		}
	}

	/**
	 * Copy all extensions from the temp backup directory to the framework extensions directory (recover)
	 * @internal
	 */
	public function _action_plugin_post_update()
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			return;
		}

		// a directory outside the plugin
		$tmp_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
			fw_fix_path( WP_CONTENT_DIR ) .'/tmp/fw-plugin-update-extensions-backup'
		);
		$extensions_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
			fw_get_framework_directory( '/extensions' )
		);

		if (!$wp_filesystem->exists($tmp_dir) || !$wp_filesystem->exists($extensions_dir)) {
			return;
		}

		$error = false;

		do {
			if ($wp_filesystem->exists($extensions_dir)) {
				/**
				 * Make sure to remove framework initial extensions
				 * The user do not need them because he already used the framework and has in backup the extensions he uses
				 */
				if (!$wp_filesystem->delete( $extensions_dir, true, 'd' )) {
					$error = __( 'Cannot clear the extensions directory', 'fw' );
					break;
				}

				if ( ! FW_WP_Filesystem::mkdir_recursive( $extensions_dir ) ) {
					$error = __( 'Cannot recreate the extensions directory', 'fw' );
					break;
				}
			}

			if (true !== copy_dir($tmp_dir, $extensions_dir)) {
				$error = __('Cannot recover the extensions', 'fw');
				break;
			}
		} while(false);

		if ($error) {
			trigger_error($error, E_USER_WARNING);
		} else {
			// extensions successfully recovered, the backup is not needed anymore
			$wp_filesystem->delete($tmp_dir, true, 'd');
		}
	}

	/**
	 * Scan all directories for extensions
	 *
	 * @param bool $reset_cache
	 * @return array
	 */
	private function get_installed_extensions($reset_cache = false)
	{
		$cache_key = $this->get_cache_key('installed_extensions');

		if ($reset_cache) {
			FW_Cache::del($cache_key);
		}

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$extensions = array();

			foreach (fw()->extensions->get_locations() as $location) {
				// leave only used keys
				$location = array(
					'path' => $location['path'],
					'is'   => $location['is'],
				);

				$this->read_extensions($location, $extensions);
			}

			FW_Cache::set($cache_key, $extensions);

			return $extensions;
		}
	}

	/**
	 * used by $this->get_installed_extensions()
	 * @param string $location
	 * @param array $list
	 * @param null|string $parent_extension_name
	 */
	private function read_extensions($location, &$list, $parent_extension_name = null)
	{
		$paths = glob($location['path'] .'/*', GLOB_ONLYDIR | GLOB_NOSORT);

		if (empty($paths)) {
			return;
		}

		foreach ($paths as $extension_path) {
			$extension_name = basename($extension_path);

			if (isset($list[$extension_name])) {
				// extension already found
			} elseif (file_exists($extension_path .'/manifest.php')) {
				$vars = fw_get_variables_from_file($extension_path .'/manifest.php', array(
					'manifest' => array(),
				));

				$list[$extension_name] = array(
					'path'     => $extension_path,
					'manifest' => $vars['manifest'],
					'children' => array(),
					'active'   => (bool)fw()->extensions->get($extension_name),
					'parent'   => $parent_extension_name,
					'is'       => $location['is'],
				);

				if ($parent_extension_name) {
					$list[ $parent_extension_name ]['children'][$extension_name] = array();
				}
			} else {
				// it's a directory with customizations for an extension
				continue;
			}

			$sub_extension_location = $location;
			$sub_extension_location['path'] .= '/'. $extension_name .'/extensions';

			$this->read_extensions(
				$sub_extension_location,
				$list,
				$extension_name
			);
		}
	}

	private function get_tmp_dir($append = '')
	{
		return apply_filters('fw_tmp_dir', fw_fix_path(WP_CONTENT_DIR) .'/tmp') . $append;
	}

	/**
	 * @internal
	 */
	public function _action_fw_init()
	{
		$this->extension_settings_form = new FW_Form('fw_extension_settings', array(
			'render'   => array($this, '_extension_settings_form_render'),
			'validate' => array($this, '_extension_settings_form_validate'),
			'save'     => array($this, '_extension_settings_form_save'),
		));

		if (is_admin() && $this->can_activate()) {
			$db_wp_option_name = 'fw_extensions_activation';

			if ($db_wp_option_value = get_option($db_wp_option_name, array())) {
				$db_wp_option_value = array_merge(array(
					'activated' => array(),
					'deactivated' => array(),
				), $db_wp_option_value);

				/**
				 * Fire the 'fw_extensions_after_activation' action
				 */
				if ($db_wp_option_value['activated']) {
					$succeeded_extensions = $failed_extensions = array();

					foreach ($db_wp_option_value['activated'] as $extension_name => $not_used_var) {
						if (fw_ext($extension_name)) {
							$succeeded_extensions[$extension_name] = array();
						} else {
							$failed_extensions[$extension_name] = array();
						}
					}

					if (!empty($succeeded_extensions)) {
						do_action('fw_extensions_after_activation', $succeeded_extensions);
					}
					if (!empty($failed_extensions)) {
						do_action('fw_extensions_activation_failed', $failed_extensions);
					}
				}

				/**
				 * Fire the 'fw_extensions_after_deactivation' action
				 */
				if ($db_wp_option_value['deactivated']) {
					$succeeded_extensions = $failed_extensions = array();

					foreach ($db_wp_option_value['deactivated'] as $extension_name => $not_used_var) {
						if (!fw_ext($extension_name)) {
							$succeeded_extensions[$extension_name] = array();
						} else {
							$failed_extensions[$extension_name] = array();
						}
					}

					if (!empty($succeeded_extensions)) {
						do_action('fw_extensions_after_deactivation', $succeeded_extensions);
					}
					if (!empty($failed_extensions)) {
						do_action('fw_extensions_deactivation_failed', $failed_extensions);
					}
				}

				delete_option($db_wp_option_name);
			}
		}
	}

	/**
	 * Activate extensions with $manifest['display'] = false; $manifest['standalone'] = true;
	 * - First level extensions
	 * - Child extensions of the active extensions
	 */
	private function activate_hidden_standalone_extensions()
	{
		if (!is_admin()) {
			return;
		}

		if (!$this->can_activate()) {
			return;
		}

		$activate_extensions = array();

		foreach (
			// all disabled extensions
			array_diff_key($this->get_installed_extensions(), fw()->extensions->get_all())
			as $ext_name => $ext_data
		) {
			if ($ext_data['parent'] && !fw_ext($ext_data['parent'])) {
				// child extensions of an inactive extension
				continue;
			}

			if (false !== fw_akg(
				'display',
				$ext_data['manifest'],
				$this->manifest_default_values['display']
			)) {
				// is visible
				continue;
			}

			if (true !== fw_akg(
				'standalone',
				$ext_data['manifest'],
				$this->manifest_default_values['standalone']
			)) {
				// not standalone
				continue;
			}

			$collected = $this->get_extensions_for_activation($ext_name);

			if (is_wp_error($collected)) {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					if (
						fw_current_screen_match(array(
							'only' => array(
								array('parent_base' => $this->get_page_slug())
							)
						))
					) {
						// display this warning only on Unyson extensions page
						FW_Flash_Messages::add('fw_ext_auto_activate_hidden_standalone',
							sprintf(__('Cannot activate hidden standalone extension %s', 'fw'),
								fw_akg('name', $ext_data['manifest'], fw_id_to_title($ext_name))
							),
							'error'
						);
					}
				}
				return;
			}

			$activate_extensions = array_merge($activate_extensions, $collected);
		}

		if (empty($activate_extensions)) {
			return;
		}

		$option_name = fw()->extensions->_get_active_extensions_db_option_name();

		$db_active_extensions = array_merge(get_option($option_name, array()), $activate_extensions);

		update_option($option_name, $db_active_extensions);
	}

	/**
	 * @internal
	 */
	public function _action_admin_menu()
	{
		$capability = $this->can_activate();

		if (!$capability) {
			return;
		}

		$data = array(
			'title'             => fw()->manifest->get_name(),
			'capability'        => $capability,
			'slug'              => $this->get_page_slug(),
			'content_callback'  => array($this, '_display_page'),
		);

		/**
		 * Collect $hookname that contains $data['slug'] before the action
		 * and skip them in verification after action
		 */
		{
			global $_registered_pages;

			$found_hooknames = array();

			if (!empty($_registered_pages)) {
				foreach ( $_registered_pages as $hookname => $b ) {
					if ( strpos( $hookname, $data['slug'] ) !== false ) {
						$found_hooknames[$hookname] = true;
					}
				}
			}
		}

		/**
		 * Use this action if you what to add the extensions page in a custom place in menu
		 * Usage example http://pastebin.com/2iWVRPAU
		 */
		do_action('fw_backend_add_custom_extensions_menu', $data);

		/**
		 * Check if menu was added in the action above
		 */
		{
			$menu_exists = false;

			if (!empty($_registered_pages)) {
				foreach ( $_registered_pages as $hookname => $b ) {
					if (isset($found_hooknames[$hookname])) {
						continue;
					}

					if ( strpos( $hookname, $data['slug'] ) !== false ) {
						$menu_exists = true;
						break;
					}
				}
			}
		}

		if ($menu_exists) {
			// do nothing
		} else {
			add_menu_page(
				$data['title'],
				$data['title'],
				$data['capability'],
				$data['slug'],
				$data['content_callback'],
				'none',
				3
			);
		}
	}

	/**
	 * If output already started, we cannot set the redirect header, do redirect from js
	 */
	private function js_redirect()
	{
		echo
			'<script type="text/javascript">'.
				'window.location.replace("'. esc_js($this->get_link()) .'");'.
			'</script>';
	}

	/**
	 * @internal
	 */
	public function _display_page()
	{
		$page = FW_Request::GET('sub-page');

		switch ($page) {
			case 'install':
				$this->display_install_page();
				break;
			case 'delete':
				$this->display_delete_page();
				break;
			case 'extension':
				$this->display_extension_page();
				break;
			case 'activate':
				$this->display_activate_page();
				break;
			case 'deactivate':
				$this->display_deactivate_page();
				break;
			default:
				$this->display_list_page();
		}
	}

	private function display_list_page()
	{
		{
			wp_enqueue_style(
				'fw-extensions-page',
				$this->get_uri('/static/extensions-page.css'),
				array('fw'),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				'fw-extensions-page',
				$this->get_uri('/static/extensions-page.js'),
				array('fw'),
				fw()->manifest->get_version(),
				true
			);
			wp_localize_script('fw-extensions-page', '_fw_extensions_script_data', array(
				'link' => $this->get_link(),
			));
		}

		/** Prepare extensions list for view */
		{
			$lists = array(
				'active'    => array(),
				'disabled'  => array(),
				'installed' => array(),
				'available' => array(),
				'supported' => array(),
			);

			foreach ($this->get_installed_extensions() as $ext_name => $ext_data) {
				$lists[ $ext_data['active'] ? 'active' : 'disabled' ][$ext_name] = $ext_data;
			}

			$lists['installed'] = $lists['active'] + $lists['disabled'];

			unset($ext_data); // prevent change by reference

			foreach ($this->get_available_extensions() as $ext_name => $ext_data) {
				$lists['available'][$ext_name] = array(
					'name' => $ext_data['name'],
					'description' => $ext_data['description'],
					'thumbnail' => isset($ext_data['thumbnail'])
						? $ext_data['thumbnail']
						: (isset($lists['installed'][$ext_name])
							? fw_akg('thumbnail', $lists['installed'][$ext_name]['manifest'], $this->default_thumbnail)
							: $this->default_thumbnail),
					'display' => isset($ext_data['display'])
						? $ext_data['display']
						: $this->manifest_default_values['display'],
				);
			}

			foreach (fw()->theme->manifest->get('supported_extensions', array()) as $required_ext_name => $required_ext_data) {
				if (isset($lists['installed'][ $required_ext_name ])) {
					$lists['supported'][ $required_ext_name ] = array(
						'name'        => fw_akg( 'name', $lists['installed'][ $required_ext_name ]['manifest'], fw_id_to_title( $required_ext_name ) ),
						'description' => fw_akg( 'description', $lists['installed'][ $required_ext_name ]['manifest'], '' ),
					);
				} elseif (isset($lists['available'][$required_ext_name])) {
					$lists['supported'][ $required_ext_name ] = array(
						'name'        => $lists['available'][ $required_ext_name ]['name'],
						'description' => $lists['available'][ $required_ext_name ]['description'],
					);
				} else {
					$lists['supported'][ $required_ext_name ] = array(
						'name'        => fw_id_to_title( $required_ext_name ),
						'description' => '',
					);
				}
			}
		}

		echo '<div class="wrap">';

		echo '<h2>'. sprintf(__('%s Extensions', 'fw'), fw()->manifest->get_name()) .'</h2><br/>';

		echo '<div id="fw-extensions-list-wrapper" style="opacity:0;">';

		fw_render_view(dirname(__FILE__) .'/views/extensions-page.php', array(
			'lists' => &$lists,
			'link' => $this->get_link(),
			'display_default_value' => $this->manifest_default_values['display'],
			'default_thumbnail' => $this->default_thumbnail,
			'nonces' => array(
				'delete' => $this->get_nonce('delete'),
				'install' => $this->get_nonce('install'),
				'activate' => $this->get_nonce('activate'),
				'deactivate' => $this->get_nonce('deactivate'),
			),
			'can_install' => $this->can_install(),
		), false);

		echo '</div>';

		echo '</div>';
	}

	private function display_install_page()
	{
		$flash_id = 'fw_extensions_install';

		if (!$this->can_install()) {
			FW_Flash_Messages::add(
				$flash_id,
				__('You are not allowed to install extensions.', 'fw'),
				'error'
			);
			$this->js_redirect();
			return;
		}

		if (array_key_exists('supported', $_GET)) {
			$supported = true;
			$extensions = array_fill_keys(
				array_keys($this->get_supported_extensions_for_install()),
				array()
			);

			if (empty($extensions)) {
				FW_Flash_Messages::add(
					$flash_id,
					__('All supported extensions are already installed.', 'fw'),
					'info'
				);
				$this->js_redirect();
				return;
			}
		} else {
			$supported = false;

			$extensions = array_fill_keys(
				array_map( 'trim', explode( ',', FW_Request::GET( 'extension', '' ) )),
				array()
			);

			// activate already installed extensions
			$this->activate_extensions($extensions);
		}

		{
			if (!class_exists('_FW_Extensions_Install_Upgrader_Skin')) {
				fw_include_file_isolated(
					dirname(__FILE__) .'/includes/class--fw-extensions-install-upgrader-skin.php'
				);
			}

			$skin = new _FW_Extensions_Install_Upgrader_Skin(array(
				'title' => $supported
					? _n('Install Compatible Extension', 'Install Compatible Extensions', count($extensions), 'fw')
					: _n('Install Extension', 'Install Extensions', count($extensions), 'fw'),
			));
		}

		$skin->header();

		do {
			$nonce = $this->get_nonce('install');

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				if (!isset($_POST[$nonce['name']]) || !wp_verify_nonce($_POST[$nonce['name']], $nonce['action'])) {
					$skin->error(__('Invalid nonce.', 'fw'));
				}

				if (!FW_WP_Filesystem::request_access(
					fw_get_framework_directory('/extensions'), fw_current_url(), array($nonce['name'])
				)) {
					break;
				}

				$install_result = $this->install_extensions($extensions, array('verbose' => $skin));

				if (is_wp_error($install_result)) {
					$skin->error($install_result);
				} elseif (is_array($install_result)) {
					$error = array();

					foreach ($install_result as $extension_name => $extension_result) {
						if (is_wp_error($extension_result)) {
							$error[] = $extension_result->get_error_message();
						}
					}

					$error = '<ul><li>'. implode('</li><li>', $error) .'</li></ul>';

					$skin->error($error);
				} elseif ($install_result === true) {
					$skin->set_result(true);
				}

				/** @var WP_Filesystem_Base $wp_filesystem */
				global $wp_filesystem;

				$wp_fs_tmp_dir = FW_WP_Filesystem::real_path_to_filesystem_path($this->get_tmp_dir());

				if ($wp_filesystem->exists($wp_fs_tmp_dir)) {
					if ( ! $wp_filesystem->rmdir( $wp_fs_tmp_dir, true ) ) {
						$skin->error(
							sprintf( __( 'Cannot remove temporary directory: %s', 'fw' ), $wp_fs_tmp_dir )
						);
					}
				}

				$skin->after(array(
					'extensions_page_link' => $this->get_link()
				));
			} else {
				echo '<form method="post">';

				wp_nonce_field($nonce['action'], $nonce['name']);

				$extension_titles = array();
				foreach ($extensions as $extension_name => $not_used_var) {
					$extension_titles[$extension_name] = $this->get_extension_title($extension_name);
				}

				fw_render_view(dirname(__FILE__) .'/views/install-form.php', array(
					'extension_titles' => $extension_titles,
					'list_page_link' => $this->get_link(),
					'supported' => $supported
				), false);

				echo '</form>';
			}
		} while(false);

		$skin->footer();
	}

	/**
	 * Download (and activate) extensions
	 * After refresh they should be active, if all dependencies will be met and if parent-extension::_init() will not return false
	 * @param array $extensions {'ext_1' => array(), 'ext_2' => array(), ...}
	 * @param array $opts
	 * @return WP_Error|bool|array
	 *         true:  when all extensions succeeded
	 *         array: when some/all failed
	 */
	public function install_extensions(array $extensions, $opts = array())
	{
		{
			$opts = array_merge(array(
				/**
				 * @type bool
				 * false: return {'ext_1' => true|WP_Error, 'ext_2' => true|WP_Error, ...}
				 * true:  return first WP_Error or true on success
				 */
				'cancel_on_error' => false,
				/**
				 * @type bool Activate installed extensions
				 */
				'activate' => true,
				/**
				 * @type bool|WP_Upgrader_Skin
				 */
				'verbose' => false,
			), $opts);

			$cancel_on_error = $opts['cancel_on_error']; // fixme: remove successfully installed extensions before error?
			$activate = $opts['activate'];
			$verbose = $opts['verbose'];

			unset($opts);
		}

		if (!$this->can_install()) {
			return new WP_Error(
				'access_denied',
				__('You have no permissions to install extensions', 'fw')
			);
		}

		if (empty($extensions)) {
			return new WP_Error(
				'no_extensions',
				__('No extensions provided', 'fw')
			);
		}

		global $wp_filesystem;

		if (!$wp_filesystem) {
			return new WP_Error(
				'fs_not_initialized',
				__('WP Filesystem is not initialized', 'fw')
			);
		}

		if (function_exists('ini_get')) {
			$timeout = intval(ini_get('max_execution_time'));
		} else {
			$timeout = false;
		}

		$available_extensions = $this->get_available_extensions();
		$installed_extensions = $this->get_installed_extensions();

		$result = $downloaded_extensions = array();
		$has_errors = false;

		while (!empty($extensions)) {
			$not_used_var = reset($extensions);
			$extension_name = key($extensions);
			unset($extensions[$extension_name]);

			$extensions_before_install = array_keys($installed_extensions);

			if (isset($installed_extensions[$extension_name])) {
				$result[$extension_name] = new WP_Error(
					'extension_installed',
					sprintf(__('Extension "%s" is already installed.', 'fw'), $this->get_extension_title($extension_name))
				);
				$has_errors = true;

				if ($cancel_on_error) {
					break;
				} else {
					continue;
				}
			}

			if (!isset($available_extensions[ $extension_name ])) {
				$result[$extension_name] = new WP_Error(
					'extension_not_available',
					sprintf(
						__('Extension "%s" is not available for install.', 'fw'),
						$this->get_extension_title($extension_name)
					)
				);
				$has_errors = true;

				if ($cancel_on_error) {
					break;
				} else {
					continue;
				}
			}

			/**
			 * Find parent extensions
			 * they will be installed if does not exist
			 */
			{
				$parents = array($extension_name);

				$current_parent = $extension_name;
				while (!empty($available_extensions[$current_parent]['parent'])) {
					$current_parent = $available_extensions[$current_parent]['parent'];

					if (!isset($available_extensions[$current_parent])) {
						$result[$extension_name] = new WP_Error(
							'parent_extension_not_available',
							sprintf(
								__('Parent extension "%s" not available.', 'fw'),
								$this->get_extension_title($current_parent)
							)
						);
						$has_errors = true;

						if ($cancel_on_error) {
							break 2;
						} else {
							continue 2;
						}
					}

					$parents[] = $current_parent;
				}

				$parents = array_reverse($parents);
			}

			/**
			 * Install parent extensions and the extension
			 */
			{
				$current_extension_path = fw_get_framework_directory();

				foreach ($parents as $parent_extension_name) {
					$current_extension_path .= '/extensions/'. $parent_extension_name;

					if (isset($installed_extensions[$parent_extension_name])) {
						// skip already installed extensions
						continue;
					}

					if ($verbose) {
						$verbose_message = sprintf(__('Downloading the "%s" extension...', 'fw'),
							$this->get_extension_title($parent_extension_name)
						);

						if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
							$verbose->feedback($verbose_message);
						} else {
							echo fw_html_tag('p', array(), $verbose_message);
						}
					}

					// increase timeout
					if ($timeout !== false && function_exists('set_time_limit')) {
						$timeout += 30;
						set_time_limit($timeout);
					}

					$wp_fw_downloaded_dir = $this->download(
						$parent_extension_name,
						$available_extensions[$parent_extension_name]
					);

					if (is_wp_error($wp_fw_downloaded_dir)) {
						if ($verbose) {
							$verbose_message = $wp_fw_downloaded_dir->get_error_message();

							if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
								$verbose->error($verbose_message);
							} else {
								echo fw_html_tag('p', array(), $verbose_message);
							}
						}

						$result[$extension_name] = $wp_fw_downloaded_dir;
						$has_errors = true;

						if ($cancel_on_error) {
							break 2;
						} else {
							continue 2;
						}
					}

					if ($verbose) {
						$verbose_message = sprintf(__('Installing the "%s" extension...', 'fw'),
							$this->get_extension_title($parent_extension_name)
						);

						if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
							$verbose->feedback($verbose_message);
						} else {
							echo fw_html_tag('p', array(), $verbose_message);
						}
					}

					$merge_result = $this->merge_extension(
						$wp_fw_downloaded_dir,
						FW_WP_Filesystem::real_path_to_filesystem_path($current_extension_path)
					);

					if (is_wp_error($merge_result)) {
						if ($verbose) {
							$verbose_message = $merge_result->get_error_message();

							if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
								$verbose->error($verbose_message);
							} else {
								echo fw_html_tag('p', array(), $verbose_message);
							}
						}

						$result[$extension_name] = $merge_result;
						$has_errors = true;

						if ($cancel_on_error) {
							break 2;
						} else {
							continue 2;
						}
					}

					if ($verbose) {
						$verbose_message = sprintf(__('The %s extension has been successfully installed.', 'fw'),
							$this->get_extension_title($parent_extension_name)
						);

						if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
							$verbose->feedback($verbose_message);
						} else {
							echo fw_html_tag('p', array(), $verbose_message);
						}
					}

					$downloaded_extensions[$parent_extension_name] = array();

					/**
					 * Read again all extensions
					 * The downloaded extension may contain more sub extensions
					 */
					{
						unset($installed_extensions);
						$installed_extensions = $this->get_installed_extensions(true);
					}
				}
			}

			$result[$extension_name] = true;

			/**
			 * Collect required extensions of the newly installed extensions
			 */
			foreach (
				// new extensions
				array_diff(
					array_keys($installed_extensions),
					$extensions_before_install
				)
				as $new_extension_name
			) {
				foreach (
					array_keys(
						fw_akg(
							'requirements/extensions',
							$installed_extensions[$new_extension_name]['manifest'],
							array()
						)
					)
					as $required_extension_name
				) {
					if (isset($installed_extensions[$required_extension_name])) {
						// already installed
						continue;
					}

					$extensions[$required_extension_name] = array();
				}
			}
		}

		if ($activate) {
			$activate_extensions = array();

			foreach ($result as $extension_name => $extension_result) {
				if (!is_wp_error($extension_result)) {
					$activate_extensions[$extension_name] = array();
				}
			}

			if (!empty($activate_extensions)) {
				if ($verbose) {
					$verbose_message = _n(
						'Activating extension...',
						'Activating extensions...',
						count($activate_extensions),
						'fw'
					);

					if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
						$verbose->feedback($verbose_message);
					} else {
						echo fw_html_tag('p', array(), $verbose_message);
					}
				}

				$activation_result = $this->activate_extensions($activate_extensions);

				if ($verbose) {
					if (is_wp_error($activation_result)) {
						if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
							$verbose->error($activation_result->get_error_message());
						} else {
							echo fw_html_tag('p', array(), $activation_result->get_error_message());
						}
					} elseif (is_array($activation_result)) {
						$verbose_message = array();

						foreach ($activation_result as $extension_name => $extension_result) {
							if (is_wp_error($extension_result)) {
								$verbose_message[] = $extension_result->get_error_message();
							}
						}

						$verbose_message = '<ul><li>' . implode('</li><li>', $verbose_message) . '</li></ul>';

						if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
							$verbose->error($verbose_message);
						} else {
							echo fw_html_tag('p', array(), $verbose_message);
						}
					} elseif ($activation_result === true) {
						$verbose_message = _n(
							'Extension has been successfully activated.',
							'Extensions has been successfully activated.',
							count($activate_extensions),
							'fw'
						);

						if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
							$verbose->feedback($verbose_message);
						} else {
							echo fw_html_tag('p', array(), $verbose_message);
						}
					}
				}
			}
		}

		do_action('fw_extensions_install', $result);

		if (
			$cancel_on_error
			&&
			$has_errors
		) {
			if (
				($last_result = end($result))
				&&
				is_wp_error($last_result)
			) {
				return $last_result;
			} else {
				// this should not happen, but just to be sure (for the future, if the code above will be changed)
				return new WP_Error(
					'installation_failed',
					_n('Cannot install extension', 'Cannot install extensions', count($extensions), 'fw')
				);
			}
		}

		if ($has_errors) {
			return $result;
		} else {
			return true;
		}
	}

	private function display_delete_page()
	{
		$flash_id = 'fw_extensions_delete';

		if (!$this->can_install()) {
			FW_Flash_Messages::add(
				$flash_id,
				__('You are not allowed to delete extensions.', 'fw'),
				'error'
			);
			$this->js_redirect();
			return;
		}

		$extensions = array_fill_keys(array_map('trim', explode(',', FW_Request::GET('extension', ''))), array());

		{
			if (!class_exists('_FW_Extensions_Delete_Upgrader_Skin')) {
				fw_include_file_isolated(
					dirname(__FILE__) .'/includes/class--fw-extensions-delete-upgrader-skin.php'
				);
			}

			$skin = new _FW_Extensions_Delete_Upgrader_Skin(array(
				'title' => _n('Delete Extension', 'Delete Extensions', count($extensions), 'fw'),
			));
		}

		$skin->header();

		do {
			$nonce = $this->get_nonce('delete');

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				if (!isset($_POST[$nonce['name']]) || !wp_verify_nonce($_POST[$nonce['name']], $nonce['action'])) {
					$skin->error(__('Invalid nonce.', 'fw'));
				}

				if (!FW_WP_Filesystem::request_access(
					fw_get_framework_directory('/extensions'), fw_current_url(), array($nonce['name'])
				)) {
					break;
				}

				$uninstall_result = $this->uninstall_extensions($extensions, array('verbose' => $skin));

				if (is_wp_error($uninstall_result)) {
					$skin->error($uninstall_result);
				} elseif (is_array($uninstall_result)) {
					$error = array();

					foreach ($uninstall_result as $extension_name => $extension_result) {
						if (is_wp_error($extension_result)) {
							$error[] = $extension_result->get_error_message();
						}
					}

					$error = '<ul><li>'. implode('</li><li>', $error) .'</li></ul>';

					$skin->error($error);
				} elseif ($uninstall_result === true) {
					$skin->set_result(true);
				}

				$skin->after(array(
					'extensions_page_link' => $this->get_link()
				));
			} else {
				echo '<form method="post">';

				wp_nonce_field($nonce['action'], $nonce['name']);

				fw_render_view(dirname(__FILE__) .'/views/delete-form.php', array(
					'extension_names' => array_keys($extensions),
					'installed_extensions' => $this->get_installed_extensions(),
					'list_page_link' => $this->get_link(),
				), false);

				echo '</form>';
			}
		} while(false);

		$skin->footer();
	}

	/**
	 * Remove extensions
	 * @param array $extensions {'ext_1' => array(), 'ext_2' => array(), ...}
	 * @param array $opts
	 * @return WP_Error|bool|array
	 *         true:  when all extensions succeeded
	 *         array: when some/all failed
	 */
	public function uninstall_extensions(array $extensions, $opts = array())
	{
		{
			$opts = array_merge(array(
				/**
				 * @type bool
				 * false: return {'ext_1' => true|WP_Error, 'ext_2' => true|WP_Error, ...}
				 * true:  return first WP_Error or true on success
				 */
				'cancel_on_error' => false,
				/**
				 * @type bool|WP_Upgrader_Skin
				 */
				'verbose' => false,
			), $opts);

			$cancel_on_error = $opts['cancel_on_error']; // fixme: install back successfully removed extensions before error?
			$verbose = $opts['verbose'];

			unset($opts);
		}

		if (!$this->can_install()) {
			return new WP_Error(
				'access_denied',
				__('You have no permissions to uninstall extensions', 'fw')
			);
		}

		if (empty($extensions)) {
			return new WP_Error(
				'no_extensions',
				__('No extensions provided', 'fw')
			);
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			return new WP_Error(
				'fs_not_initialized',
				__('WP Filesystem is not initialized', 'fw')
			);
		}

		$installed_extensions = $this->get_installed_extensions();
		$extensions_before_uninstall = array_fill_keys(array_keys($installed_extensions), array());

		$result = $uninstalled_extensions = array();
		$has_errors = false;

		while (!empty($extensions)) {
			$not_used_var = reset($extensions);
			$extension_name = key($extensions);
			unset($extensions[$extension_name]);

			$extension_title = $this->get_extension_title($extension_name);

			if (!isset($installed_extensions[ $extension_name ])) {
				// already deleted
				$result[$extension_name] = true;
				continue;
			}

			if (
				!isset($installed_extensions[ $extension_name ]['path'])
				||
				empty($installed_extensions[ $extension_name ]['path'])
			) {
				/**
				 * This happens sometimes, but I don't know why
				 * If the script will continue, it will delete the root folder
				 */
				fw_print(
					'Please report this to https://github.com/ThemeFuse/Unyson/issues',
					$extension_name,
					$installed_extensions
				);
				die;
			}

			$wp_fs_extension_path = FW_WP_Filesystem::real_path_to_filesystem_path(
				$installed_extensions[ $extension_name ]['path']
			);

			if (!$wp_filesystem->exists($wp_fs_extension_path)) {
				// already deleted, maybe because it was a sub-extension of an deleted extension
				$result[$extension_name] = true;
				continue;
			}

			if ($verbose) {
				$verbose_message = sprintf(__('Deleting the "%s" extension...', 'fw'), $extension_title);

				if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
					$verbose->feedback($verbose_message);
				} else {
					echo fw_html_tag('p', array(), $verbose_message);
				}
			}

			if (!$wp_filesystem->delete($wp_fs_extension_path, true, 'd')) {
				$result[$extension_name] = new WP_Error(
					'cannot_delete_directory',
					sprintf(__('Cannot delete the "%s" extension.', 'fw'), $extension_title)
				);
				$has_errors = true;

				if ($cancel_on_error) {
					break;
				} else {
					continue;
				}
			} else {
				if ($verbose) {
					$verbose_message = sprintf(
						__('The %s extension has been successfully deleted.', 'fw'),
						$extension_title
					);

					if (is_subclass_of($verbose, 'WP_Upgrader_Skin')) {
						$verbose->feedback($verbose_message);
					} else {
						echo fw_html_tag('p', array(), $verbose_message);
					}
				}

				$result[$extension_name] = true;
			}

			/**
			 * Read again all extensions
			 * The delete extension may contain more sub extensions
			 */
			{
				unset($installed_extensions);
				$installed_extensions = $this->get_installed_extensions(true);
			}

			/**
			 * Add for deletion not used extensions
			 * For e.g. standalone=false extension that were required by the deleted extension
			 *          and now are not required by any other extension
			 */
			{
				$not_used_extensions = array_fill_keys(
					array_keys(
						array_diff_key(
							$installed_extensions,
							$this->get_used_extensions($extensions, array_keys($installed_extensions))
						)
					),
					array()
				);

				$extensions = array_merge($extensions, $not_used_extensions);
			}
		}

		do_action('fw_extensions_uninstall', $result);

		if (
			$cancel_on_error
			&&
			$has_errors
		) {
			if (
				($last_result = end($result))
				&&
				is_wp_error($last_result)
			) {
				return $last_result;
			} else {
				// this should not happen, but just to be sure (for the future, if the code above will be changed)
				return new WP_Error(
					'uninstall_failed',
					_n('Cannot uninstall extension', 'Cannot uninstall extensions', count($extensions), 'fw')
				);
			}
		}

		// remove from active list the deleted extensions
		{
			update_option(
				fw()->extensions->_get_active_extensions_db_option_name(),
				array_diff_key(
					fw()->extensions->_get_db_active_extensions(),
					array_diff_key(
						$extensions_before_uninstall,
						$installed_extensions
					)
				)
			);
		}

		if ($has_errors) {
			return $result;
		} else {
			return true;
		}
	}

	private function display_extension_page()
	{
		$extension_name = trim(FW_Request::GET('extension', ''));

		$installed_extensions = $this->get_installed_extensions();

		$flash_id = 'fw_extension_page';

		{
			$error = '';

			do {
				if (empty($extension_name)) {
					$error = __('Extension not specified.', 'fw');
					break;
				}

				if (!isset($installed_extensions[$extension_name])) {
					$error = sprintf(__('Extension "%s" is not installed.', 'fw'), $this->get_extension_title($extension_name));
					break;
				}
			} while(false);

			if ($error) {
				FW_Flash_Messages::add($flash_id, $error, 'error');
				$this->js_redirect();
				return;
			}
		}

		{
			wp_enqueue_style(
				'fw-extension-page',
				$this->get_uri('/static/extension-page.css'),
				array('fw'),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				'fw-extension-page',
				$this->get_uri('/static/extension-page.js'),
				array('fw'),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			$tab = fw_akg('tab', $_GET, 'settings');

			if (!in_array($tab, array('settings', 'docs'))) {
				$tab = 'settings';
			}
		}

		$extension_title = $this->get_extension_title($extension_name);
		$link = $this->get_link();

		echo '<div class="wrap" id="fw-extension-page">';

		fw_render_view(dirname(__FILE__) .'/views/extension-page-header.php', array(
			'extension_name'  => $extension_name,
			'extension_data'  => $installed_extensions[$extension_name],
			'link_delete'     => $link .'&sub-page=delete',
			'link_extension'  => $link .'&sub-page=extension',
			'extension_title' => $extension_title,
			'tab'             => $tab,
			'is_supported'    =>
				fw()->theme->manifest->get('supported_extensions/'. $extension_name, false) !== false
				||
				$installed_extensions[$extension_name]['is']['theme']
		), false);

		unset($installed_extensions);

		echo '<div id="fw-extension-tab-content" style="opacity: 0;">';
		{
			$method_data = array();

			switch ($tab) {
				case 'settings':
					$error = $this->display_extension_settings_page($extension_name, $method_data);
					break;
				case 'docs':
					$error = $this->display_extension_docs_page($extension_name, $method_data);
					break;
			}
		}
		echo '</div>';

		echo '</div>';

		if ($error) {
			FW_Flash_Messages::add($flash_id, $error, 'error');
			$this->js_redirect();
			return;
		}
	}

	private function display_extension_settings_page($extension_name, $data)
	{
		if (!fw()->extensions->get($extension_name)) {
			return sprintf(
				__('Extension "%s" does not exist or is not active.', 'fw'),
				fw_htmlspecialchars($extension_name)
			);
		}

		$extension = fw()->extensions->get($extension_name);

		if (!$extension->get_settings_options()) {
			return sprintf(
				__('%s extension does not have settings.', 'fw'),
				$extension->manifest->get_name()
			);
		}

		echo '<div id="fw-extension-settings">';

		echo $this->extension_settings_form->render(array(
			'extension' => $extension,
		));

		echo '</div>';
	}

	private function display_extension_docs_page($extension_name, $data)
	{
		$installed_extensions = $this->get_installed_extensions();
		$docs_path = $installed_extensions[$extension_name]['path'] .'/readme.md.php';
		unset($installed_extensions);

		if (!file_exists($docs_path)) {
			return __('Extension has no Install Instructions', 'fw');
		}

		echo fw()->backend->render_box(
			'fw-extension-docs',
			'',
			fw()->backend->render_options(array(
				'docs' => array(
					'label' => false,
					'type'  => 'html-full',
					'html'  => $this->get_markdown_parser()->text(
						fw_render_view($docs_path, array())
					),
				),
			))
		);
	}

	private function display_activate_page()
	{
		$error = '';

		do {
			if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
				$error = __('Invalid request method.', 'fw');
				break;
			}

			$nonce = $this->get_nonce('activate');

			if (!wp_verify_nonce(FW_Request::POST($nonce['name']), $nonce['action'])) {
				$error = __('Invalid nonce.', 'fw');
				break;
			}

			if (!isset($_GET['extension'])) {
				$error = __('No extension specified.', 'fw');
				break;
			}

			$activation_result = $this->activate_extensions(
				array_fill_keys(explode(',', $_GET['extension']), array())
			);

			if (is_wp_error($activation_result)) {
				$error = $activation_result->get_error_message();
			} elseif (is_array($activation_result)) {
				$error = array();

				foreach ($activation_result as $extension_name => $extension_result) {
					if (is_wp_error($extension_result)) {
						$error[] = $extension_result->get_error_message();
					}
				}

				$error = '<ul><li>'. implode('</li><li>', $error) .'</li></ul>';
			}
		} while(false);

		if ($error) {
			FW_Flash_Messages::add(
				'fw_extensions_activate_page',
				$error,
				'error'
			);
			$this->js_redirect();
			return;
		}

		$this->js_redirect();
	}

	/**
	 * Add extensions to active extensions list in database
	 * After refresh they should be active, if all dependencies will be met and if parent-extension::_init() will not return false
	 * @param array $extensions {'ext_1' => array(), 'ext_2' => array(), ...}
	 * @param bool $cancel_on_error
	 *        false: return {'ext_1' => true|WP_Error, 'ext_2' => true|WP_Error, ...}
	 *        true:  return first WP_Error or true on success
	 * @return WP_Error|bool|array
	 *         true:  when all extensions succeeded
	 *         array: when some/all failed
	 */
	public function activate_extensions(array $extensions, $cancel_on_error = false)
	{
		if (!$this->can_activate()) {
			return new WP_Error(
				'access_denied',
				__('You have no permissions to activate extensions', 'fw')
			);
		}

		if (empty($extensions)) {
			return new WP_Error(
				'no_extensions',
				__('No extensions provided', 'fw')
			);
		}

		$installed_extensions = $this->get_installed_extensions();

		$result = $extensions_for_activation = array();
		$has_errors = false;

		foreach ($extensions as $extension_name => $not_used_var) {
			if (!isset($installed_extensions[$extension_name])) {
				$result[$extension_name] = new WP_Error(
					'extension_not_installed',
					sprintf(__('Extension "%s" does not exist.', 'fw'), $this->get_extension_title($extension_name))
				);
				$has_errors = true;

				if ($cancel_on_error) {
					break;
				} else {
					continue;
				}
			}

			$collected = $this->get_extensions_for_activation($extension_name);

			if (is_wp_error($collected)) {
				$result[$extension_name] = $collected;
				$has_errors = true;

				if ($cancel_on_error) {
					break;
				} else {
					continue;
				}
			}

			$extensions_for_activation = array_merge($extensions_for_activation, $collected);

			$result[$extension_name] = true;
		}

		if (
			$cancel_on_error
			&&
			$has_errors
		) {
			if (
				($last_result = end($result))
				&&
				is_wp_error($last_result)
			) {
				return $last_result;
			} else {
				// this should not happen, but just to be sure (for the future, if the code above will be changed)
				return new WP_Error(
					'activation_failed',
					_n('Cannot activate extension', 'Cannot activate extensions', count($extensions), 'fw')
				);
			}
		}

		update_option(
			fw()->extensions->_get_active_extensions_db_option_name(),
			array_merge(fw()->extensions->_get_db_active_extensions(), $extensions_for_activation)
		);

		// remove already active extensions
		foreach ($extensions_for_activation as $extension_name => $not_used_var) {
			if (fw_ext($extension_name)) {
				unset($extensions_for_activation[$extension_name]);
			}
		}

		/**
		 * Prepare db wp option used to fire the 'fw_extensions_after_activation' action on next refresh
		 */
		{
			$db_wp_option_name = 'fw_extensions_activation';
			$db_wp_option_value = get_option($db_wp_option_name, array(
				'activated' => array(),
				'deactivated' => array(),
			));

			/**
			 * Keep adding to the existing value instead of resetting it on each method call
			 * in case the method will be called multiple times
			 */
			$db_wp_option_value['activated'] = array_merge($db_wp_option_value['activated'], $extensions_for_activation);

			/**
			 * Remove activated extensions from deactivated
			 */
			$db_wp_option_value['deactivated'] = array_diff_key($db_wp_option_value['deactivated'], $db_wp_option_value['activated']);

			update_option($db_wp_option_name, $db_wp_option_value);
		}

		do_action('fw_extensions_before_activation', $extensions_for_activation);

		if ($has_errors) {
			return $result;
		} else {
			return true;
		}
	}

	private function collect_sub_extensions($ext_name, &$installed_extensions)
	{
		$result = array();

		foreach ($installed_extensions[$ext_name]['children'] as $child_ext_name => $child_ext_data) {
			$result[$child_ext_name] = array();

			$result += $this->collect_sub_extensions($child_ext_name, $installed_extensions);
		}

		return $result;
	}

	private function collect_required_extensions($ext_name, &$installed_extensions, &$collected)
	{
		if (!isset($installed_extensions[$ext_name])) {
			return;
		}

		foreach (fw_akg('requirements/extensions', $installed_extensions[$ext_name]['manifest'], array()) as $req_ext_name => $req_ext_data) {
			if (isset($collected[$req_ext_name])) {
				// prevent requirements recursion
				continue;
			}

			$collected[$req_ext_name] = array();

			$this->collect_required_extensions($req_ext_name, $installed_extensions, $collected);
		}
	}

	private function display_deactivate_page()
	{
		$installed_extensions = $this->get_installed_extensions();

		$error = '';

		do {
			if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
				$error = __('Invalid request method.', 'fw');
				break;
			}

			$nonce = $this->get_nonce('deactivate');

			if (!wp_verify_nonce(FW_Request::POST($nonce['name']), $nonce['action'])) {
				$error = __('Invalid nonce.', 'fw');
				break;
			}

			if (!isset($_GET['extension'])) {
				$error = __('No extension specified.', 'fw');
				break;
			}

			$deactivation_result = $this->deactivate_extensions(
				array_fill_keys(explode(',', $_GET['extension']), array())
			);

			if (is_wp_error($deactivation_result)) {
				$error = $deactivation_result->get_error_message();
			} elseif (is_array($deactivation_result)) {
				$error = array();

				foreach ($deactivation_result as $extension_name => $extension_result) {
					if (is_wp_error($extension_result)) {
						$error[] = $extension_result->get_error_message();
					}
				}

				$error = '<ul><li>'. implode('</li><li>', $error) .'</li></ul>';
			}
		} while(false);

		if ($error) {
			FW_Flash_Messages::add(
				'fw_extensions_activate_page',
				$error,
				'error'
			);
		}

		$this->js_redirect();
	}

	/**
	 * Remove extensions from active extensions list in database
	 * After refresh they will be inactive
	 * @param array $extensions {'ext_1' => array(), 'ext_2' => array(), ...}
	 * @param bool $cancel_on_error
	 *        false: return {'ext_1' => true|WP_Error, 'ext_2' => true|WP_Error, ...}
	 *        true:  return first WP_Error or true on success
	 * @return WP_Error|bool|array
	 *         true:  when all extensions succeeded
	 *         array: when some/all failed
	 */
	public function deactivate_extensions(array $extensions, $cancel_on_error = false)
	{
		if (!$this->can_activate()) {
			return new WP_Error(
				'access_denied',
				__('You have no permissions to deactivate extensions', 'fw')
			);
		}

		if (empty($extensions)) {
			return new WP_Error(
				'no_extensions',
				__('No extensions provided', 'fw')
			);
		}

		$installed_extensions = $this->get_installed_extensions();

		$result = $extensions_for_deactivation = array();
		$has_errors = false;

		foreach ($extensions as $extension_name => $not_used_var) {
			if (!isset($installed_extensions[$extension_name])) {
				// anyway remove from the active list
				$extensions_for_deactivation[$extension_name] = array();

				$result[$extension_name] = new WP_Error(
					'extension_not_installed',
					sprintf(__( 'Extension "%s" does not exist.' , 'fw' ), $this->get_extension_title($extension_name))
				);
				$has_errors = true;

				if ($cancel_on_error) {
					break;
				} else {
					continue;
				}
			}

			$current_deactivating_extensions = array(
				$extension_name => array()
			);

			// add sub-extensions for deactivation
			foreach ($this->collect_sub_extensions($extension_name, $installed_extensions) as $sub_extension_name => $sub_extension_data) {
				$current_deactivating_extensions[ $sub_extension_name ] = array();
			}

			// add extensions that requires deactivated extensions
			$this->collect_extensions_that_requires($current_deactivating_extensions, $current_deactivating_extensions);

			$extensions_for_deactivation = array_merge(
				$extensions_for_deactivation,
				$current_deactivating_extensions
			);

			unset($current_deactivating_extensions);

			$result[$extension_name] = true;
		}

		if (
			$cancel_on_error
			&&
			$has_errors
		) {
			if (
				($last_result = end($result))
				&&
				is_wp_error($last_result)
			) {
				return $last_result;
			} else {
				// this should not happen, but just to be sure (for the future, if the code above will be changed)
				return new WP_Error(
					'deactivation_failed',
					_n('Cannot deactivate extension', 'Cannot activate extensions', count($extensions), 'fw')
				);
			}
		}

		// add not used extensions for deactivation
		$extensions_for_deactivation = array_merge($extensions_for_deactivation,
			array_fill_keys(
				array_keys(
					array_diff_key(
						$installed_extensions,
						$this->get_used_extensions($extensions_for_deactivation, array_keys(fw()->extensions->get_all()))
					)
				),
				array()
			)
		);

		update_option(
			fw()->extensions->_get_active_extensions_db_option_name(),
			array_diff_key(
				fw()->extensions->_get_db_active_extensions(),
				$extensions_for_deactivation
			)
		);

		// remove already inactive extensions
		foreach ($extensions_for_deactivation as $extension_name => $not_used_var) {
			if (!fw_ext($extension_name)) {
				unset($extensions_for_deactivation[$extension_name]);
			}
		}

		/**
		 * Prepare db wp option used to fire the 'fw_extensions_after_deactivation' action on next refresh
		 */
		{
			$db_wp_option_name = 'fw_extensions_activation';
			$db_wp_option_value = get_option($db_wp_option_name, array(
				'activated' => array(),
				'deactivated' => array(),
			));

			/**
			 * Keep adding to the existing value instead of resetting it on each method call
			 * in case the method will be called multiple times
			 */
			$db_wp_option_value['deactivated'] = array_merge($db_wp_option_value['deactivated'], $extensions_for_deactivation);

			/**
			 * Remove deactivated extensions from activated
			 */
			$db_wp_option_value['activated'] = array_diff_key($db_wp_option_value['activated'], $db_wp_option_value['deactivated']);

			update_option($db_wp_option_name, $db_wp_option_value);
		}

		do_action('fw_extensions_before_deactivation', $extensions_for_deactivation);

		if ($has_errors) {
			return $result;
		} else {
			return true;
		}
	}

	/**
	 * @param array $data
	 * @return array
	 * @internal
	 */
	public function _extension_settings_form_render($data)
	{
		/**
		 * @var FW_Extension $extension
		 */
		$extension = $data['data']['extension'];

		do_action('fw_extension_settings_form_render:'. $extension->get_name());

		echo fw_html_tag('input', array(
			'type'  => 'hidden',
			'name'  => 'fw_extension_name',
			'value' => $extension->get_name(),
		), true);

		echo fw()->backend->render_options(
			$extension->get_settings_options(),
			fw_get_db_ext_settings_option($extension->get_name())
		);

		$data['submit']['html'] = '';

		echo '<p>';
		echo fw_html_tag('input', array(
			'type'  => 'submit',
			'class' => 'button-primary',
			'value' => __('Save', 'fw'),
		));
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		echo fw_html_tag('a', array(
			'href' => $this->get_link(),
		), __('Cancel', 'fw'));
		echo '</p>';

		return $data;
	}

	/**
	 * @param array $errors
	 * @return array
	 * @internal
	 */
	public function _extension_settings_form_validate($errors)
	{
		do {
			if (!current_user_can($this->can_activate())) {
				$errors[] = __('You are not allowed to save extensions settings.', 'fw');
				break;
			}

			$extension = fw()->extensions->get(FW_Request::POST('fw_extension_name'));

			if (!$extension) {
				$errors[] = __('Invalid extension.', 'fw');
				break;
			}

			if (!$extension->get_settings_options()) {
				$errors[] = __('Extension does not have settings options.', 'fw');
				break;
			}
		} while(false);

		return $errors;
	}

	/**
	 * @param array $data
	 * @return array
	 * @internal
	 */
	public function _extension_settings_form_save($data)
	{
		$extension = fw()->extensions->get(FW_Request::POST('fw_extension_name'));

		$options_before_save = (array)fw_get_db_ext_settings_option($extension->get_name());

		fw_set_db_ext_settings_option(
			$extension->get_name(),
			null,
			array_merge(
				$options_before_save,
				fw_get_options_values_from_input(
					$extension->get_settings_options()
				)
			)
		);

		FW_Flash_Messages::add(
			'fw_extension_settings_saved',
			__('Extensions settings successfully saved.', 'fw'),
			'success'
		);

		$data['redirect'] = fw_current_url();

		do_action('fw_extension_settings_form_saved:'. $extension->get_name(), $options_before_save);

		return $data;
	}

	/**
	 * Download an extension
	 *
	 * global $wp_filesystem; must me initialized
	 *
	 * @param string $extension_name
	 * @param array $data Extension data from the "available extensions" array
	 * @return string|WP_Error WP Filesystem path to the downloaded directory
	 */
	private function download($extension_name, $data)
	{
		$wp_error_id = 'fw_extension_download';

		if (empty($data['download'])) {
			return new WP_Error(
				$wp_error_id,
				sprintf(__('Extension "%s" has no download sources.', 'fw'), $this->get_extension_title($extension_name))
			);
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		// create temporary directory
		{
			$wp_fs_tmp_dir = FW_WP_Filesystem::real_path_to_filesystem_path($this->get_tmp_dir());

			if ($wp_filesystem->exists($wp_fs_tmp_dir)) {
				// just in case it already exists, clear everything, it may contain old files
				if (!$wp_filesystem->rmdir($wp_fs_tmp_dir, true)) {
					return new WP_Error(
						$wp_error_id,
						sprintf(__('Cannot remove temporary directory: %s', 'fw'), $wp_fs_tmp_dir)
					);
				}
			}

			if (!FW_WP_Filesystem::mkdir_recursive($wp_fs_tmp_dir)) {
				return new WP_Error(
					$wp_error_id,
					sprintf(__('Cannot create temporary directory: %s', 'fw'), $wp_fs_tmp_dir)
				);
			}
		}

		foreach ($data['download'] as $source => $source_data) {
			switch ($source) {
				case 'github':
					if (empty($source_data['user_repo'])) {
						return new WP_Error(
							$wp_error_id,
							sprintf(__('"%s" extension github source "user_repo" parameter is required', 'fw'), $this->get_extension_title($extension_name))
						);
					}

					{
						$transient_name = 'fw_ext_manager_gh_download';
						$transient_ttl  = HOUR_IN_SECONDS;

						$cache = get_site_transient($transient_name);

						if ($cache === false) {
							$cache = array();
						}
					}

					if (isset($cache[ $source_data['user_repo'] ])) {
						$download_link = $cache[ $source_data['user_repo'] ]['zipball_url'];
					} else {
						$http = new WP_Http();

						$response = $http->get(
							apply_filters('fw_github_api_url', 'https://api.github.com')
							. '/repos/'. $source_data['user_repo'] .'/releases'
						);

						unset($http);

						$response_code = intval(wp_remote_retrieve_response_code($response));

						if ($response_code !== 200) {
							if ($response_code === 403) {
								$json_response = json_decode($response['body'], true);

								if ($json_response) {
									return new WP_Error(
										$wp_error_id,
										__('Github error:', 'fw') .' '. $json_response['message']
									);
								}
							} elseif ($response_code) {
								return new WP_Error(
									$wp_error_id,
									sprintf(
										__( 'Failed to access Github repository "%s" releases. (Response code: %d)', 'fw' ),
										$source_data['user_repo'], $response_code
									)
								);
							} else {
								return new WP_Error(
									$wp_error_id,
									sprintf(
										__( 'Failed to access Github repository "%s" releases.', 'fw' ),
										$source_data['user_repo']
									)
								);
							}
						}

						$releases = json_decode($response['body'], true);

						unset($response);

						if (empty($releases)) {
							return new WP_Error(
								$wp_error_id,
								sprintf(
									__('"%s" extension github repository "%s" has no releases.', 'fw'),
									$this->get_extension_title($extension_name), $source_data['user_repo']
								)
							);
						}

						$release = reset($releases);

						unset($releases);

						{
							$cache[ $source_data['user_repo'] ] = array(
								'zipball_url' => 'https://github.com/'. $source_data['user_repo'] .'/archive/'. $release['tag_name'] .'.zip',
								'tag_name' => $release['tag_name']
							);

							set_site_transient($transient_name, $cache, $transient_ttl);
						}

						$download_link = $cache[ $source_data['user_repo'] ]['zipball_url'];

						unset($release);
					}

					{
						$http = new WP_Http();

						$response = $http->request($download_link, array(
							'timeout' => $this->download_timeout,
						));

						unset($http);

						if (($response_code = intval(wp_remote_retrieve_response_code($response))) !== 200) {
							if ($response_code) {
								return new WP_Error(
									$wp_error_id,
									sprintf( __( 'Cannot download the "%s" extension zip. (Response code: %d)', 'fw' ),
										$this->get_extension_title( $extension_name ), $response_code
									)
								);
							} elseif (is_wp_error($response)) {
								return new WP_Error(
									$wp_error_id,
									sprintf( __( 'Cannot download the "%s" extension zip. %s', 'fw' ),
										$this->get_extension_title( $extension_name ),
										$response->get_error_message()
									)
								);
							} else {
								return new WP_Error(
									$wp_error_id,
									sprintf( __( 'Cannot download the "%s" extension zip.', 'fw' ),
										$this->get_extension_title( $extension_name )
									)
								);
							}
						}

						$zip_path = $wp_fs_tmp_dir .'/temp.zip';

						// save zip to file
						if (!$wp_filesystem->put_contents($zip_path, $response['body'])) {
							return new WP_Error(
								$wp_error_id,
								sprintf(__('Cannot save the "%s" extension zip.', 'fw'), $this->get_extension_title($extension_name))
							);
						}

						unset($response);

						$unzip_result = unzip_file(
							FW_WP_Filesystem::filesystem_path_to_real_path($zip_path),
							$wp_fs_tmp_dir
						);

						if (is_wp_error($unzip_result)) {
							return $unzip_result;
						}

						// remove zip file
						if (!$wp_filesystem->delete($zip_path, false, 'f')) {
							return new WP_Error(
								$wp_error_id,
								sprintf(__('Cannot remove the "%s" extension downloaded zip.', 'fw'), $this->get_extension_title($extension_name))
							);
						}

						$unzipped_dir_files = $wp_filesystem->dirlist($wp_fs_tmp_dir);

						if (!$unzipped_dir_files) {
							return new WP_Error(
								$wp_error_id,
								__('Cannot access the unzipped directory files.', 'fw')
							);
						}

						/**
						 * get first found directory
						 * (if everything worked well, there should be only one directory)
						 */
						foreach ($unzipped_dir_files as $file) {
							if ($file['type'] == 'd') {
								return $wp_fs_tmp_dir .'/'. $file['name'];
							}
						}

						return new WP_Error(
							$wp_error_id,
							sprintf(__('The unzipped "%s" extension directory not found.', 'fw'), $this->get_extension_title($extension_name))
						);
					}
					break;
				default:
					return new WP_Error(
						$wp_error_id,
						sprintf(__('Unknown "%s" extension download source "%s"', 'fw'), $this->get_extension_title($extension_name), $source)
					);
			}
		}
	}

	/**
	 * Merge the downloaded extension directory with the existing directory
	 *
	 * @param string $source_wp_fs_dir Downloaded extension directory
	 * @param string $destination_wp_fs_dir
	 *
	 * @return null|WP_Error
	 */
	private function merge_extension($source_wp_fs_dir, $destination_wp_fs_dir)
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$wp_error_id = 'fw_extensions_merge';

		$source_files = $wp_filesystem->dirlist($source_wp_fs_dir);

		if ($source_files === false) {
			return new WP_Error(
				$wp_error_id,
				sprintf(__('Cannot read directory "%s".', 'fw'), $source_wp_fs_dir)
			);
		}

		if (empty($source_files)) {
			// directory is empty, nothing to move
			return;
		}

		/**
		 * Prepare destination directory
		 * Remove everything except the extensions/ directory
		 */
		if ($wp_filesystem->exists($destination_wp_fs_dir)) {
			$destination_files = $wp_filesystem->dirlist($destination_wp_fs_dir);

			if ($destination_files === false) {
				return new WP_Error(
					$wp_error_id,
					sprintf(__('Cannot read directory "%s".', 'fw'), $destination_wp_fs_dir)
				);
			}

			if (!empty($destination_files)) {
				// the directory contains some files, delete everything
				foreach ($destination_files as $file) {
					if ($file['name'] === 'extensions' && $file['type'] === 'd') {
						// do not touch the extensions/ directory
						continue;
					}

					if (!$wp_filesystem->delete($destination_wp_fs_dir .'/'. $file['name'], true, $file['type'])) {
						return new WP_Error(
							$wp_error_id,
							sprintf(__('Cannot delete "%s".', 'fw'), $destination_wp_fs_dir .'/'. $file['name'])
						);
					}
				}

				unset($destination_files);
			}
		} else {
			if (!FW_WP_Filesystem::mkdir_recursive($destination_wp_fs_dir)) {
				return new WP_Error(
					$wp_error_id,
					sprintf(__('Cannot create the "%s" directory.', 'fw'), $destination_wp_fs_dir)
				);
			}
		}

		$has_sub_extensions = false;

		foreach ($source_files as $file) {
			if ($file['name'] === 'extensions' && $file['type'] === 'd') {
				// do not touch the extensions/ directory
				$has_sub_extensions = true;
				continue;
			}

			if (!$wp_filesystem->move($source_wp_fs_dir .'/'. $file['name'], $destination_wp_fs_dir .'/'. $file['name'])) {
				return new WP_Error(
					$wp_error_id,
					sprintf(
						__('Cannot move "%s" to "%s".', 'fw'),
						$source_wp_fs_dir .'/'. $file['name'],
						$destination_wp_fs_dir .'/'. $file['name']
					)
				);
			}
		}

		unset($source_files);

		if (!$has_sub_extensions) {
			return;
		}

		$sub_extensions = $wp_filesystem->dirlist($source_wp_fs_dir .'/extensions');

		if ($sub_extensions === false) {
			return new WP_Error(
				$wp_error_id,
				sprintf(__('Cannot read directory "%s".', 'fw'), $source_wp_fs_dir .'/extensions')
			);
		}

		if (empty($sub_extensions)) {
			// directory is empty, nothing to remove
			return;
		}

		foreach ($sub_extensions as $file) {
			if ($file['type'] !== 'd') {
				// wrong, only directories must exist in the extensions/ directory
				continue;
			}

			$merge_result = $this->merge_extension(
				$source_wp_fs_dir .'/extensions/'. $file['name'],
				$destination_wp_fs_dir .'/extensions/'. $file['name']
			);

			if (is_wp_error($merge_result)) {
				return $merge_result;
			}
		}
	}

	private function get_supported_extensions_for_install()
	{
		$supported_extensions = fw()->theme->manifest->get('supported_extensions', array());

		if (empty($supported_extensions)) {
			return array();
		}

		// remove not available extensions
		$supported_extensions = array_intersect_key($supported_extensions, $this->get_available_extensions());

		if (empty($supported_extensions)) {
			return array();
		}

		// remove already installed extensions
		$supported_extensions = array_diff_key($supported_extensions, $this->get_installed_extensions());

		if (empty($supported_extensions)) {
			return array();
		}

		return $supported_extensions;
	}

	/**
	 * @param $actions
	 * @return array
	 * @internal
	 */
	public function _filter_plugin_action_list($actions)
	{
		return array_merge(
			array(
				'fw-extensions' => fw_html_tag('a', array(
					'href' => $this->get_link(),
				), fw()->manifest->get_name()),
			),
			$actions
		);
	}

	/**
	 * @return string Extensions page link
	 */
	private function get_link()
	{
		static $cache_link = null;

		if ($cache_link === null) {
			$cache_link = menu_page_url( $this->get_page_slug(), false );

			// https://core.trac.wordpress.org/ticket/28226
			if (is_multisite() && is_network_admin()) {
				$cache_link = self_admin_url(
					// extract relative link
					preg_replace('/^'. preg_quote(admin_url(), '/') .'/', '', $cache_link)
				);
			}
		}

		return $cache_link;
	}

	/**
	 * @param array $skip_extensions {'ext' => mixed}
	 * @param array $check_for_deps ['ext', 'ext', ...] Extensions to check if has in dependencies the used extensions
	 *
	 * @return array
	 */
	private function get_used_extensions($skip_extensions, $check_for_deps)
	{
		$used_extensions = array();

		$installed_extensions = $this->get_installed_extensions();

		foreach ($installed_extensions as $inst_ext_name => &$inst_ext_data) {
			if (isset($skip_extensions[ $inst_ext_name ])) {
				continue;
			}

			if (isset($used_extensions[$inst_ext_name])) {
				// already marked as used
				continue;
			}

			do {
				foreach ($check_for_deps as $deps_ext) {
					if (isset($skip_extensions[$deps_ext])) {
						continue;
					}

					if (false !== fw_akg(
						'requirements/extensions/'. $inst_ext_name,
						$installed_extensions[$deps_ext]['manifest'],
						false
					)) {
						// is required by an active extension
						break 2;
					}
				}

				if ( true === fw_akg(
					'standalone',
					$inst_ext_data['manifest'],
					$this->manifest_default_values['standalone']
				) ) {
					// can exist alone
					break;
				}

				// not used
				continue 2;
			} while(false);

			$used_extensions[$inst_ext_name] = array();

			// Set all sub-extensions as used
			foreach ($this->collect_sub_extensions($inst_ext_name, $installed_extensions) as $sub_extension_name => $sub_extension_data) {
				if (isset($skip_extensions[$sub_extension_name])) {
					continue;
				}

				$used_extensions[ $sub_extension_name ] = array();
			}

			// Set all parents as used
			{
				$current_parent = $inst_ext_name;
				while ($current_parent = $installed_extensions[$current_parent]['parent']) {
					$used_extensions[$current_parent] = array();
				}
			}
		}
		unset($inst_ext_data);

		// remove all skipped extensions and sub-extension from used extensions
		foreach (array_keys($skip_extensions) as $skip_extension_name) {
			unset($used_extensions[$skip_extension_name]);

			if (isset($installed_extensions[$skip_extension_name])) {
				foreach ($this->collect_sub_extensions($skip_extension_name, $installed_extensions) as $sub_extension_name => $sub_extension_data) {
					unset($used_extensions[$sub_extension_name]);
				}
			}
		}

		return $used_extensions;
	}

	/**
	 * @internal
	 */
	public function _action_admin_footer()
	{
		$this->activate_hidden_standalone_extensions();
	}

	public function get_extension_title($extension_name)
	{
		$installed_extensions = $this->get_installed_extensions();

		if (isset($installed_extensions[$extension_name])) {
			return fw_akg('name', $installed_extensions[$extension_name]['manifest'], fw_id_to_title($extension_name));
		}

		unset($installed_extensions);

		$available_extensions = $this->get_available_extensions();

		if (isset($available_extensions[$extension_name])) {
			return $available_extensions[$extension_name]['name'];
		}

		return fw_id_to_title($extension_name);
	}

	/**
	 * @internal
	 */
	public function _action_enqueue_menu_icon_style()
	{
		wp_enqueue_style(
			'fw-extensions-menu-icon',
			$this->get_uri('/static/unyson-font-icon/style.css'),
			array(),
			fw()->manifest->get_version()
		);
	}

	private function activate_theme_extensions()
	{
		$db_active_extensions = fw()->extensions->_get_db_active_extensions();

		foreach ($this->get_installed_extensions() as $extension_name => $extension) {
			if ($extension['is']['theme']) {
				$db_active_extensions[ $extension_name ] = array();
			}
		}

		update_option(
			fw()->extensions->_get_active_extensions_db_option_name(),
			$db_active_extensions
		);
	}

	/**
	 * @internal
	 */
	public function _action_theme_switch()
	{
		$this->activate_theme_extensions();
		$this->activate_extensions(
			array_fill_keys(
				array_keys(fw()->theme->manifest->get('supported_extensions', array())),
				array()
			)
		);
	}

	/**
	 * @param array $collected The found extensions {'extension_name' => array()}
	 * @param array $extensions {'extension_name' => array()}
	 * @param bool $check_all Check all extensions or only active extensions
	 */
	private function collect_extensions_that_requires(&$collected, $extensions, $check_all = false)
	{
		if (empty($extensions)) {
			return;
		}

		$found_extensions = array();

		foreach ($this->get_installed_extensions() as $extension_name => $extension_data) {
			if (isset($collected[$extension_name])) {
				continue;
			}

			if (!$check_all) {
				if (!fw_ext($extension_name)) {
					continue;
				}
			}

			if (
				array_intersect_key(
					$extensions,
					fw_akg(
						'requirements/extensions',
						$extension_data['manifest'],
						array()
					)
				)
			) {
				$found_extensions[$extension_name] = $collected[$extension_name] = array();
			}
		}

		$this->collect_extensions_that_requires($collected, $found_extensions, $check_all);
	}

	/**
	 * Get extension settings page link
	 * @param string $extension_name
	 * @return string
	 */
	public function get_extension_link($extension_name)
	{
		return $this->get_link() .'&sub-page=extension&extension='. $extension_name;
	}

	/**
	 * @param string $extension_name
	 * @return array|WP_Error Extensions to merge with db active extensions list
	 */
	private function get_extensions_for_activation($extension_name)
	{
		$installed_extensions = $this->get_installed_extensions();

		$wp_error_id = 'fw_ext_activation';

		if (!isset($installed_extensions[$extension_name])) {
			return new WP_Error($wp_error_id,
				sprintf(
					__('Cannot activate the %s extension because it is not installed. %s', 'fw'),
					$this->get_extension_title($extension_name),
					fw_html_tag('a', array(
						'href' => $this->get_link() .'&sub-page=install&extension='. $extension_name
					),  __('Install', 'fw'))
				)
			);
		}

		{
			$extension_parents = array($extension_name);

			$current_parent = $extension_name;
			while ($current_parent = $installed_extensions[$current_parent]['parent']) {
				$extension_parents[] = $current_parent;
			}

			$extension_parents = array_reverse($extension_parents);
		}

		$extensions = array();

		foreach ($extension_parents as $parent_extension_name) {
			$extensions[ $parent_extension_name ] = array();
		}

		// search sub-extensions
		foreach ($this->collect_sub_extensions($extension_name, $installed_extensions) as $sub_extension_name => $sub_extension_data) {
			$extensions[ $sub_extension_name ] = array();
		}

		// search required extensions
		{
			$pending_required_search = $extensions;

			while ($pending_required_search) {
				foreach (array_keys($pending_required_search) as $pend_req_extension_name) {
					unset($pending_required_search[$pend_req_extension_name]);

					unset($required_extensions); // reset reference
					$required_extensions = array();
					$this->collect_required_extensions($pend_req_extension_name, $installed_extensions, $required_extensions);

					foreach ($required_extensions as $required_extension_name => $required_extension_data) {
						if (!isset($installed_extensions[$required_extension_name])) {
							return new WP_Error($wp_error_id,
								sprintf(
									__('Cannot activate the %s extension because it is not installed. %s', 'fw'),
									$this->get_extension_title($required_extension_name),
									fw_html_tag('a', array(
										'href' => $this->get_link() .'&sub-page=install&extension='. $required_extension_name
									),  __('Install', 'fw'))
								)
							);
						}

						$extensions[$required_extension_name] = array();

						// search sub-extensions
						foreach ($this->collect_sub_extensions($required_extension_name, $installed_extensions) as $sub_extension_name => $sub_extension_data) {
							if (isset($extensions[$sub_extension_name])) {
								continue;
							}

							$extensions[$sub_extension_name] = array();

							$pending_required_search[$sub_extension_name] = array();
						}
					}
				}
			}
		}

		return $extensions;
	}
}
