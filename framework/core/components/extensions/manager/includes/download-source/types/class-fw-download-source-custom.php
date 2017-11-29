<?php defined( 'FW' ) or die();

class FW_Ext_Download_Source_Custom extends FW_Ext_Download_Source {
	private $download_timeout = 300;

	public function get_type() {
		return 'custom';
	}

	/**
	 * @param array $set
	 * @param string $zip_path
	 *
	 * @return WP_Error|bool
	 */
	public function download( array $set, $zip_path ) {

		global $wp_filesystem;

		$wp_error_id    = 'fw_ext_custom_download_source';
		$extension_name = $set['extension_name'];
		$transient_name = 'fw_ext_mngr_gh_dl';

		$cache = ( $c = get_site_transient( $transient_name ) ) && $c !== false ? $c : array();

		if ( isset( $cache[ $extension_name ] ) ) {
			$download_link = $cache[ $extension_name ]['zipball_url'];
		} else {

			$requirements = fw()->theme->manifest->get( 'requirements/extensions' );

			if ( isset( $requirements[ $set['extension_name'] ] ) && isset( $requirements[ $set['extension_name'] ]['max_version'] ) ) {
				$tag = $requirements[ $set['extension_name'] ]['max_version'];
			} else {
				$tag = $this->get_version( $set );

				if ( is_wp_error( $tag ) ) {
					return $tag;
				}
			}
			// Ex: https://downloads.wordpress.org/plugin/your_plugin.1.0.8.zip
			$download_link = apply_filters( 'fw_custom_url_zip', esc_url( "{$set['remote']}.{$tag}.zip" ), $set );

			$cache[ $extension_name ] = array( 'zipball_url' => $download_link, 'tag_name' => $tag );

			set_site_transient( $transient_name, $cache, HOUR_IN_SECONDS );
		}

		if ( $set['plugin'] ) {
			return $this->install_plugin( $set, $download_link );
		}

		$request = wp_remote_request(
			$download_link,
			array(
				'method'  => isset( $set['method'] ) ? $set['method'] : 'GET',
				'timeout' => $this->download_timeout,
				'body'    => $set
			)
		);

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		if ( ! ( $body = wp_remote_retrieve_body( $request ) ) || is_wp_error( $body ) ) {
			return ! $body ? new WP_Error( $wp_error_id, sprintf( esc_html__( 'Empty zip body for extension: %s', 'fw' ), $set['extension_title'] ) ) : $body;
		}

		// save zip to file
		if ( ! $wp_filesystem->put_contents( $zip_path, $body ) ) {
			return new WP_Error( $wp_error_id, sprintf( __( 'Cannot save the "%s" extension zip.', 'fw' ), $set['extension_title'] ) );
		}

		return '';
	}

	public function get_version( $set ) {

		if ( strpos( $set['remote'], 'downloads.wordpress.org' ) !== false ) {

			include ABSPATH . 'wp-admin/includes/plugin-install.php';

			$wp_org = plugins_api(
				'plugin_information',
				array(
					'slug'   => 'translatepress-multilingual',
					'fields' => array(
						'downloaded'        => false,
						'versions'          => false,
						'reviews'           => false,
						'banners'           => false,
						'icons'             => false,
						'rating'            => false,
						'active_installs'   => false,
						'group'             => false,
						'contributors'      => false,
						'description'       => false,
						'short_description' => false,
						'donate_link'       => false,
						'tags'              => false,
						'sections'          => false,
						'homepage'          => false,
						'added'             => false,
						'last_updated'      => false,
						'compatibility'     => false,
						'tested'            => false,
						'requires'          => false,
						'downloadlink'      => true,
					)
				)
			);

			if ( is_wp_error( $wp_org ) ) {
				return new WP_Error( sprintf( __( 'Cannot get latest versions for extension: %s', 'fw' ), $set['extension_title'] ) );
			}

			return $wp_org->version;
		}

		$request = wp_remote_request(
			apply_filters( 'fw_custom_url_versions', $set['remote'], $set ),
			array(
				'method'  => isset( $set['method'] ) ? $set['method'] : 'GET',
				'timeout' => $this->download_timeout,
				'body'    => $set
			)
		);

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		if ( ! ( $version = wp_remote_retrieve_body( $request ) ) || is_wp_error( $version ) ) {
			return ! $version ? new WP_Error( sprintf( esc_html__( 'Empty version for extension: %s', 'fw' ), $set['extension_title'] ) ) : $version;
		}

		return $version;
	}

	public function install_plugin( $set, $source ) {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_active( $set['plugin'] ) ) {
			return '';
		}

		if ( ! ( $installed = get_plugins() ) || ! isset( $installed[ $set['plugin'] ] ) ) {
			if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}

			$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( array() ) );
			$install = $upgrader->install( $source );

			if ( ! $install || is_wp_error( $install ) ) {
				return new WP_Error( sprintf( __( 'Cannot install plugin: %s', 'fw' ), $set['extension_title'] ) );
			}

			if ( ! ( $installed = get_plugins() ) || ! isset( $installed[ $set['plugin'] ] ) ) {
				return new WP_Error( sprintf( __( 'Cannot find plugin: %s', 'fw' ), $set['extension_title'] ) );
			}

			$cache_plugins = ( $c = wp_cache_get( 'plugins', 'plugins' ) ) && ! empty( $c ) ? $c : array();
			$cache_plugins[''][ $set['plugin'] ] = $installed[ $set['plugin'] ];
			wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
		}

		return activate_plugin( $set['plugin'] );
	}

}





















