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

	private $manifest_default_values = array(
		'display' => false,
		'standalone' => false,
	);

	private $default_thumbnail = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIW2PUsHf9DwAC8AGtfm5YCAAAAABJRU5ErkJgggAA';

	/**
	 * @var FW_Access_Key
	 */
	private static $access_key;

	private static function get_access_key() {
		if (!self::$access_key) {
			self::$access_key = new FW_Access_Key('fw_ext_manager');
		}

		return self::$access_key;
	}

	public function __construct()
	{
		// In any case/permission, make sure to not miss the plugin update actions to prevent extensions delete
		{
			add_action('fw_plugin_pre_update', array($this, '_action_plugin_pre_update'));
			add_action('fw_plugin_post_update', array($this, '_action_plugin_post_update'));
		}

		// Preserve {theme}/framework-customizations/theme/available-extensions.php
		{
			add_filter('upgrader_pre_install',  array($this, '_filter_theme_available_extensions_copy'), 999, 2);

			/**
			 * Must be executed after
			 * https://github.com/WordPress/WordPress/blob/4.6/wp-admin/includes/class-theme-upgrader.php#L204-L205
			 */
			add_action('upgrader_process_complete', array($this, '_action_theme_available_extensions_restore'), 999, 2);
		}

		add_action('fw_plugin_activate', array($this, '_action_plugin_activate_install_compatible_extensions'), 100);
		add_action('fw_after_plugin_activate', array($this, '_action_after_plugin_activate'), 100);
		add_action('after_switch_theme', array($this, '_action_theme_switch'), '');

		if (!is_admin()) {
			return;
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
			add_action('admin_enqueue_scripts', array($this, '_action_enqueue_scripts'));
			add_action('admin_notices', array($this, '_action_admin_notices'));

			if ($this->can_install()) {
				add_action('wp_ajax_fw_extensions_check_direct_fs_access', array($this, '_action_ajax_check_direct_fs_access'));
				add_action('wp_ajax_fw_extensions_install', array($this, '_action_ajax_install'));
				add_action('wp_ajax_fw_extensions_uninstall', array($this, '_action_ajax_uninstall'));
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
		if ( fw_is_cli() ) {
			return true;
		}

		$can_activate = current_user_can('manage_options');

		if ($can_activate) {
			// also you can use this method to get the capability
			$can_activate = 'manage_options';
		}

		if (!$can_activate) {
			// make sure if can install, then also can activate. (can install) > (can activate)
			$can_activate = $this->can_install();
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
		if ( fw_is_cli() ) {
			return true;
		}

		$capability = 'install_plugins';

		if (is_multisite()) {
			// only network admin can change files that affects the entire network
			$can_install = current_user_can_for_blog(get_current_blog_id(), $capability);
		} else {
			$can_install = current_user_can($capability);
		}

		if ($can_install) {
			// also you can use this method to get the capability
			$can_install = $capability;
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
	 *
	 * @since 2.6.9
	 */
	public function get_available_extensions() {
		try {
			$cache_key = $this->get_cache_key( 'available_extensions' );

			return FW_Cache::get( $cache_key );
		} catch ( FW_Cache_Not_Found_Exception $e ) {
			$available = fw_get_variables_from_file( dirname( __FILE__ ) . '/available-extensions.php', array( 'extensions' => array() ) );
			$available = $available['extensions'];

			// Allow theme to register available extensions
			$theme_available_ext_file = fw_fix_path( get_template_directory() ) . apply_filters('fw_theme_available_extensions_file_path', fw_get_framework_customizations_dir_rel_path( '/theme/available-extensions.php' ));

			if ( file_exists( $theme_available_ext_file ) ) {

				$register = new _FW_Available_Extensions_Register( self::get_access_key()->get_key() );

				/**
				 * Usage: https://github.com/ThemeFuse/Unyson/issues/2900
				 * Create {theme}/framework-customizations/theme/available-extensions.php with the following contents:
				 * $extension = new FW_Available_Extension();
				 * $extension->set_...();
				 * $register->register($extension);
				 */
				$theme_exts = fw_get_variables_from_file( $theme_available_ext_file, array( 'extensions' => array() ), array( 'register' => $register ) );
				$available = array_merge( $available, $theme_exts['extensions'] );

				foreach ( $register->_get_types( self::$access_key ) as $extension ) {
					/** @var FW_Available_Extension $extension */
					if ( isset( $available[ $extension->get_name() ] ) ) {
						trigger_error(
							'Overwriting default extension "' . $extension->get_name() . '" is not allowed',
							E_USER_WARNING
						);
						continue;
					} elseif ( ! $extension->is_valid() ) {
						trigger_error(
							'Theme extension "' . $extension->get_name() . '" is not valid',
							E_USER_WARNING
						);
						continue;
					} else {
						$available[ $extension->get_name() ] = array(
							'theme'       => true, // Registered by theme
							'display'     => $extension->get_display(),
							'parent'      => $extension->get_parent(),
							'name'        => $extension->get_title(),
							'description' => $extension->get_description(),
							'thumbnail'   => $extension->get_thumbnail(),
							'download'    => $extension->get_download_source(),
						);
					}
				}
			}

			{
				$installed_extensions = $this->get_installed_extensions();
				$supported_extensions = fw()->theme->manifest->get( 'supported_extensions', array() );

				if ( isset( $installed_extensions['backup'] ) ) {
					// make sure only Backup or Backups can be installed
					unset( $available['backups'] );
				}

				foreach ( array( 'backup', 'styling', 'learning' ) as $obsolete_extension ) {
					if (
						! isset( $supported_extensions[ $obsolete_extension ] )
						&&
						! isset( $installed_extensions[ $obsolete_extension ] )
					) {
						unset( $available[ $obsolete_extension ] );
					}
				}
			}

			FW_Cache::set( $cache_key, $available );

			return $available;
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
	public function _action_ajax_install()
	{
		if ( ! $this->can_install() ) {
			// if can't install, no need to know if has access or not
			wp_send_json_error();
		}

		if ( ! FW_WP_Filesystem::has_direct_access( fw_get_framework_directory( '/extensions' ) ) ) {
			wp_send_json_error();
		}

		$extension = (string) FW_Request::POST( 'extension' );

		$install_result = $this->install_extensions( array( $extension => array() ), array( 'cancel_on_error' => true ) );

		if ( $install_result === true ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( $install_result );
		}
	}

	/**
	 * @internal
	 */
	public function _action_ajax_uninstall()
	{
		if (!$this->can_install()) {
			// if can't install, no need to know if has access or not
			wp_send_json_error();
		}

		if (!FW_WP_Filesystem::has_direct_access(fw_get_framework_directory('/extensions'))) {
			wp_send_json_error();
		}

		$extension = (string)FW_Request::POST('extension');

		$install_result = $this->uninstall_extensions(array(
			$extension => array()
		), array(
			'cancel_on_error' => true
		));

		if ($install_result === true) {
			wp_send_json_success();
		} else {
			wp_send_json_error($install_result);
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

		do_action('fw_after_plugin_activate:before_potential_redirect');

		if (is_admin() && $this->can_install() && $this->get_supported_extensions_for_install()) {
			wp_redirect($this->get_link() . '&sub-page=install&supported');
			exit;
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

		if (!FW_WP_Filesystem::is_ready()) {
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

		if (!FW_WP_Filesystem::is_ready()) {
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
	 *
	 * @since 2.6.9
	 */
	public function get_installed_extensions($reset_cache = false)
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
					if ($this->is_extensions_page()) {
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

		if (! apply_filters('fw_backend_enable_custom_extensions_menu', true)) {
			return;
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
		// note: static is enqueued in 'admin_enqueue_scripts' action

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
					'theme' => isset($ext_data['theme']) && $ext_data['theme'],
                    'download' => isset( $ext_data['download'] ) ? $ext_data['download'] : array()
				);

				if ($lists['available'][$ext_name]['theme']) {
					$lists['supported'][$ext_name] = array(
						'name' => $lists['available'][$ext_name]['name'],
						'description' => $lists['available'][$ext_name]['description'],
					);
				}
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

		echo '<div id="fw-extensions-list-wrapper">';

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
					break;
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

				if ($supported && $install_result === true) {
					/**
					 * @since 2.6.14
					 * Fixes https://github.com/ThemeFuse/Unyson/issues/2330
					 */
					do_action( 'fw_after_supported_extensions_install_success' );
				}
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
	public function install_extensions( array $extensions, $opts = array() ) {
		{
			$opts = array_merge( array(
				/**
				 * @type bool
				 * false: return {'ext_1' => true|WP_Error, 'ext_2' => true|WP_Error, ...}
				 * true:  return first WP_Error or true on success
				 */
				'cancel_on_error' => false,
				/**
				 * @type bool Activate installed extensions
				 */
				'activate'        => true,
				/**
				 * @type bool|WP_Upgrader_Skin
				 */
				'verbose'         => false,
			), $opts );

			$cancel_on_error = $opts['cancel_on_error']; // fixme: remove successfully installed extensions before error?
			$activate        = $opts['activate'];
			$verbose         = $opts['verbose'];

			unset( $opts );
		}

		if ( ! $this->can_install() ) {
			return new WP_Error(
				'access_denied',
				__( 'You have no permissions to install extensions', 'fw' )
			);
		}

		if ( empty( $extensions ) ) {
			return new WP_Error(
				'no_extensions',
				__( 'No extensions provided', 'fw' )
			);
		}

		if ( ! FW_WP_Filesystem::is_ready() ) {
		    FW_WP_Filesystem::init_file_system();
		}

		$timeout              = function_exists( 'ini_get' ) ? intval( ini_get( 'max_execution_time' ) ) : false;
		$available_extensions = $this->get_available_extensions();
		$installed_extensions = $this->get_installed_extensions();
		$result               = $downloaded_extensions = array();
		$has_errors           = false;

		while ( ! empty( $extensions ) ) {
			reset( $extensions );
			$extension_name = key( $extensions );
			unset( $extensions[ $extension_name ] );

			$extensions_before_install = array_keys( $installed_extensions );

			if ( isset( $installed_extensions[ $extension_name ] ) ) {
				$result[ $extension_name ] = new WP_Error(
					'extension_installed',
					sprintf( __( 'Extension "%s" is already installed.', 'fw' ), $this->get_extension_title( $extension_name ) )
				);
				$has_errors                = true;

				if ( $cancel_on_error ) {
					break;
				}
			}

			if ( ! isset( $available_extensions[ $extension_name ] ) ) {
				$result[ $extension_name ] = new WP_Error(
					'extension_not_available',
					sprintf(
						__( 'Extension "%s" is not available for install.', 'fw' ),
						$this->get_extension_title( $extension_name )
					)
				);
				$has_errors                = true;

				if ( $cancel_on_error ) {
					break;
				}
			}

			/**
			 * Find parent extensions
			 * they will be installed if does not exist
			 */
			{
				$parents = array( $extension_name );

				$current_parent = $extension_name;
				while ( ! empty( $available_extensions[ $current_parent ]['parent'] ) ) {
					$current_parent = $available_extensions[ $current_parent ]['parent'];

					if ( ! isset( $available_extensions[ $current_parent ] ) ) {
						$result[ $extension_name ] = new WP_Error(
							'parent_extension_not_available',
							sprintf(
								__( 'Parent extension "%s" not available.', 'fw' ),
								$this->get_extension_title( $current_parent )
							)
						);
						$has_errors                = true;

						if ( $cancel_on_error ) {
							break 2;
						} else {
							continue 2;
						}
					}

					$parents[] = $current_parent;
				}

				$parents = array_reverse( $parents );
			}

			/**
			 * Install parent extensions and the extension
			 */
			{
				$destination_path = array(
					'framework' => fw_get_framework_directory(),
					'theme'     => fw_fix_path( get_template_directory() ) . fw_get_framework_customizations_dir_rel_path(),
				);
				$current_extension_path = '';

				foreach ( $parents as $parent_extension_name ) {
					$current_extension_path .= '/extensions/' . $parent_extension_name;
					$set = $available_extensions[ $parent_extension_name ];
                    $destination = isset( $set['theme'] ) && $set['theme'] ? 'theme' : 'framework';

					if ( isset( $installed_extensions[ $parent_extension_name ] ) ) {
						continue; // skip already installed extensions
					}

					if ( $verbose ) {
						$verbose_message = sprintf( esc_html__( 'Downloading the "%s" extension...', 'fw' ), $this->get_extension_title( $parent_extension_name ) );

						if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
							$verbose->feedback( $verbose_message );
						} else {
							echo fw_html_tag( 'p', array(), $verbose_message );
						}
					}

					// increase timeout
					if ( $timeout !== false && function_exists( 'set_time_limit' ) ) {
						$timeout += 30;
						set_time_limit( $timeout );
					}

					// If is plugin will returned downloadable link zip.
					$wp_fw_downloaded_dir = $this->download( $parent_extension_name, $set );

					if ( is_wp_error( $wp_fw_downloaded_dir ) ) {
						if ( $verbose ) {
							$verbose_message = $wp_fw_downloaded_dir->get_error_message();

							if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
								$verbose->error( $verbose_message );
							} else {
								echo fw_html_tag( 'p', array(), $verbose_message );
							}
						}

						$result[ $extension_name ] = $wp_fw_downloaded_dir;
						$has_errors                = true;

						if ( $cancel_on_error ) {
							break 2;
						} else {
							continue 2;
						}
					}

					if ( $verbose ) {
						$verbose_message = sprintf( esc_html__( 'Installing the "%s" extension...', 'fw' ), $this->get_extension_title( $parent_extension_name ) );

						if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
							$verbose->feedback( $verbose_message );
						} else {
							echo fw_html_tag( 'p', array(), $verbose_message );
						}
					}

					// Merge directories only for extensions. If we have plugin it will installed via Plugin_Upgrader.
					if ( empty( $set['download']['opts']['plugin'] ) ) {
						$merge_result = $this->merge_extension(
							$wp_fw_downloaded_dir,
							FW_WP_Filesystem::real_path_to_filesystem_path( $destination_path[ $destination ] . $current_extension_path )
						);

						if ( is_wp_error( $merge_result ) ) {
							if ( $verbose ) {
								$verbose_message = $merge_result->get_error_message();

								if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
									$verbose->error( $verbose_message );
								} else {
									echo fw_html_tag( 'p', array(), $verbose_message );
								}
							}

							$result[ $extension_name ] = $merge_result;
							$has_errors                = true;

							if ( $cancel_on_error ) {
								break 2;
							} else {
								continue 2;
							}
						}
                    }

					if ( $verbose ) {
						$verbose_message = sprintf( __( 'The %s extension has been successfully installed.', 'fw' ),
							$this->get_extension_title( $parent_extension_name )
						);

						if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
							$verbose->feedback( $verbose_message );
						} else {
							echo fw_html_tag( 'p', array(), $verbose_message );
						}
					}

					$downloaded_extensions[ $parent_extension_name ] = array();

					/**
					 * Read again all extensions
					 * The downloaded extension may contain more sub extensions
					 */
					{
						unset( $installed_extensions );
						$installed_extensions = $this->get_installed_extensions( true );
					}
				}
			}

			$result[ $extension_name ] = true;

			/**
			 * Collect required extensions of the newly installed extensions
			 */
			foreach (
				// new extensions
				array_diff(
					array_keys( $installed_extensions ),
					$extensions_before_install
				)
				as $new_extension_name
			) {
				foreach (
					array_keys(
						fw_akg(
							'requirements/extensions',
							$installed_extensions[ $new_extension_name ]['manifest'],
							array()
						)
					)
					as $required_extension_name
				) {
					if ( isset( $installed_extensions[ $required_extension_name ] ) ) {
						// already installed
						continue;
					}

					$extensions[ $required_extension_name ] = array();
				}
			}
		}

		if ( $activate ) {
			$activate_extensions = array();

			foreach ( $result as $extension_name => $extension_result ) {
				if ( ! is_wp_error( $extension_result ) ) {
					$activate_extensions[ $extension_name ] = array();
				}
			}

			if ( ! empty( $activate_extensions ) ) {
				if ( $verbose ) {
					$verbose_message = _n(
						'Activating extension...',
						'Activating extensions...',
						count( $activate_extensions ),
						'fw'
					);

					if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
						$verbose->feedback( $verbose_message );
					} else {
						echo fw_html_tag( 'p', array(), $verbose_message );
					}
				}

				$activation_result = $this->activate_extensions( $activate_extensions );

				if ( $verbose ) {
					if ( is_wp_error( $activation_result ) ) {
						if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
							$verbose->error( $activation_result->get_error_message() );
						} else {
							echo fw_html_tag( 'p', array(), $activation_result->get_error_message() );
						}
					} elseif ( is_array( $activation_result ) ) {
						$verbose_message = array();

						foreach ( $activation_result as $extension_name => $extension_result ) {
							if ( is_wp_error( $extension_result ) ) {
								$verbose_message[] = $extension_result->get_error_message();
							}
						}

						$verbose_message = '<ul><li>' . implode( '</li><li>', $verbose_message ) . '</li></ul>';

						if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
							$verbose->error( $verbose_message );
						} else {
							echo fw_html_tag( 'p', array(), $verbose_message );
						}
					} elseif ( $activation_result === true ) {
						$verbose_message = _n(
							'Extension has been successfully activated.',
							'Extensions has been successfully activated.',
							count( $activate_extensions ),
							'fw'
						);

						if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
							$verbose->feedback( $verbose_message );
						} else {
							echo fw_html_tag( 'p', array(), $verbose_message );
						}
					}
				}
			}
		}

		do_action( 'fw_extensions_install', $result );

		if ( $cancel_on_error && $has_errors ) {
			if ( ( $last_result = end( $result ) ) && is_wp_error( $last_result ) ) {
				return $last_result;
			} else {
				// this should not happen, but just to be sure (for the future, if the code above will be changed)
				return new WP_Error(
					'installation_failed',
					_n( 'Cannot install extension', 'Cannot install extensions', count( $extensions ), 'fw' )
				);
			}
		}

		return $has_errors ? $result : true;
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
					break;
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

		if (!FW_WP_Filesystem::is_ready()) {
			return new WP_Error(
				'fs_not_initialized',
				__('WP Filesystem is not initialized', 'fw')
			);
		}

		$installed_extensions        = $this->get_installed_extensions();
		$available_extensions        = $this->get_available_extensions();
		$extensions_before_uninstall = array_fill_keys( array_keys( $installed_extensions ), array() );

		$result = $uninstalled_extensions = array();
		$has_errors = false;

		while ( ! empty( $extensions ) ) {

			reset( $extensions );
			$extension_name = key( $extensions );
			unset( $extensions[ $extension_name ] );

			if ( ! empty( $available_extensions[ $extension_name ]['download']['opts']['plugin'] ) ) {
				$unistall     = delete_plugins( (array) $available_extensions[ $extension_name ]['download']['opts']['plugin'] );
                $plugin_title = $available_extensions[ $extension_name ]['name'];

				if ( $unistall ) {
					$this->verbose( sprintf( esc_html__( 'Extension "%s" has been deleted.', 'fw' ), $plugin_title ), $verbose );
					$result[ $extension_name ] = true;
                } else {
				    if ( is_wp_error( $unistall ) ) {
					    $msg_error = $unistall->get_error_message() . ' - ' . $plugin_title;
                    } else {
					    $msg_error = sprintf( esc_html__( 'Plugin %s is empty.' ), $plugin_title );
                    }

					$result[ $extension_name ] = new WP_Error( 'fw_delete_plugins', $msg_error, $plugin_title );
					$has_errors = true;

					if ( $cancel_on_error ) {
						break;
					} else {
						continue;
					}
                }

				continue;
			}

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

			$this->verbose( sprintf( esc_html__( 'Deleting the "%s" extension...', 'fw' ), $extension_title ), $verbose );

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
				$this->verbose( sprintf( esc_html__( 'The %s extension has been successfully deleted.', 'fw' ), $extension_title ), $verbose );
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

	public function verbose( $msg, &$verbose ) {

	    if ( ! $verbose ) {
	        return;
        }

		if ( is_subclass_of( $verbose, 'WP_Upgrader_Skin' ) ) {
			$verbose->feedback( $msg );
		} else {
			echo fw_html_tag( 'p', array(), $msg );
		}
	}

	private function display_extension_page()
	{
		// note: static is enqueued in 'admin_enqueue_scripts' action

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
					$error = sprintf(__('Extension "%s" is not installed.', 'fw'), esc_html( $this->get_extension_title($extension_name) ));
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

		echo '<div id="fw-extension-tab-content">';
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
		$ext = fw_ext($extension_name);
		$docs = $ext->get_rendered_docs();

		if (! $docs) {
			return __(
				'Extension has no documentation. Maybe ask its developer to write some?',
				'fw'
			);
		}

		echo fw()->backend->render_box(
			'fw-extension-docs',
			'',
			fw()->backend->render_options(array(
				'docs' => array(
					'label' => false,
					'type'  => 'html-full',
					'html'  => $docs
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
		$available_extensions = $this->get_available_extensions();

		$result = $extensions_for_activation = array();
		$has_errors = false;

		foreach ($extensions as $extension_name => $not_used_var) {

			if ( ! empty( $available_extensions[ $extension_name ]['download']['opts']['plugin'] ) ) {

			    $plugin_file = $available_extensions[ $extension_name ]['download']['opts']['plugin'];

				// A small financial support for maintaining the plugin.
				if ( 'translatepress-multilingual/index.php' === $plugin_file ) {
					update_option( 'translatepress_affiliate_id', 1 );
				}

				activate_plugin( $plugin_file );

				continue;
			}

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

			update_option($db_wp_option_name, $db_wp_option_value, false);
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

		$available_extensions = $this->get_available_extensions();
		$installed_extensions = $this->get_installed_extensions();

		$result = $extensions_for_deactivation = array();
		$has_errors = false;

		foreach ($extensions as $extension_name => $not_used_var) {

		    if ( ! empty( $available_extensions[ $extension_name ]['download']['opts']['plugin'] ) ) {
			    deactivate_plugins( plugin_basename( $available_extensions[ $extension_name ]['download']['opts']['plugin'] ) );
			    continue;
            }


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

			update_option($db_wp_option_name, $db_wp_option_value, false);
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
	 * global $wp_filesystem; must be initialized
	 *
	 * @param string $extension_name
	 * @param array $data Extension data from the "available extensions" array
	 * @return string|WP_Error WP Filesystem path to the downloaded directory
	 */
	private function download( $extension_name, $data ) {
		global $wp_filesystem;
		$wp_error_id = 'fw_extension_download';

		if ( empty( $data['download'] ) ) {
			return new WP_Error(
				$wp_error_id,
				sprintf( __( 'Extension "%s" has no download sources.', 'fw' ), $this->get_extension_title( $extension_name ) )
			);
		}

		$opts = array_merge( array(
            'item'            => $extension_name,
			'extension_name'  => $extension_name,
			'extension_title' => $this->get_extension_title( $extension_name )
		), $data['download']['opts'] );

		if ( isset( $opts['plugin'] ) && is_plugin_active( $opts['plugin'] ) ) {
			return '';
		}

		if ( ( $download_source = $this->get_download_source( $data ) ) && is_wp_error( $download_source ) ) {
			return $download_source;
		}

		if ( isset( $opts['plugin'] ) ) {
			return $download_source->download( $opts, '' );
        }

		// create temporary directory
		$wp_fs_tmp_dir = FW_WP_Filesystem::real_path_to_filesystem_path( $this->get_tmp_dir() );

		if ( $wp_filesystem->exists( $wp_fs_tmp_dir ) ) {
			// just in case it already exists, clear everything, it may contain old files
			if ( ! $wp_filesystem->rmdir( $wp_fs_tmp_dir, true ) ) {
				return new WP_Error(
					$wp_error_id,
					sprintf( __( 'Cannot remove temporary directory: %s', 'fw' ), $wp_fs_tmp_dir )
				);
			}
		}

		if ( ! FW_WP_Filesystem::mkdir_recursive( $wp_fs_tmp_dir ) ) {
			return new WP_Error(
				$wp_error_id,
				sprintf( __( 'Cannot create temporary directory: %s', 'fw' ), $wp_fs_tmp_dir )
			);
		}

		return $this->perform_zip_download( $download_source, $opts, $wp_fs_tmp_dir );
	}

	private function perform_zip_download( FW_Ext_Download_Source $download_source, array $opts, $wp_fs_tmp_dir ) {
		$wp_error_id = 'fw_extension_download';

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$zip_path = $wp_fs_tmp_dir . '/temp.zip';

		$download_result = $download_source->download( $opts, $zip_path );

		/**
		 * Pass further the error, if the service returned one.
		 */
		if ( is_wp_error( $download_result ) ) {
			return $download_result;
		}

		$extension_name = $opts['extension_name'];

		$unzip_result = unzip_file(
			FW_WP_Filesystem::filesystem_path_to_real_path( $zip_path ),
			$wp_fs_tmp_dir
		);

		if ( is_wp_error( $unzip_result ) ) {
			return $unzip_result;
		}

		// remove zip file
		if ( ! $wp_filesystem->delete( $zip_path, false, 'f' ) ) {
			return new WP_Error(
				$wp_error_id,
				sprintf( __( 'Cannot remove the "%s" extension downloaded zip.', 'fw' ), $this->get_extension_title( $extension_name ) )
			);
		}

		$unzipped_dir_files = $wp_filesystem->dirlist( $wp_fs_tmp_dir );

		if ( ! $unzipped_dir_files ) {
			return new WP_Error(
				$wp_error_id,
				__( 'Cannot access the unzipped directory files.', 'fw' )
			);
		}

		/**
		 * get first found directory
		 * (if everything worked well, there should be only one directory)
		 */
		foreach ( $unzipped_dir_files as $file ) {
			if ( $file['type'] == 'd' ) {
				return $wp_fs_tmp_dir . '/' . $file['name'];
			}
		}

		return new WP_Error(
			$wp_error_id,
			sprintf( __( 'The unzipped "%s" extension directory not found.', 'fw' ), $this->get_extension_title( $extension_name ) )
		);
	}

	/**
	 * @param $set
	 *
	 * @return FW_Ext_Download_Source|WP_Error
	 */
	private function get_download_source( $set ) {
		require_once dirname( __FILE__ ) . '/includes/download-source/types/init.php';

		$register = new _FW_Ext_Download_Source_Register( self::get_access_key()->get_key() );

		/**
		 * Register download sources for extensions.
		 *
		 * Usage:
		 *   $download_source = new FW_Ext_Download_Source();
		 *   $register->register($download_source);
		 */
		do_action( 'fw_register_ext_download_sources', $register );

		$download_source = $register->_get_type( self::get_access_key(), $set['download']['source'] );

		if ( ! $download_source ) {
			$download_source = new WP_Error( 'invalid_dl_source', sprintf( esc_html__( 'Invalid download source: %s', 'fw' ), $set['download']['source'] ) );
		}

		return $download_source;
	}

	/**
	 * Merge the downloaded extension directory with the existing directory
	 *
	 * @param string $source_wp_fs_dir Downloaded extension directory
	 * @param string $destination_wp_fs_dir
	 *
	 * @return null|WP_Error
	 */
	private function merge_extension( $source_wp_fs_dir, $destination_wp_fs_dir ) {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$wp_error_id = 'fw_extensions_merge';

		// check source
		{
			$source_files = $wp_filesystem->dirlist( $source_wp_fs_dir );

			if ( $source_files === false ) {
				return new WP_Error(
					$wp_error_id,
					sprintf( __( 'Cannot read directory "%s".', 'fw' ), $source_wp_fs_dir )
				);
			}

			if ( empty( $source_files ) ) {
				return; // directory is empty, nothing to move
			}
		}

		/**
		 * Prepare destination directory
		 * Remove everything except the extensions/ directory
		 */
		if ( $wp_filesystem->exists( $destination_wp_fs_dir ) ) {
			$destination_files = $wp_filesystem->dirlist( $destination_wp_fs_dir );

			if ( $destination_files === false ) {
				return new WP_Error(
					$wp_error_id,
					sprintf( __( 'Cannot read directory "%s".', 'fw' ), $destination_wp_fs_dir )
				);
			}

			if ( ! empty( $destination_files ) ) {
				if (
					count( $source_files ) == 1
					&&
					( $file = reset( $source_files ) )
					&&
					$file['name'] === 'extensions'
					&&
					$file['type'] === 'd'
				) {
					/**
					 * Source extension is empty
					 * It happens when you merge a directory which contains child extensions
					 * Do not delete current destination files, just go in the next child extensions level
					 * Used by https://github.com/ThemeFuse/Unyson/issues/1874
					 */
				} else {
					// the directory contains some files, delete everything
					foreach ( $destination_files as $file ) {
						if ( $file['name'] === 'extensions' && $file['type'] === 'd' ) {
							// do not touch the extensions/ directory
							continue;
						}

						if ( ! $wp_filesystem->delete(
							$destination_wp_fs_dir . '/' . $file['name'],
							true,
							$file['type']
						) ) {
							return new WP_Error(
								$wp_error_id,
								sprintf(
									__( 'Cannot delete "%s".', 'fw' ),
									$destination_wp_fs_dir . '/' . $file['name']
								)
							);
						}
					}
				}

				unset( $destination_files );
			}
		} else {
			if ( ! FW_WP_Filesystem::mkdir_recursive( $destination_wp_fs_dir ) ) {
				return new WP_Error(
					$wp_error_id,
					sprintf( __( 'Cannot create the "%s" directory.', 'fw' ), $destination_wp_fs_dir )
				);
			}
		}

		// Move files from source to destination
		{
			$has_sub_extensions = false;

			foreach ( $source_files as $file ) {
				if ( $file['name'] === 'extensions' && $file['type'] === 'd' ) {
					$has_sub_extensions = true; // do not touch the extensions/ directory
					continue;
				}

				if ( ! $wp_filesystem->move( $source_wp_fs_dir . '/' . $file['name'], $destination_wp_fs_dir . '/' . $file['name'] ) ) {
					return new WP_Error(
						$wp_error_id,
						sprintf(
							__( 'Cannot move "%s" to "%s".', 'fw' ),
							$source_wp_fs_dir . '/' . $file['name'],
							$destination_wp_fs_dir . '/' . $file['name']
						)
					);
				}
			}

			unset( $source_files );
		}

		if ( ! $has_sub_extensions ) {
			return;
		}

		$sub_extensions = $wp_filesystem->dirlist( $source_wp_fs_dir . '/extensions' );

		if ( $sub_extensions === false ) {
			return new WP_Error(
				$wp_error_id,
				sprintf( __( 'Cannot read directory "%s".', 'fw' ), $source_wp_fs_dir . '/extensions' )
			);
		}

		if ( empty( $sub_extensions ) ) {
			// directory is empty, nothing to remove
			return;
		}

		foreach ( $sub_extensions as $file ) {
			if ( $file['type'] !== 'd' ) {
				// wrong, only directories must exist in the extensions/ directory
				continue;
			}

			$merge_result = $this->merge_extension(
				$source_wp_fs_dir . '/extensions/' . $file['name'],
				$destination_wp_fs_dir . '/extensions/' . $file['name']
			);

			if ( is_wp_error( $merge_result ) ) {
				return $merge_result;
			}
		}
	}

	/**
	 * @since 2.6.9
	 */
	public function get_supported_extensions()
	{
		$supported_extensions = fw()->theme->manifest->get('supported_extensions', array());

		// Add Available Extensions registered by the theme
		foreach ($this->get_available_extensions() as $name => $extension) {
			if (isset($extension['theme']) && $extension['theme']) {
				$supported_extensions[$name] = array();
			}
		}

		if (empty($supported_extensions)) {
			return array();
		}

		// remove not available extensions
		$supported_extensions = array_intersect_key($supported_extensions, $this->get_available_extensions());

		if (empty($supported_extensions)) {
			return array();
		}

		if (empty($supported_extensions)) {
			return array();
		}

		return $supported_extensions;
	}

	/**
	 * @since 2.6.9
	 */
	public function get_supported_extensions_for_install()
	{
		// remove already installed extensions
		return array_diff_key(
			$this->get_supported_extensions(),
			$this->get_installed_extensions()
		);
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

	public function is_extensions_page()
	{
		$current_screen = get_current_screen();

		if (empty($current_screen)) {
			return false;
		}

		return (
			property_exists($current_screen, 'base') && strpos($current_screen->base, $this->get_page_slug()) !== false
			&&
			property_exists($current_screen, 'id') && strpos($current_screen->id, $this->get_page_slug()) !== false
			&&
		    !isset($_GET['sub-page'])
		);
	}

	public function is_extension_page()
	{
		$current_screen = get_current_screen();

		if (empty($current_screen)) {
			return false;
		}

		return (
			property_exists($current_screen, 'base') && strpos($current_screen->base, $this->get_page_slug()) !== false
			&&
			property_exists($current_screen, 'id') && strpos($current_screen->id, $this->get_page_slug()) !== false
			&&
			isset($_GET['sub-page']) && $_GET['sub-page'] === 'extension'
		);
	}

	/**
	 * @internal
	 */
	public function _action_enqueue_scripts()
	{
		wp_enqueue_style(
			'fw-extensions-menu-icon',
			$this->get_uri('/static/unyson-font-icon/style.css'),
			array(),
			fw()->manifest->get_version()
		);

		/**
		 * Enqueue only on Extensions List page
		 */
		if ($this->is_extensions_page()) {
			wp_enqueue_style(
				'fw-extensions-page',
				$this->get_uri('/static/extensions-page.css'),
				array(
					'fw',
					'fw-unycon', 'font-awesome', // in case some extension has font-icon thumbnail
				),
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

			/**
			 * this is needed for fw.soleModal design
			 * it is displayed when extension ajax install returns an error
			 */
			wp_enqueue_media();
		}

		if ($this->is_extension_page()) {
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

			/**
			 * Enqueue extension settings options static
			 */
			if (
				isset($_GET['extension'])
				&&
				is_string($extension_name = $_GET['extension'])
				&&
				fw()->extensions->get($extension_name)
				&&
				($extension_settings_options = fw()->extensions->get($extension_name)->get_settings_options())
			) {
				fw()->backend->enqueue_options_static($extension_settings_options);
			}
		}
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
		if ( ! apply_filters( 'fw_after_switch_theme_activate_exts', true ) ) {
			return;
		}

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

	/**
	 * @internal
	 */
	public function _action_admin_notices() {
		$should_notify = apply_filters(
			'fw_notify_about_missing_extensions',
			true
		);

		/**
		 * In v2.4.12 was done a terrible mistake https://github.com/ThemeFuse/Unyson-Extensions-Approval/issues/160
		 * Show a warning with link to install theme supported extensions
		 */
		if (
			$should_notify
			&&
			!isset($_GET['supported']) // already on 'Install Supported Extensions' page
			&&
			$this->can_install()
			&&
			(($installed_extensions = $this->get_installed_extensions()) || true)
			&&
			!isset($installed_extensions['page-builder'])
			&&
			$this->get_supported_extensions_for_install()
		) {
			echo '<div class="error"> <p>'
			, fw_html_tag('a', array('href' => $this->get_link() .'&sub-page=install&supported'),
				__('Install theme compatible extensions', 'fw'))
			, '</p></div>';
		}
	}

	/**
	 * Copy Theme Available Extensions to a tmp directory
	 * Used before theme update
	 * @since 2.6.0
	 * @return null|WP_Error
	 */
	public function theme_available_extensions_copy() {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!FW_WP_Filesystem::is_ready()) {
			return new WP_Error(
				'fs_not_initialized',
				__('WP Filesystem is not initialized', 'fw')
			);
		}

		// Prepare temporary directory
		{
			$wpfs_tmp_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
				$this->get_tmp_dir('/theme-ext')
			);

			if (
				$wp_filesystem->exists( $wpfs_tmp_dir )
				&&
				! $wp_filesystem->rmdir( $wpfs_tmp_dir, true )
			) {
				return new WP_Error(
					'tmp_dir_rm_fail',
					sprintf(__('Temporary directory cannot be removed: %s', 'fw'), $wpfs_tmp_dir)
				);
			}

			if ( ! FW_WP_Filesystem::mkdir_recursive( $wpfs_tmp_dir ) ) {
				return new WP_Error(
					'tmp_dir_rm_fail',
					sprintf(__('Temporary directory cannot be created: %s', 'fw'), $wpfs_tmp_dir)
				);
			}
		}

		$available_extensions = $this->get_available_extensions();
		$installed_extensions = $this->get_installed_extensions(true);
		$base_dir = fw_get_template_customizations_directory('/extensions');

		foreach ($installed_extensions as $name => $ext) {
			if ( ! (
				isset($available_extensions[$name])
				&&
				isset($available_extensions[$name]['theme'])
				&&
				$available_extensions[$name]['theme']
			) ) {
				continue;
			}

			if ( ($rel_path = preg_replace('/^'. preg_quote($base_dir, '/') .'/', '', $ext['path'])) === $base_dir ) {
				return new WP_Error(
					'rel_path_failed',
					sprintf(__('Failed to extract relative directory from: %s', 'fw'), $ext['path'])
				);
			}

			if ( ($wpfs_path = FW_WP_Filesystem::real_path_to_filesystem_path($ext['path'])) === false) {
				return new WP_Error(
					'real_to_wpfs_filed',
					sprintf(__('Failed to extract relative directory from: %s', 'fw'), $ext['path'])
				);
			}

			$wpfs_dest_dir = $wpfs_tmp_dir . $rel_path;

			if ( ! FW_WP_Filesystem::mkdir_recursive($wpfs_dest_dir) ) {
				return new WP_Error(
					'dest_dir_mk_fail',
					sprintf(__('Failed to create directory %s', 'fw'), $wpfs_dest_dir)
				);
			}

			if ( is_wp_error( $copy_result = copy_dir($wpfs_path, $wpfs_dest_dir) ) ) {
				/** @var WP_Error $copy_result */
				return new WP_Error(
					'ext_copy_failed',
					sprintf( __('Failed to copy extension to %s', 'fw'), $wpfs_dest_dir )
				);
			}
		}
	}

	/**
	 * Copy Theme Available Extensions from tmp directory to theme
	 * Used after theme update
	 * @since 2.6.0
	 * @return null|WP_Error
	 */
	public function theme_available_extensions_restore() {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!FW_WP_Filesystem::is_ready()) {
			return new WP_Error(
				'fs_not_initialized',
				__('WP Filesystem is not initialized', 'fw')
			);
		}

		if ( ! $wp_filesystem->exists(
			$wpfs_tmp_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
				$this->get_tmp_dir('/theme-ext')
			)
		) ) {
			return new WP_Error(
				'no_tmp_dir',
				sprintf(__('Temporary directory does not exist: %s', 'fw'), $wpfs_tmp_dir)
			);
		}

		/**
		 * Fixes the case when the theme path before update was
		 * wp-content/themes/theme-name/theme-name-parent
		 * but after update it became
		 * wp-content/themes/theme-name-parent
		 *
		 * and at this point get_template_directory() returns old theme directory
		 * so fw_get_template_customizations_directory() also returns old path
		 */
		$theme_dir = wp_get_theme()->get_theme_root() .'/'. wp_get_theme()->get_template();

		if ( ! ($wpfs_base_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
			$base_dir = $theme_dir . fw_get_framework_customizations_dir_rel_path('/extensions')
		) ) ) {
			return new WP_Error(
				'base_dir_to_wpfs_fail',
				sprintf( __('Cannot obtain WP Filesystem dir for %s', 'fw'), $base_dir )
			);
		}

		if ( ! ( $dirlist = $wp_filesystem->dirlist($wpfs_tmp_dir) ) ) {
			return;
		}

		foreach ( $dirlist as $filename => $fileinfo ) {
			if ( 'd' !== $fileinfo['type'] ) {
				continue;
			}

			if ( is_wp_error($merge_result = $this->merge_extension(
				$wpfs_tmp_dir  .'/'. $filename,
				$wpfs_base_dir .'/'. $filename
			)) ) {
				return $merge_result;
			}
		}

		$wp_filesystem->rmdir( $wpfs_tmp_dir, true );
	}

	/**
	 * Copy Theme Available Extensions to tmp dir
	 * @param bool|WP_Error $result
	 * @param array $data
	 *
	 * @return bool|WP_Error
	 */
	public function _filter_theme_available_extensions_copy($result, $data) {
		if (
			!is_wp_error($result)
			&&
			is_array($data)
			&&
			isset($data['theme'])
			&&
			$data['theme'] === wp_get_theme()->get_template()
		) {
			if ( is_wp_error( $copy_result = fw()->extensions->manager->theme_available_extensions_copy() ) ) {
				return $copy_result;
			}
		}

		return $result;
	}

	/**
	 * Restore Theme Available Extensions from tmp dir
	 * @param Theme_Upgrader $instance
	 * @param array $data
	 *
	 * @return bool|WP_Error
	 */
	public function _action_theme_available_extensions_restore($instance, $data) {
		if (
			!is_wp_error($instance->skin->result)
			&&
			is_array($data)
			&&
			isset($data['action']) && $data['action'] === 'update'
			&&
			isset($data['type']) && $data['type'] === 'theme'
			&&
			isset($data['themes'])
			&&
			($template = wp_get_theme()->get_template())
			&&
			(
				in_array($template, $data['themes'])
				||
				/**
				 * Fixes the case when the theme path before update was
				 * wp-content/themes/theme-name/theme-name-parent
				 * but after update it became
				 * wp-content/themes/theme-name-parent
				 */
				( preg_match($regex = '/\-parent$/', $template)
					? in_array( preg_replace($regex, '', $template) .'/'. $template, $data['themes'] )
					: false )
			)
		) {
			fw()->extensions->manager->theme_available_extensions_restore();
		}
	}

	/**
	 * Install compatible extensions on plugin install -> activate
	 *
	 * In order for this to work, int TGM config must be set: 'is_automatic' => true
	 * http://tgmpluginactivation.com/configuration/
	 *
	 * @internal
	 */
	public function _action_plugin_activate_install_compatible_extensions() {
		if (!FW_WP_Filesystem::is_ready()) {
			return;
		}

		if ($compatible_extensions = $this->get_supported_extensions_for_install()) {
			$this->install_extensions($compatible_extensions);
			// the result is not used because we don't know here if we can print the errors or not
		}
	}

	/**
	 * @since 2.6.9
	 */
	public function collect_extension_requirements($extension_name, $can_install = null) {
		$installed_extensions = $this->get_installed_extensions();

		if (is_null($can_install)) {
			$can_install = $this->can_install();
		}

		if (! isset($installed_extensions[$extension_name])) {
			return array();
		} else {
			$data = $installed_extensions[$extension_name];
		}

		$result = array();

		$manifest_requirements = fw_akg('requirements', $data['manifest'], array());

		foreach ($manifest_requirements as $req_name => $req_data) {
			switch ($req_name) {
				case 'php':
					if (empty($req_data['min_version']) && empty($req_data['max_version'])) {
						break;
					}

					if ( ! empty( $req_data['min_version'] ) ) {
						if (!version_compare($req_data['min_version'], phpversion(), '<=')) {
							$result[] = sprintf(
								__( 'PHP needs to be updated to %s', 'fw' ),
								$req_data['min_version']
							);
						}
					}

					if ( ! empty( $req_data['max_version'] ) ) {
						if (!version_compare($req_data['max_version'], phpversion(), '>=')) {
							$result[] = sprintf(
								__('Maximum supported PHP version is %s', 'fw'),
								$req_data['max_version']
							);
						}
					}

					break;

				case 'wordpress':
					if (empty($req_data['min_version']) && empty($req_data['max_version'])) {
						break;
					}

					global $wp_version;

					if ( ! empty( $req_data['min_version'] ) ) {
						if (!version_compare($req_data['min_version'], $wp_version, '<=')) {
							if ($can_install) {
								$result[] = sprintf(
									__( 'You need to update WordPress to %s: %s', 'fw' ),
									$req_data['min_version'],
									fw_html_tag( 'a', array( 'href' => self_admin_url( 'update-core.php' ) ), __( 'Update WordPress', 'fw' ) )
								);
							} else {
								$result[] = sprintf(
									__( 'WordPress needs to be updated to %s', 'fw' ),
									$req_data['min_version']
								);
							}
						}
					}

					if ( ! empty( $req_data['max_version'] ) ) {
						if (!version_compare($req_data['max_version'], $wp_version, '>=')) {
							$result[] = sprintf(
								__('Maximum supported WordPress version is %s', 'fw'),
								$req_data['max_version']
							);
						}
					}

					break;

				case 'framework':
					if (empty($req_data['min_version']) && empty($req_data['max_version'])) {
						break;
					}

					if ( ! empty( $req_data['min_version'] ) ) {
						if (!version_compare($req_data['min_version'], fw()->manifest->get_version(), '<=')) {
							if ($can_install) {
								$result[] = sprintf(
									__( 'You need to update %s to %s: %s', 'fw' ),
									fw()->manifest->get_name(),
									$req_data['min_version'],
									fw_html_tag( 'a', array( 'href' => self_admin_url( 'update-core.php' ) ),
										sprintf( __( 'Update %s', 'fw' ), fw()->manifest->get_name() )
									)
								);
							} else {
								$result[] = sprintf(
									__( '%s needs to be updated to %s', 'fw' ),
									fw()->manifest->get_name(),
									$req_data['min_version']
								);
							}
						}
					}

					if ( ! empty( $req_data['max_version'] ) ) {
						if (!version_compare($req_data['max_version'], fw()->manifest->get_version(), '>=')) {
							$result[] = sprintf(
								__( 'Maximum supported %s version is %s', 'fw' ),
								fw()->manifest->get_name(),
								$req_data['max_version']
							);
						}
					}

					break;

				case 'extensions':
					foreach ($req_data as $req_ext => $req_ext_data) {
						if ($ext = fw()->extensions->get($req_ext)) {
							if (empty($req_ext_data['min_version']) && empty($req_ext_data['max_version'])) {
								continue;
							}

							if ( ! empty( $req_ext_data['min_version'] ) ) {
								if (!version_compare($req_ext_data['min_version'], $ext->manifest->get_version(), '<=')) {
									if ($can_install) {
										$result[] = sprintf(
											__('You need to update the %s extension to %s: %s', 'fw'),
											$ext->manifest->get_name(),
											$req_ext_data['min_version'],
											fw_html_tag('a', array('href' => self_admin_url('update-core.php')),
												sprintf(__('Update %s', 'fw'), $ext->manifest->get_name())
											)
										);
									} else {
										$result[] = sprintf(
											__('The %s extension needs to be updated to %s', 'fw'),
											$ext->manifest->get_name(),
											$req_ext_data['min_version']
										);
									}
								}
							}

							if ( ! empty( $req_ext_data['max_version'] ) ) {
								if (!version_compare($req_ext_data['max_version'], $ext->manifest->get_version(), '>=')) {
									$result[] = sprintf(
										__( 'Maximum supported %s extension version is %s', 'fw' ),
										$ext->manifest->get_name(),
										$req_ext_data['max_version']
									);
								}
							}
						} else {
							$ext_title = fw_id_to_title($req_ext);

							if (isset($lists['installed'][$req_ext])) {
								$ext_title = fw_akg('name', $lists['installed'][$req_ext]['manifest'], $ext_title);

								ob_start(); ?>
								<form action="<?php echo esc_attr($link) ?>&sub-page=activate&extension=<?php echo esc_attr($req_ext) ?>" method="post" style="display: inline;">
									<?php wp_nonce_field($nonces['activate']['action'], $nonces['activate']['name']); ?>
									<?php echo sprintf(__( 'The %s extension is disabled', 'fw' ), $ext_title); ?>:
									<a href="#" onclick="jQuery(this).closest('form').submit(); return false;"><?php echo sprintf(__('Activate %s', 'fw'), $ext_title); ?></a>
								</form>
								<?php
								$result[] = ob_get_clean();
							} else {
								if ($can_install && isset($lists['available'][$req_ext])) {
									$ext_title = $lists['available'][ $req_ext ]['name'];

									$result[] = sprintf(
										__( 'The %s extension is not installed: %s', 'fw' ),
										$ext_title,
										fw_html_tag( 'a', array( 'href' => $link . '&sub-page=install&extension=' . $req_ext ),
											sprintf( __( 'Install %s', 'fw' ), $ext_title )
										)
									);
								} else {
									$result[] = sprintf(
										__( 'The %s extension is not installed', 'fw' ),
										$ext_title
									);
								}
							}
						}
					}

					break;

				default:
					trigger_error('Invalid requirement: '. $req_name, E_USER_WARNING);
			}
		}

		return $result;
	}
}
