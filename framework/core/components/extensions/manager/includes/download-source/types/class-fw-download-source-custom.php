<?php defined( 'FW' ) or die();

class FW_Ext_Download_Source_Custom extends FW_Ext_Download_Source {
	private $download_timeout = 300;
	// Used in filter http_request_args when extension is as plugin we use api worpdress Plugin_Upgrader.
	private $set              = array();

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
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$wp_error_id    = 'fw_ext_custom_download_source';
		$transient_name = 'fw_ext_mngr_gh_dl';
		$requirements   = fw()->theme->manifest->get( 'requirements/extensions' );
		$set['type']    = 'extension';

		if ( isset( $requirements[ $set['extension_name'] ] ) && isset( $requirements[ $set['extension_name'] ]['max_version'] ) ) {
			$set['tag'] = $requirements[ $set['extension_name'] ]['max_version'];
		} else {
			$set['tag'] = $this->get_version( $set );

			if ( is_wp_error( $set['tag'] ) ) {
				return $set['tag'];
			}
		}

		$cache = ( $c = get_site_transient( $transient_name ) ) && $c !== false ? $c : array();
		$cache[ $set['item'] ] = array( 'tag_name' => $set['tag'] );
		set_site_transient( $transient_name, $cache, HOUR_IN_SECONDS );

		if ( $set['plugin'] ) {
			return $this->install_plugin( $set, $set['remote'] );
		}

		$request = wp_remote_post(
			$set['remote'],
			array(
				'timeout' => $this->download_timeout,
				'body'    => json_encode( array_merge( $set, array( 'pull' => 'zip' ) ) )
			)
		);

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		if ( ! ( $body = wp_remote_retrieve_body( $request ) ) || is_wp_error( $body ) ) {
			return ! $body ? new WP_Error( $wp_error_id, sprintf( esc_html__( 'Empty zip body for extension: %s', 'fw' ), $set['extension_title'] ) ) : $body;
		}

		// Try to extract error if server returned json with key error, if not then is an archive zip.
		if ( ( $error = json_decode( $body, true ) ) && isset( $error['error'] ) ) {
			return new WP_Error( $wp_error_id, $error['error'] );
		}

		// save zip to file
		if ( ! $wp_filesystem->put_contents( $zip_path, $body ) ) {
			return new WP_Error( $wp_error_id, sprintf( __( 'Cannot save the "%s" extension zip.', 'fw' ), $set['name'] ) );
		}

		return '';
	}

	public function get_version( $set ) {

		if ( $this->is_wp_org( $set['remote'] ) ) {

			include ABSPATH . 'wp-admin/includes/plugin-install.php';

			$wp_org = plugins_api(
				'plugin_information',
				array(
					'slug'   => $set['extension_name'],
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

		$request = wp_remote_post(
			$set['remote'],
			array(
				'timeout' => $this->download_timeout,
				'body'    => json_encode( array_merge( $set, array( 'pull' => 'version' ) ) )
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

			if ( $this->is_wp_org( $set['remote'] ) ) {
				$source = esc_url( "{$source}.{$set['tag']}.zip" );
			}

			$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
			// To easy access download settings in function http_request_args.
			$this->set = $set;
			add_filter( 'http_request_args', array( $this, 'http_request_args' ) );

			$install = $upgrader->install( $source );

			remove_filter( 'http_request_args', array( $this, 'http_request_args' ) );

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

		// A small financial support for maintaining the plugin.
		if ( 'translatepress-multilingual/index.php' === $set['plugin'] ) {
			update_option( 'translatepress_affiliate_id', 1 );
		}

		return activate_plugin( $set['plugin'] );
	}

	public function http_request_args( $r ) {
		$r['fw_set'] = json_encode( array_merge( $this->set, array( 'type' => 'extension' ) ) );
		return $r;
	}

	public function is_wp_org( $url ) {
		return strpos( $url, 'downloads.wordpress.org' ) !== false;
	}
}





















