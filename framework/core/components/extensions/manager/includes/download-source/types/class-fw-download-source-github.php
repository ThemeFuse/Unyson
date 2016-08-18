<?php if (! defined('FW')) { die('Forbidden'); }

class FW_Ext_Download_Source_Github extends FW_Ext_Download_Source
{
	private $download_timeout = 300;

	public function get_type() {
		return 'github';
	}

	/**
	 * @param array $opts {user_repo: 'ThemeFuse/Unyson'}
	 * @param string $zip_path
	 *
	 * @return WP_Error
	 */
	public function download(array $opts, $zip_path) {
		$wp_error_id = 'fw_ext_github_download_source';
		$theme_ext_requirements = fw()->theme->manifest->get('requirements/extensions');

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$extension_name = $opts['extension_name'];
		$extension_title = $opts['extension_title'];

		if (empty($opts['user_repo'])) {
			return new WP_Error(
				$wp_error_id,
				sprintf(__('"%s" extension github source "user_repo" parameter is required', 'fw'), $extension_title)
			);
		}

		{
			$transient_name = 'fw_ext_mngr_gh_dl';
			$transient_ttl  = HOUR_IN_SECONDS;

			$cache = get_site_transient($transient_name);

			if ($cache === false) {
				$cache = array();
			}
		}

		if (isset($cache[ $opts['user_repo'] ])) {
			$download_link = $cache[ $opts['user_repo'] ]['zipball_url'];
		} else {
			$http = new WP_Http();

			if (
				isset($theme_ext_requirements[$extension_name])
				&&
				isset($theme_ext_requirements[$extension_name]['max_version'])
			) {
				$tag = 'tags/v'. $theme_ext_requirements[$extension_name]['max_version'];
			} else {
				$tag = 'latest';
			}

			$response = $http->get(
				apply_filters('fw_github_api_url', 'https://api.github.com')
				. '/repos/'. $opts['user_repo'] .'/releases/'. $tag
			);

			unset($http);

			$response_code = intval(wp_remote_retrieve_response_code($response));

			if ($response_code !== 200) {
				if ($response_code === 403) {
					if ($json_response = json_decode($response['body'], true)) {
						return new WP_Error(
							$wp_error_id,
							__('Github error:', 'fw') .' '. $json_response['message']
						);
					} else {
						return new WP_Error(
							$wp_error_id,
							sprintf(
								__( 'Failed to access Github repository "%s" releases. (Response code: %d)', 'fw' ),
								$opts['user_repo'], $response_code
							)
						);
					}
				} elseif ($response_code) {
					return new WP_Error(
						$wp_error_id,
						sprintf(
							__( 'Failed to access Github repository "%s" releases. (Response code: %d)', 'fw' ),
							$opts['user_repo'], $response_code
						)
					);
				} elseif (is_wp_error($response)) {
					return new WP_Error(
						$wp_error_id,
						sprintf(
							__( 'Failed to access Github repository "%s" releases. (%s)', 'fw' ),
							$opts['user_repo'], $response->get_error_message()
						)
					);
				} else {
					return new WP_Error(
						$wp_error_id,
						sprintf(
							__( 'Failed to access Github repository "%s" releases.', 'fw' ),
							$opts['user_repo']
						)
					);
				}
			}

			$release = json_decode($response['body'], true);

			unset($response);

			if (empty($release)) {
				return new WP_Error(
					$wp_error_id,
					sprintf(
						__('"%s" extension github repository "%s" has no releases.', 'fw'),
						$extension_title, $opts['user_repo']
					)
				);
			}

			{
				$cache[ $opts['user_repo'] ] = array(
					'zipball_url' => 'https://github.com/'. $opts['user_repo'] .'/archive/'. $release['tag_name'] .'.zip',
					'tag_name' => $release['tag_name']
				);

				set_site_transient($transient_name, $cache, $transient_ttl);
			}

			$download_link = $cache[ $opts['user_repo'] ]['zipball_url'];


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
							$extension_title, $response_code
						)
					);
				} elseif (is_wp_error($response)) {
					return new WP_Error(
						$wp_error_id,
						sprintf( __( 'Cannot download the "%s" extension zip. %s', 'fw' ),
							$extension_title,
							$response->get_error_message()
						)
					);
				} else {
					return new WP_Error(
						$wp_error_id,
						sprintf( __( 'Cannot download the "%s" extension zip.', 'fw' ),
							$extension_title
						)
					);
				}
			}

			// save zip to file
			if (!$wp_filesystem->put_contents($zip_path, $response['body'])) {
				return new WP_Error(
					$wp_error_id,
					sprintf(__('Cannot save the "%s" extension zip.', 'fw'), $extension_title)
				);
			}

			unset($response);
		}
	}
}
