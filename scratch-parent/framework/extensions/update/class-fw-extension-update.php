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
		if (!current_user_can('update_themes')) {
			return false; // prevent child extensions activation
		}

		$this->context = get_template_directory();

		$this->add_actions();
		$this->add_filters();
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

				$fixed_latest_version = preg_replace('/^[^0-9]+/i', '', $latest_version);

				if (!version_compare($latest_version, $extension->manifest->get_version(), '>')) {
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

			$fixed_latest_version = preg_replace('/^[^0-9]+/i', '', $latest_version);

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

			$fixed_latest_version = preg_replace('/^[^0-9]+/i', '', $latest_version);

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

		$update = $this->get_framework_update(true);

		do {
			if ($update === false) {
				$skin->error(__('Failed to get framework latest version.', 'fw'));
				break;
			} elseif (is_wp_error($update)) {
				$skin->error($update);
				break;
			}

			$context = $this->context;

			if (!FW_WP_Filesystem::request_access($context, fw_current_url(), array($nonce_name))) {
				break;
			}

			/** @var WP_Filesystem_Base $wp_filesystem */
			global $wp_filesystem;

			// create temporary directory for files to be downloaded in it
			{
				$tmp_download_dir = FW_WP_Filesystem::real_path_to_filesystem_path(
					fw_fix_path(WP_CONTENT_DIR) .'/cache/framework/update'
				);

				// just in case it already exists, clear everything, it may contain broken/old files
				if ($wp_filesystem->exists($tmp_download_dir)) {
					if (!$wp_filesystem->rmdir( $tmp_download_dir, true )) {
						$skin->error(__('Cannot remove old temporary directory: ', 'fw') . $tmp_download_dir);
						break;
					}
				}

				if (!FW_WP_Filesystem::mkdir_recursive($tmp_download_dir)) {
					$skin->error(__('Cannot create directory: ', 'fw') . $tmp_download_dir);
					break;
				}
			}

			$skin->feedback(__('Downloading the framework...', 'fw'));
			{
				/** @var FW_Ext_Update_Service $service */
				$service = $this->get_child($update['service']);

				$downloaded_dir = $service->_download_framework($update['latest_version'], $tmp_download_dir);

				if (!$downloaded_dir) {
					$skin->error(__('Cannot download the framework.', 'fw'));
					break;
				} elseif (is_wp_error($downloaded_dir)) {
					$skin->error($downloaded_dir);
					break;
				}
			}

			$this->maintenance_mode(true);

			$skin->feedback(__('Installing the framework...', 'fw'));
			{
				$framework_dir = FW_WP_Filesystem::real_path_to_filesystem_path(fw_get_framework_directory());

				$include_hidden_files = false;

				// removing all files from the framework directory
				{
					$dir_files = $wp_filesystem->dirlist($framework_dir, $include_hidden_files);
					if ($dir_files === false) {
						$skin->error(__('Cannot access directory: ', 'fw') . $framework_dir);
						break;
					}

					foreach ($dir_files as $file) {
						$file_path = $framework_dir .'/'. $file['name'];

						if (!$wp_filesystem->delete($file_path, true, $file['type'])) {
							$skin->error(__('Cannot remove: ', 'fw') . $file_path);
						}
					}
				}

				// move all files from the downloaded directory to the framework directory
				{
					$dir_files = $wp_filesystem->dirlist($downloaded_dir, $include_hidden_files);
					if ($dir_files === false) {
						$skin->error(__('Cannot access directory: ', 'fw') . $downloaded_dir);
						break;
					}

					foreach ($dir_files as $file) {
						$downloaded_file_path = $downloaded_dir .'/'. $file['name'];
						$framework_file_path  = $framework_dir .'/'. $file['name'];

						if (!$wp_filesystem->move($downloaded_file_path, $framework_file_path)) {
							$skin->error(
								sprintf(__('Cannot move "%s" to "%s"', 'fw'), $framework_dir, $framework_file_path)
							);
						}
					}
				}
			}

			$skin->feedback(__('The framework has been successfully updated.', 'fw'));

			$wp_filesystem->delete($tmp_download_dir, true, 'd');

			$skin->set_result(true);
			$skin->after();
		} while(false);

		$this->maintenance_mode(false);

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

		require_once(ABSPATH . 'wp-admin/admin-header.php');

		fw_print('Hello');

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

		require_once(ABSPATH . 'wp-admin/admin-header.php');

		fw_print('Hello');

		require_once(ABSPATH . 'wp-admin/admin-footer.php');
	}
}
