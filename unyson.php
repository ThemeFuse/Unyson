<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );
/**
 * Plugin Name: Unyson
 * Plugin URI: http://unyson.themefuse.com/
 * Description: A free drag & drop framework that comes with a bunch of built in extensions that will help you develop premium themes fast & easy.
 * Version: 2.1.24
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
			{
				require_once dirname(__FILE__) .'/framework/includes/term-meta/function_fw_term_meta_setup_blog.php';

				if (is_multisite() && is_network_admin()) {
					global $wpdb;

					$blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}'" );
					foreach ( $blogs as $blog_id ) {
						switch_to_blog( $blog_id );
						_fw_term_meta_setup_blog( $blog_id );
					}

					do {} while ( restore_current_blog() );
				} else {
					_fw_term_meta_setup_blog();
				}
			}

			// add special option (is used in another action)
			update_option('_fw_plugin_activated', true);
		}
		register_activation_hook( __FILE__, '_action_fw_plugin_activate' );

		/** @internal */
		function _action_fw_plugin_check_if_was_activated() {
			if (get_option('_fw_plugin_activated')) {
				delete_option('_fw_plugin_activated');

				do_action('fw_after_plugin_activate');
			}
		}
		add_action('current_screen', '_action_fw_plugin_check_if_was_activated', 100);
		// as late as possible, but to be able to make redirects (content not started)

		/** @internal */
		function _action_fw_term_meta_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				require_once dirname(__FILE__) .'/framework/includes/term-meta/function_fw_term_meta_setup_blog.php';

				switch_to_blog( $blog_id );
				_fw_term_meta_setup_blog();
				do {} while ( restore_current_blog() );
			}
		}
		add_action( 'wpmu_new_blog', '_action_fw_term_meta_new_blog', 10, 6 );

		/**
		 * @param int $blog_id Blog ID
		 * @param bool $drop True if blog's table should be dropped. Default is false.
		 * @internal
		 */
		function _action_fw_delete_blog( $blog_id, $drop ) {
			if ($drop) { // delete table created by the _fw_term_meta_setup_blog() function
				/** @var WPDB $wpdb */
				global $wpdb;

				if (property_exists($wpdb, 'fw_termmeta')) { // it should exist, but check to be sure
					$wpdb->query("DROP TABLE IF EXISTS {$wpdb->fw_termmeta};");
				}
			}
		}
		add_action( 'delete_blog', '_action_fw_delete_blog', 10, 2 );

		/** @internal */
		function _filter_fw_check_if_plugin_pre_update( $result, $data ) {
			if ( isset( $data['plugin'] ) && $data['plugin'] === plugin_basename( __FILE__ ) ) {
				/**
				 * Before plugin update
				 */
				do_action( 'fw_plugin_pre_update' );
			}

			return $result;
		}
		add_filter( 'upgrader_pre_install', '_filter_fw_check_if_plugin_pre_update', 10, 2 );

		/** @internal */
		function _filter_fw_check_if_plugin_post_update( $result, $data ) {
			if ( isset( $data['plugin'] ) && $data['plugin'] === plugin_basename( __FILE__ ) ) {
				/**
				 * After plugin update
				 */
				do_action( 'fw_plugin_post_update' );
			}

			return $result;
		}
		add_filter( 'upgrader_post_install', '_filter_fw_check_if_plugin_post_update', 10, 2 );

		/** @internal */
		function _filter_fw_plugin_action_list( $actions ) {
			return apply_filters( 'fw_plugin_action_list', $actions );
		}
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), '_filter_fw_plugin_action_list' );

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
