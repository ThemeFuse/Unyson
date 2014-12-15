<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );
/**
 * Plugin Name: Unyson
 * Plugin URI: http://unyson.themefuse.com/
 * Description: A free drag & drop framework that comes with a bunch of built in extensions that will help you develop premium themes fast & easy.
 * Version: 2.1.8
 * Author: ThemeFuse
 * Author URI: http://themefuse.com
 * License: GPL2+
 * Text Domain: fw
 * Domain Path: /languages/
 */

if (defined('FW')) {
	/**
	 * The plugin was already loaded (maybe as another plugin with different directory name)
	 */
} else {

	{
		/** @internal */
		function _filter_fw_framework_plugin_directory_uri() {
			return plugin_dir_url( __FILE__ ) . 'framework';
		}
		add_filter( 'fw_framework_directory_uri', '_filter_fw_framework_plugin_directory_uri' );
	}

	require dirname( __FILE__ ) . '/framework/bootstrap.php';

	/**
	 * Plugin related functionality
	 *
	 * Note:
	 * The framework doesn't know that it's used as a plugin.
	 * It can be localed in the theme directory or any other directory.
	 * Only its path and uri is known (specified above)
	 */
	{
		/** @internal */
		function _action_fw_plugin_activate() {
			foreach ( glob( dirname( __FILE__ ) . '/framework/includes/on-plugin-activation/*.php' ) as $file ) {
				require_once $file;
			}
		}
		register_activation_hook( __FILE__, '_action_fw_plugin_activate' );

		/** @internal */
		function _action_term_meta_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				_fw_term_meta_setup_blog( $blog_id );
			}
		}
		add_action( 'wpmu_new_blog', '_action_term_meta_new_blog', 10, 6 );

		/** @internal */
		function _filter_check_if_plugin_pre_update( $result, $data ) {
			if ( isset( $data['plugin'] ) && $data['plugin'] === plugin_basename( __FILE__ ) ) {
				/**
				 * Before plugin update
				 */
				do_action( 'fw_plugin_pre_update' );
			}

			return $result;
		}
		add_filter( 'upgrader_pre_install', '_filter_check_if_plugin_pre_update', 10, 2 );

		/** @internal */
		function _filter_check_if_plugin_post_update( $result, $data ) {
			if ( isset( $data['plugin'] ) && $data['plugin'] === plugin_basename( __FILE__ ) ) {
				/**
				 * After plugin update
				 */
				do_action( 'fw_plugin_post_update' );
			}

			return $result;
		}
		add_filter( 'upgrader_post_install', '_filter_check_if_plugin_post_update', 10, 2 );

		/** @internal */
		function _filter_plugin_action_list( $actions ) {
			return apply_filters( 'fw_plugin_action_list', $actions );
		}
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), '_filter_plugin_action_list' );

		/** @internal */
		function _action_fw_textdomain() {
			load_plugin_textdomain( 'fw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		add_action( 'plugins_loaded', '_action_fw_textdomain' );

		/** @internal */
		function _filter_fw_tmp_dir( $dir ) {
			/**
			 * Some users force WP_Filesystem to use the 'direct' method <?php define( 'FS_METHOD', 'direct' ); ?> and set chmod 777 to the unyson/ plugin.
			 * By default tmp dir is WP_CONTENT_DIR.'/tmp' and WP_Filesystem can't create it with 'direct' method, then users can't download and install extensions.
			 * In order to prevent this situation, create the temporary directory inside the plugin folder.
			 */
			return dirname( __FILE__ ) . '/tmp';
		}
		add_filter( 'fw_tmp_dir', '_filter_fw_tmp_dir' );
	}
}
