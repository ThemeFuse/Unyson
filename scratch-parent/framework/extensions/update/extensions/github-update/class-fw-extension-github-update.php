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
	 * {@inheritdoc}
	 * @internal
	 */
	public function _get_framework_latest_version($force_check)
	{
		$user_slash_repo = fw()->manifest->get($this->manifest_key);

		if (empty($user_slash_repo)) {
			return false;
		}

		if (!preg_match($this->manifest_key_regex, $user_slash_repo)) {
			return new WP_Error('fw_ext_update_github_framework_manifest_invalid',
				__('Framework manifest has invalid "github_update" parameter. Please use "user/repo" format.', 'fw')
			);
		}

		$theme_id = preg_replace('[^a-z0-9_]', '_', fw()->theme->manifest->get_id());
		$transient_id = 'fw_ext_update_gh_'. $theme_id .'_fw'; // this should be 45 characters or less

		if ($force_check) {
			delete_site_transient($transient_id);
		} else {
			$cache = get_site_transient($transient_id);

			if ($cache !== false && isset($cache[$user_slash_repo])) {
				return $cache[$user_slash_repo];
			}
		}

		$latest_version = $this->fetch_latest_version($user_slash_repo);

		if (empty($latest_version)) {
			return new WP_Error(
				sprintf(__('Failed to fetch framework latest version from github "%s".', 'fw'), $user_slash_repo)
			);
		}

		if (is_wp_error($latest_version)) {
			return $latest_version;
		}

		set_site_transient(
			$transient_id,
			array($user_slash_repo => $latest_version),
			$this->transient_expiration
		);

		return $latest_version;
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _download_framework($version, $wp_filesystem_download_directory)
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$user_slash_repo = fw()->manifest->get($this->manifest_key);

		$http = new WP_Http();

		$response = $http->get('https://api.github.com/repos/'. $user_slash_repo .'/releases');

		unset($http);

		if (wp_remote_retrieve_response_code($response) !== 200) {
			return new WP_Error('fw_ext_update_github_framework_download_releases_failed',
				__('Failed to access Github repository releases.', 'fw')
			);
		}

		$releases = json_decode($response['body'], true);

		unset($response);

		if (empty($releases)) {
			return new WP_Error('fw_ext_update_github_framework_download_no_releases',
				__('Github repository has no releases.', 'fw')
			);
		}

		$release = false;

		foreach ($releases as $_release) {
			if ($_release['tag_name'] === $version) {
				$release = $_release;
			}
		}

		if (empty($release)) {
			return new WP_Error('fw_ext_update_github_framework_download_not_existing_release',
				sprintf(__('Requested version (release) for download does not exists "%s".', 'fw'), $version)
			);
		}

		$http = new WP_Http();

		$response = $http->request($release['zipball_url'], array(
			'timeout' => $this->download_timeout,
		));

		unset($http);

		if (wp_remote_retrieve_response_code($response) !== 200) {
			return new WP_Error('fw_ext_update_github_framework_download_failed',
				__('Cannot download the framework zip.', 'fw')
			);
		}

		$zip_path = $wp_filesystem_download_directory .'/temp.zip';

		// save zip to file
		if (!$wp_filesystem->put_contents($zip_path, $response['body'])) {
			return new WP_Error('fw_ext_update_github_framework_save_download_failed',
				__('Cannot save the framework zip.', 'fw')
			);
		}

		unset($response);

		$unzip_result = unzip_file(FW_WP_Filesystem::filesystem_path_to_real_path($zip_path), $wp_filesystem_download_directory);

		if (is_wp_error($unzip_result)) {
			return $unzip_result;
		}

		// remove zip file
		if (!$wp_filesystem->delete($zip_path, false, 'f')) {
			return new WP_Error('fw_ext_update_github_framework_remove_downloaded_zip_failed',
				__('Cannot remove the framework zip.', 'fw')
			);
		}

		$unzipped_dir_files = $wp_filesystem->dirlist($wp_filesystem_download_directory);

		if (!$unzipped_dir_files) {
			return new WP_Error('fw_ext_update_github_framework_unzipped_dir_fail',
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

		return new WP_Error('fw_ext_update_github_framework_unzipped_dir_not_found',
			__('The unzipped framework directory not found.', 'fw')
		);
	}
}
