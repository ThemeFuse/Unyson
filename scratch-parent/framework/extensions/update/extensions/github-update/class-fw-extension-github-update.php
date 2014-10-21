<?php if (!defined('FW')) die('Forbidden');

/**
 * Github Update
 *
 * Add {'github_update' => 'user/repo'} to your manifest and this extension will handle it
 */
class FW_Extension_Github_Update extends FW_Ext_Update_Service
{
	private $manifest_key = 'github_update';
	private $manifest_key_regex = '/^([^\s\/]+)\/([^\s\/]+)$/';

	private $transient_expiration = 3600;

	private $download_timeout = 300;

	/**
	 * @internal
	 */
	protected function _init()
	{
	}

	private function fetch_latest_version($user_slash_repo)
	{
		$http = new WP_Http();

		$response = $http->get('https://api.github.com/repos/'. $user_slash_repo .'/releases');

		unset($http);

		if (wp_remote_retrieve_response_code($response) !== 200) {
			return new WP_Error('fw_ext_update_github_fetch_failed', __('Failed to contact Github.', 'fw'));
		}

		$releases = json_decode($response['body'], true);

		unset($response);

		if (empty($releases)) {
			return new WP_Error(
				'fw_ext_update_github_fetch_no_releases',
				sprintf(__('No releases found in repository "%s".', 'fw'), $user_slash_repo)
			);
		}

		return $releases[0]['tag_name'];
	}

	/**
	 * Get repository latest release version
	 *
	 * @param string $user_slash_repo Github 'user/repo'
	 * @param bool $force_check Bypass cache
	 * @param string $title Used in messages
	 *
	 * @return string|WP_Error
	 */
	private function get_latest_version($user_slash_repo, $force_check, $title)
	{
		if (!preg_match($this->manifest_key_regex, $user_slash_repo)) {
			return new WP_Error('fw_ext_update_github_manifest_invalid',
				sprintf(
					__('%s manifest has invalid "github_update" parameter. Please use "user/repo" format.', 'fw'),
					$title
				)
			);
		}

		$theme_id = preg_replace('[^a-z0-9_]', '_', fw()->theme->manifest->get_id());
		$transient_id = 'fw_ext_upd_gh_'. $theme_id .'_fw'; // the length must be 45 characters or less

		if ($force_check) {
			delete_site_transient($transient_id);

			$cache = array();
		} else {
			$cache = get_site_transient($transient_id);

			if ($cache === false) {
				$cache = array();
			} elseif (isset($cache[$user_slash_repo])) {
				return $cache[$user_slash_repo];
			}
		}

		$latest_version = $this->fetch_latest_version($user_slash_repo);

		if (empty($latest_version)) {
			return new WP_Error(
				'fw_ext_update_github_failed_fetch_latest_version',
				sprintf(
					__('Failed to fetch %s latest version from github "%s".', 'fw'),
					$title, $user_slash_repo
				)
			);
		}

		if (is_wp_error($latest_version)) {
			return $latest_version;
		}

		$cache = array_merge($cache, array($user_slash_repo => $latest_version));

		set_site_transient(
			$transient_id,
			$cache,
			$this->transient_expiration
		);

		return $latest_version;
	}

	/**
	 * @param string $user_slash_repo Github 'user/repo'
	 * @param string $version Requested version to download
	 * @param string $wp_filesystem_download_directory Allocated temporary empty directory
	 * @param string $title Used in messages
	 *
	 * @return string|WP_Error Path to the downloaded directory
	 */
	private function download($user_slash_repo, $version, $wp_filesystem_download_directory, $title)
	{
		$http = new WP_Http();

		$response = $http->get('https://api.github.com/repos/'. $user_slash_repo .'/releases');

		unset($http);

		if (wp_remote_retrieve_response_code($response) !== 200) {
			return new WP_Error(
				'fw_ext_update_github_download_releases_failed',
				sprintf(__('Failed to access Github repository "%s" releases.', 'fw'), $user_slash_repo)
			);
		}

		$releases = json_decode($response['body'], true);

		unset($response);

		if (empty($releases)) {
			return new WP_Error(
				'fw_ext_update_github_download_no_releases',
				sprintf(
					__('%s github repository "%s" has no releases.', 'fw'),
					$title, $user_slash_repo
				)
			);
		}

		$release = false;

		foreach ($releases as $_release) {
			if ($_release['tag_name'] === $version) {
				$release = $_release;
			}
		}

		if (empty($release)) {
			return new WP_Error(
				'fw_ext_update_github_download_not_existing_release',
				sprintf(
					__('%s release "%s" does not exist.', 'fw'),
					$title, $version
				)
			);
		}

		$http = new WP_Http();

		$response = $http->request($release['zipball_url'], array(
			'timeout' => $this->download_timeout,
		));

		unset($http);

		if (wp_remote_retrieve_response_code($response) !== 200) {
			return new WP_Error(
				'fw_ext_update_github_download_failed',
				sprintf(__('Cannot download %s zip.', 'fw'), $title)
			);
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$zip_path = $wp_filesystem_download_directory .'/temp.zip';

		// save zip to file
		if (!$wp_filesystem->put_contents($zip_path, $response['body'])) {
			return new WP_Error(
				'fw_ext_update_github_save_download_failed',
				sprintf(__('Cannot save %s zip.', 'fw'), $title)
			);
		}

		unset($response);

		$unzip_result = unzip_file(
			FW_WP_Filesystem::filesystem_path_to_real_path($zip_path),
			$wp_filesystem_download_directory
		);

		if (is_wp_error($unzip_result)) {
			return $unzip_result;
		}

		// remove zip file
		if (!$wp_filesystem->delete($zip_path, false, 'f')) {
			return new WP_Error(
				'fw_ext_update_github_remove_downloaded_zip_failed',
				sprintf(__('Cannot remove %s zip.', 'fw'), $title)
			);
		}

		$unzipped_dir_files = $wp_filesystem->dirlist($wp_filesystem_download_directory);

		if (!$unzipped_dir_files) {
			return new WP_Error(
				'fw_ext_update_github_unzipped_dir_fail',
				__('Cannot access the unzipped directory files.', 'fw')
			);
		}

		/**
		 * get first found directory
		 * (if everything worked well, there should be only one directory)
		 */
		foreach ($unzipped_dir_files as $file) {
			if ($file['type'] == 'd') {
				return $wp_filesystem_download_directory .'/'. $file['name'];
			}
		}

		return new WP_Error(
			'fw_ext_update_github_unzipped_dir_not_found',
			sprintf(__('The unzipped %s directory not found.', 'fw'), $title)
		);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _get_framework_latest_version($force_check)
	{
		$user_slash_repo = fw()->manifest->get($this->manifest_key);

		if (empty($user_slash_repo)) {
			return false;
		}

		return $this->get_latest_version(
			$user_slash_repo,
			$force_check,
			__('Framework', 'fw')
		);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _download_framework($version, $wp_filesystem_download_directory)
	{
		return $this->download(
			fw()->manifest->get($this->manifest_key),
			$version,
			$wp_filesystem_download_directory,
			__('Framework', 'fw')
		);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _get_theme_latest_version($force_check)
	{
		$user_slash_repo = fw()->theme->manifest->get($this->manifest_key);

		if (empty($user_slash_repo)) {
			return false;
		}

		return $this->get_latest_version(
			$user_slash_repo,
			$force_check,
			__('Theme', 'fw')
		);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _download_theme($version, $wp_filesystem_download_directory)
	{
		return $this->download(
			fw()->theme->manifest->get($this->manifest_key),
			$version,
			$wp_filesystem_download_directory,
			__('Theme', 'fw')
		);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _get_extension_latest_version(FW_Extension $extension, $force_check)
	{
		$user_slash_repo = $extension->manifest->get($this->manifest_key);

		if (empty($user_slash_repo)) {
			return false;
		}

		return $this->get_latest_version(
			$user_slash_repo,
			$force_check,
			sprintf(__('%s extension', 'fw'), $extension->manifest->get_name())
		);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _download_extension(FW_Extension $extension, $version, $wp_filesystem_download_directory)
	{
		return $this->download(
			$extension->manifest->get($this->manifest_key),
			$version,
			$wp_filesystem_download_directory,
			sprintf(__('%s extension', 'fw'), $extension->manifest->get_name())
		);
	}
}
