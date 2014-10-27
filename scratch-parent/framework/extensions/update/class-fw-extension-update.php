<?php if (!defined('FW')) die('Forbidden');

require dirname(__FILE__) .'/includes/extends/class-fw-ext-update-service.php';

class FW_Extension_Update extends FW_Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function _child_extension_is_valid($child_extension_instance)
	{
		return is_subclass_of($child_extension_instance, 'FW_Ext_Update_Service');
	}

	/**
	 * For which directory to request write permissions from Filesystem API
	 * @var string
	 */
	private $context;

	/**
	 * @internal
	 */
	protected function _init()
	{
		if (current_user_can('update_themes') || current_user_can('update_plugins')) {
			$this->context = get_template_directory();

			$this->add_actions();
			$this->add_filters();
		} else {
			return false; // prevent child extensions activation
		}
	}

	private function add_actions()
	{
		add_action('core_upgrade_preamble', array($this, '_action_updates_page_footer'));

		add_action('update-core-custom_'. 'fw-update-framework',  array($this, '_action_update_framework'));
		add_action('update-core-custom_'. 'fw-update-theme',      array($this, '_action_update_theme'));
		add_action('update-core-custom_'. 'fw-update-extensions', array($this, '_action_update_extensions'));
	}

	private function add_filters()
	{
		add_filter('wp_get_update_data', array($this, '_filter_update_data'), 10, 2);
	}

	private function get_fixed_version($version)
	{
		// remove from the beginning everything that is not a number: 'v1.2.3' -> '1.2.3', 'ver1.0.0' -> '1.0.0'
		return preg_replace('/^[^1-9]+/i', '', $version);;
	}

	private function get_wp_fs_tmp_dir()
	{
		return FW_WP_Filesystem::real_path_to_filesystem_path(
			fw_fix_path(WP_CONTENT_DIR) .'/tmp/fw-ext-update'
		);
	}

	/**
	 * @internal
	 */
	public function _action_updates_page_footer()
	{
		echo $this->render_view('updates-list', array(
			'updates' => $this->get_updates(!empty($_GET['force-check']))
		));
	}

	/**
	 * @internal
	 */
	public function _filter_update_data($data, $titles)
	{
		$updates = $this->get_updates(!empty($_GET['force-check']));

		if ($updates['framework'] && !is_wp_error($updates['framework'])) {
			$data['counts']['total']++;
		}

		if ($updates['theme'] && !is_wp_error($updates['theme'])) {
			$data['counts']['total']++;
		}

		if (!empty($updates['extensions'])) {
			foreach ($updates['extensions'] as $ext_name => $ext_update) {
				if (is_wp_error($ext_update)) {
					continue;
				}

				$data['counts']['total']++;
			}
		}

		return $data;
	}

	private function get_updates($force_check = false)
	{
		$cache_key = 'fw_ext_update/updates';

		// use cache because this method may be called multiple times (to prevent useless requests to update servers)

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$updates = array(
				'framework'  => $this->get_framework_update($force_check),
				'theme'      => $this->get_theme_update($force_check),
				'extensions' => $this->get_extensions_with_updates($force_check)
			);

			FW_Cache::set($cache_key, $updates);

			return $updates;
		}
	}

	/**
	 * Collect extensions that has new versions available
	 * @param bool $force_check
	 * @return array {ext_name => update_data}
	 */
	private function get_extensions_with_updates($force_check = false)
	{
		$updates = array();

		$services = $this->get_children();

		foreach (fw()->extensions->get_all() as $ext_name => $extension) {
			/** @var FW_Extension $extension */

			if ($extension->get_declared_source() !== 'child') {
				/**
				 * Only extensions from child theme are standalone and can have updates.
				 * Extensions from framework comes with framework.
				 * Extensions from parent theme comes with parent theme.
				 */
				continue;
			}

			/**
			 * Ask each service if it knows how to update the extension
			 */
			foreach ($services as $service_name => $service) {
				/** @var $service FW_Ext_Update_Service */

				$latest_version = $service->_get_extension_latest_version($extension, $force_check);

				if ($latest_version === false) {
					// It said that it doesn't know how to update it
					continue;
				}

				if (is_wp_error($latest_version)) {
					$updates[$ext_name] = $latest_version;
					break;
				}

				$fixed_latest_version = $this->get_fixed_version($latest_version);

				if (!version_compare($fixed_latest_version, $extension->manifest->get_version(), '>')) {
					// we already have latest version
					continue;
				}

				$updates[$ext_name] = array(
					'service' => $service_name,
					'latest_version' => $latest_version,
					'fixed_latest_version' => $fixed_latest_version
				);

				break;
			}
		}

		return $updates;
	}

	/**
	 * @param bool $force_check
	 * @return array|false|WP_Error
	 */
	private function get_framework_update($force_check = false)
	{
		/**
		 * Ask each service if it knows how to update the framework
		 */
		foreach ($this->get_children() as $service_name => $service) {
			/** @var $service FW_Ext_Update_Service */

			$latest_version = $service->_get_framework_latest_version($force_check);

			if ($latest_version === false) {
				// It said that it doesn't know how to update it
				continue;
			}

			if (is_wp_error($latest_version)) {
				return $latest_version;
			}

			$fixed_latest_version = $this->get_fixed_version($latest_version);

			if (!version_compare($fixed_latest_version, fw()->manifest->get_version(), '>')) {
				// we already have latest version
				continue;
			}

			return array(
				'service' => $service_name,
				'latest_version' => $latest_version,
				'fixed_latest_version' => $fixed_latest_version
			);
		}

		return false;
	}

	/**
	 * @param bool $force_check
	 * @return array|false|WP_Error
	 */
	private function get_theme_update($force_check = false)
	{
		/**
		 * Ask each service if it knows how to update the theme
		 */
		foreach ($this->get_children() as $service_name => $service) {
			/** @var $service FW_Ext_Update_Service */

			$latest_version = $service->_get_theme_latest_version($force_check);

			if ($latest_version === false) {
				// It said that it doesn't know how to update it
				continue;
			}

			if (is_wp_error($latest_version)) {
				return $latest_version;
			}

			$fixed_latest_version = $this->get_fixed_version($latest_version);

			if (!version_compare($fixed_latest_version, fw()->theme->manifest->get_version(), '>')) {
				// we already have latest version
				continue;
			}

			return array(
				'service' => $service_name,
				'latest_version' => $latest_version,
				'fixed_latest_version' => $fixed_latest_version
			);
		}

		return false;
	}

	/**
	 * Turn on/off the maintenance mode
	 * @param bool $enable
	 */
	private function maintenance_mode($enable = false)
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			return;
		}

		$file_path = $wp_filesystem->abspath() . '.maintenance';

		if ($wp_filesystem->exists($file_path)) {
			if (!$wp_filesystem->delete($file_path)) {
				trigger_error(__('Cannot delete: ', 'fw') . $file_path, E_USER_WARNING);
			}
		}

		if ($enable) {
			// Create maintenance file to signal that we are upgrading
			if (!$wp_filesystem->put_contents($file_path, '<?php $upgrading = ' . time() . '; ?>', FS_CHMOD_FILE)) {
				trigger_error(__('Cannot create: ', 'fw') . $file_path, E_USER_WARNING);
			}
		}
	}

	/**
	 * Download and install new version files
	 *
	 * global $wp_filesystem; must be initialized before calling this method
	 *
	 * @param array $data
	 * @return null|WP_Error
	 */
	private function update($data)
	{
		$required_data_keys = array(
			'wp_fs_destination_dir'  => true,
			'download_callback'      => true,
			'download_callback_args' => true,
			'skin'                   => true,
			'title'                  => true,
		);

		if (count($required_data_keys) > count(array_intersect_key($required_data_keys, $data))) {
			trigger_error('Some required keys are not present', E_USER_ERROR);
		}

		// move manually every key to variable, so IDE will understand better them
		{
			/**
			 * Replace all files in this directory with downloaded
			 * @var string $wp_fs_destination_dir
			 */
			$wp_fs_destination_dir = $data['wp_fs_destination_dir'];

			/**
			 * Called to download new version files to $this->get_wp_fs_tmp_dir()
			 * @var callable $download_callback
			 */
			$download_callback = $data['download_callback'];

			/**
			 * @var array
			 */
			$download_callback_args = $data['download_callback_args'];

			/**
			 * @var WP_Upgrader_Skin $skin
			 */
			$skin = $data['skin'];

			/**
			 * Used in text messages
			 * @var string $title
			 */
			$title = $data['title'];

			unset($data);
		}

		/**
		 * @var string|WP_Error
		 */
		$error = false;

		$tmp_download_dir = $this->get_wp_fs_tmp_dir();

		do {
			/** @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			// create temporary directory
			{
				if ($wp_filesystem->exists($tmp_download_dir)) {
					// just in case it already exists, clear everything, it may contain old files
					if (!$wp_filesystem->rmdir($tmp_download_dir, true)) {
						$error = __('Cannot remove old temporary directory: ', 'fw') . $tmp_download_dir;
						break;
					}
				}

				if (!FW_WP_Filesystem::mkdir_recursive($tmp_download_dir)) {
					$error = __('Cannot create directory: ', 'fw') . $tmp_download_dir;
					break;
				}
			}

			$skin->feedback(sprintf(__('Downloading the %s...', 'fw'), $title));
			{
				$downloaded_dir = call_user_func_array($download_callback, $download_callback_args);

				if (!$downloaded_dir) {
					$error = sprintf(__('Cannot download the %s.', 'fw'), $title);
					break;
				} elseif (is_wp_error($downloaded_dir)) {
					$error = $downloaded_dir;
					break;
				}
			}

			$this->maintenance_mode(true);

			$skin->feedback(sprintf(__('Installing the %s...', 'fw'), $title));
			{
				$skip_file_names = array('.git');

				// remove all files from destination directory
				{
					$dir_files = $wp_filesystem->dirlist($wp_fs_destination_dir, true);
					if ($dir_files === false) {
						$error =__('Cannot access directory: ', 'fw') . $wp_fs_destination_dir;
						break;
					}

					foreach ($dir_files as $file) {
						if (in_array($file['name'], $skip_file_names)) {
							continue;
						}

						$file_path = $wp_fs_destination_dir .'/'. $file['name'];

						if (!$wp_filesystem->delete($file_path, true, $file['type'])) {
							$skin->error(__('Cannot remove: ', 'fw') . $file_path);
						}
					}
				}

				// move all files from the temporary directory to the destination directory
				{
					$dir_files = $wp_filesystem->dirlist($downloaded_dir, true);
					if ($dir_files === false) {
						$error = __('Cannot access directory: ', 'fw') . $downloaded_dir;
						break;
					}

					foreach ($dir_files as $file) {
						if (in_array($file['name'], $skip_file_names)) {
							continue;
						}

						$downloaded_file_path  = $downloaded_dir .'/'. $file['name'];
						$destination_file_path = $wp_fs_destination_dir .'/'. $file['name'];

						if (!$wp_filesystem->move($downloaded_file_path, $destination_file_path)) {
							$skin->error(sprintf(
								__('Cannot move "%s" to "%s"', 'fw'),
								$downloaded_file_path, $destination_file_path
							));
						}
					}
				}
			}

			$skin->feedback(sprintf(__('The %s has been successfully updated.', 'fw'), $title));
		} while(false);

		$this->maintenance_mode(false);

		if ($wp_filesystem->exists($tmp_download_dir)) {
			if ( ! $wp_filesystem->delete( $tmp_download_dir, true, 'd' ) ) {
				$error = sprintf( __( 'Cannot remove temporary directory "%s".', 'fw' ), $tmp_download_dir );
			}
		}

		if ($error) {
			if (!is_wp_error($error)) {
				$error = new WP_Error( 'fw_ext_update_failed', (string)$error );
			}

			return $error;
		}
	}

	/**
	 * @internal
	 */
	public function _action_update_framework()
	{
		$nonce_name = '_nonce_fw_ext_update_framework';
		if (!isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name])) {
			wp_die(__('Invalid nonce.', 'fw'));
		}

		{
			if (!class_exists('_FW_Ext_Update_Framework_Upgrader_Skin')) {
				fw_include_file_isolated(
					$this->get_declared_path('/includes/classes/class--fw-ext-update-framework-upgrader-skin.php')
				);
			}

			$skin = new _FW_Ext_Update_Framework_Upgrader_Skin(array(
				'title' => __('Framework Update', 'fw'),
			));
		}

		require_once ABSPATH .'wp-admin/admin-header.php';

		$skin->header();

		do {
			if (!FW_WP_Filesystem::request_access($this->context, fw_current_url(), array($nonce_name))) {
				break;
			}

			$update = $this->get_framework_update();

			if ($update === false) {
				$skin->error(__('Failed to get framework latest version.', 'fw'));
				break;
			} elseif (is_wp_error($update)) {
				$skin->error($update);
				break;
			}

			/** @var FW_Ext_Update_Service $service */
			$service = $this->get_child($update['service']);

			$update_result = $this->update(array(
				'wp_fs_destination_dir' => FW_WP_Filesystem::real_path_to_filesystem_path(
					fw_get_framework_directory()
				),
				'download_callback' => array($service, '_download_framework'),
				'download_callback_args' => array($update['latest_version'], $this->get_wp_fs_tmp_dir()),
				'skin' => $skin,
				'title' => __('Framework', 'fw'),
			));

			if (is_wp_error($update_result)) {
				$skin->error($update_result);
				break;
			}

			$skin->set_result(true);
			$skin->after();
		} while(false);

		$skin->footer();

		require_once(ABSPATH . 'wp-admin/admin-footer.php');
	}

	/**
	 * @internal
	 */
	public function _action_update_theme()
	{
		$nonce_name = '_nonce_fw_ext_update_theme';
		if (!isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name])) {
			wp_die(__('Invalid nonce.', 'fw'));
		}

		{
			if (!class_exists('_FW_Ext_Update_Theme_Upgrader_Skin')) {
				fw_include_file_isolated(
					$this->get_declared_path('/includes/classes/class--fw-ext-update-theme-upgrader-skin.php')
				);
			}

			$skin = new _FW_Ext_Update_Theme_Upgrader_Skin(array(
				'title' => __('Theme Update', 'fw'),
			));
		}

		require_once(ABSPATH . 'wp-admin/admin-header.php');

		$skin->header();

		do {
			if (!FW_WP_Filesystem::request_access($this->context, fw_current_url(), array($nonce_name))) {
				break;
			}

			$update = $this->get_theme_update();

			if ($update === false) {
				$skin->error(__('Failed to get theme latest version.', 'fw'));
				break;
			} elseif (is_wp_error($update)) {
				$skin->error($update);
				break;
			}

			/** @var FW_Ext_Update_Service $service */
			$service = $this->get_child($update['service']);

			$update_result = $this->update(array(
				'wp_fs_destination_dir' => FW_WP_Filesystem::real_path_to_filesystem_path(
					get_template_directory()
				),
				'download_callback' => array($service, '_download_theme'),
				'download_callback_args' => array($update['latest_version'], $this->get_wp_fs_tmp_dir()),
				'skin' => $skin,
				'title' => __('Theme', 'fw'),
			));

			if (is_wp_error($update_result)) {
				$skin->error($update_result);
				break;
			}

			$skin->set_result(true);
			$skin->after();
		} while(false);

		$skin->footer();

		require_once(ABSPATH . 'wp-admin/admin-footer.php');
	}

	/**
	 * @internal
	 */
	public function _action_update_extensions()
	{
		$nonce_name = '_nonce_fw_ext_update_extensions';
		if (!isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name])) {
			wp_die(__('Invalid nonce.', 'fw'));
		}

		$form_input_name = 'extensions';
		$extensions_list = FW_Request::POST($form_input_name);

		if (empty($extensions_list)) {
			FW_Flash_Messages::add(
				'fw_ext_update',
				__('Please check the extensions you want to update.', 'fw'),
				'warning'
			);
			wp_redirect(self_admin_url('update-core.php'));
			exit;
		}

		// handle changes by the hack below
		{
			if (is_string($extensions_list)) {
				$extensions_list = json_decode($extensions_list);
			} else {
				$extensions_list = array_keys($extensions_list);
			}
		}

		{
			if (!class_exists('_FW_Ext_Update_Extensions_Upgrader_Skin')) {
				fw_include_file_isolated(
					$this->get_declared_path('/includes/classes/class--fw-ext-update-extensions-upgrader-skin.php')
				);
			}

			$skin = new _FW_Ext_Update_Extensions_Upgrader_Skin(array(
				'title' => __('Extensions Update', 'fw'),
			));
		}

		require_once(ABSPATH . 'wp-admin/admin-header.php');

		$skin->header();

		do {
			/**
			 * Hack for the ftp credentials template that does not support array post values
			 * https://github.com/WordPress/WordPress/blob/3949a8b6cc50a021ed93798287b4ef9ea8a560d9/wp-admin/includes/file.php#L1144
			 */
			{
				$original_post_value = $_POST[$form_input_name];
				$_POST[$form_input_name] = wp_slash(json_encode($extensions_list));
			}

			if (!FW_WP_Filesystem::request_access($this->context, fw_current_url(), array($nonce_name, $form_input_name))) {
				{ // revert hack changes
					$_POST[$form_input_name] = $original_post_value;
					unset($original_post_value);
				}
				break;
			}

			{ // revert hack changes
				$_POST[$form_input_name] = $original_post_value;
				unset($original_post_value);
			}

			$updates = $this->get_extensions_with_updates();

			if (empty($updates)) {
				$skin->error(__('No extensions updates found.', 'fw'));
				break;
			}

			foreach ($extensions_list as $extension_name) {
				if (!($extension = fw()->extensions->get($extension_name))) {
					$skin->error(
						sprintf(__('Extension "%s" does not exist or is disabled.', 'fw'), $extension_name)
					);
					continue;
				}

				if (!isset($updates[$extension_name])) {
					$skin->error(
						sprintf(__('No update found for the "%s" extension.', 'fw'), $extension->manifest->get_name())
					);
					continue;
				}

				$update = $updates[$extension_name];

				if (is_wp_error($update)) {
					$skin->error($update);
					continue;
				}

				/** @var FW_Ext_Update_Service $service */
				$service = $this->get_child($update['service']);

				$update_result = $this->update(array(
					'wp_fs_destination_dir' => FW_WP_Filesystem::real_path_to_filesystem_path(
						$extension->get_declared_path()
					),
					'download_callback' => array($service, '_download_extension'),
					'download_callback_args' => array($extension, $update['latest_version'], $this->get_wp_fs_tmp_dir()),
					'skin' => $skin,
					'title' => sprintf(__('%s extension', 'fw'), $extension->manifest->get_name()),
				));

				if (is_wp_error($update_result)) {
					$skin->error($update_result);
					continue;
				}

				$skin->set_result(true);
				$skin->decrement_extension_update_count($extension_name);
			}

			$skin->after();
		} while(false);

		$skin->footer();

		require_once(ABSPATH . 'wp-admin/admin-footer.php');
	}
}
