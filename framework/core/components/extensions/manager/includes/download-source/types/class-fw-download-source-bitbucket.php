<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Ext_Download_Source_Bitbucket extends FW_Ext_Download_Source {
	private $download_timeout = 300;

	public function get_type() {
		return 'bitbucket';
	}

	/**
	 * @param array $set {user_repo: 'ThemeFuse/Unyson'}
	 * @param string $zip_path
	 *
	 * @return WP_Error|boolean
	 */
	public function download( array $set, $zip_path ) {
		$wp_error_id            = 'fw_ext_bitbucket_download_source';
		$theme_ext_requirements = fw()->theme->manifest->get( 'requirements/extensions' );

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$extension_name  = $set['extension_name'];
		$extension_title = $set['extension_title'];

		if ( empty( $set['user_repo'] ) ) {
			return new WP_Error(
				$wp_error_id,
				sprintf( __( '"%s" extension bitbucket source "user_repo" parameter is required', 'fw' ), $extension_title )
			);
		}

		if ( isset( $theme_ext_requirements[ $extension_name ] ) && isset( $theme_ext_requirements[ $extension_name ]['max_version'] ) ) {
			$tag = $theme_ext_requirements[ $extension_name ]['max_version'];
		} else {
			$tag = $this->get_version( $set['user_repo'] );
		}

		$response = wp_remote_get( "https://bitbucket.org/{$set['user_repo']}/get/{$tag}.zip", array( 'timeout' => $this->download_timeout ) );

		if ( ( $response_code = intval( wp_remote_retrieve_response_code( $response ) ) ) !== 200 ) {
			if ( $response_code ) {
				return new WP_Error(
					$wp_error_id,
					sprintf( __( 'Cannot download the "%s" extension zip. (Response code: %d)', 'fw' ),
						$extension_title, $response_code
					)
				);
			} elseif ( is_wp_error( $response ) ) {
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
		if ( ! $wp_filesystem->put_contents( $zip_path, $response['body'] ) ) {
			return new WP_Error(
				$wp_error_id,
				sprintf( __( 'Cannot save the "%s" extension zip.', 'fw' ), $extension_title )
			);
		}

		return true;
	}

	private function get_version( $user_repo, $next_page = '' ) {
		/**
		 * If at least one request failed, do not do any other requests, to prevent site being blocked on every refresh.
		 * This may happen on localhost when develop your theme and you have no internet connection.
		 * Then this method will return a fake '0.0.0' version, it will be cached by the transient
		 * and will not bother you until the transient will expire, then a new request will be made.
		 * @var bool
		 */
		static $no_internet_connection = false;

		if ( $no_internet_connection ) {
			return '0.0.0';
		}

		$url = $next_page ? $next_page : "https://api.bitbucket.org/2.0/repositories/{$user_repo}/refs/tags/";
		$request = wp_remote_get( $url, array( 'timeout' => $this->download_timeout ) );

		if ( is_wp_error( $request ) ) {
			if ( $request->get_error_code() === 'http_request_failed' ) {
				$no_internet_connection = true;
			}

			return $request;
		}

		if ( ! ( $versions = json_decode( wp_remote_retrieve_body( $request ), true ) ) || is_wp_error( $versions ) ) {
			return ! $versions ? new WP_Error( sprintf( __( 'Empty version for item: %s', 'fw' ), $user_repo ) ) : $versions;
		}

		if ( isset( $versions['next'] ) ) {
			return $this->get_version( $user_repo, $versions['next'] );
		}

		$data_version = end( $versions['values'] );

		return ! empty( $data_version['name'] ) ? $data_version['name'] : new WP_Error( sprintf( __( 'Wrong Bibucket version for item: %s', 'fw' ), $user_repo ) );
	}
}
