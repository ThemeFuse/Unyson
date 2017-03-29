<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );
/**
 * Plugin Name: Unyson
 * Plugin URI: http://unyson.io/
 * Description: A free drag & drop framework that comes with a bunch of built in extensions that will help you develop premium themes fast & easy.
 * Version: 2.6.16
 * Author: ThemeFuse
 * Author URI: http://themefuse.com
 * License: GPL2+
 * Text Domain: fw
 * Domain Path: /framework/languages
 */

if (defined('FW')) {
	/**
	 * The plugin was already loaded (maybe as another plugin with different directory name)
	 */
} else {
	require dirname( __FILE__ ) . '/framework/bootstrap.php';

	/**
	 * Plugin related functionality
	 *
	 * Note:
	 * The framework doesn't know that it's used as a plugin.
	 * It can be localed in the theme directory or any other directory.
	 * Only its path and uri is known
	 */
	{
		/** @internal */
		function _action_fw_plugin_activate() {
			update_option('_fw_plugin_activated', true, false); // add special option (is used in another action)

			if (
				did_action('after_setup_theme') // @see framework/bootstrap.php
				&&
				!did_action('fw_init') // must not be loaded
			) {
				_action_init_framework(); // load (prematurely) the plugin

				/** @since 2.6.8 */
				do_action('fw_plugin_activate');
			}
		}
		register_activation_hook( __FILE__, '_action_fw_plugin_activate' );

		/** @internal */
		function _action_fw_plugin_check_if_was_activated() {
			if (get_option('_fw_plugin_activated')) {
				delete_option('_fw_plugin_activated');

				do_action('fw_after_plugin_activate');
			}
		}
		add_action(
			'current_screen', // as late as possible, but to be able to make redirects (content not started)
			'_action_fw_plugin_check_if_was_activated',
			100
		);

		/**
		 * @param int $blog_id Blog ID
		 * @param bool $drop True if blog's table should be dropped. Default is false.
		 * @internal
		 */
		function _action_fw_delete_blog( $blog_id, $drop ) {
			if ($drop) {
				global $wpdb; /** @var WPDB $wpdb */

				// delete old termmeta table
				$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}fw_termmeta`;");
			}
		}
		add_action( 'delete_blog', '_action_fw_delete_blog', 10, 2 );

		/** @internal */
		function _filter_fw_plugin_action_list( $actions ) {
			return apply_filters( 'fw_plugin_action_list', $actions );
		}
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), '_filter_fw_plugin_action_list' );

		/** @internal */
		function _action_fw_textdomain() {
			load_plugin_textdomain( 'fw', false, plugin_basename( dirname( __FILE__ ) ) . '/framework/languages' );
		}
		add_action( 'fw_before_init', '_action_fw_textdomain', 3 );

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

		/** @internal */
		final class _FW_Update_Hooks {
			public static function _init() {
				add_filter( 'upgrader_pre_install',  array(__CLASS__, '_filter_fw_check_if_plugin_pre_update'),  9999, 2 );
				add_filter( 'upgrader_post_install', array(__CLASS__, '_filter_fw_check_if_plugin_post_update'), 9999, 2 );
				add_action( 'automatic_updates_complete', array(__CLASS__, '_action_fw_automatic_updates_complete') );
			}

			public static function _filter_fw_check_if_plugin_pre_update( $result, $data ) {
				if (
					!is_wp_error($result)
					&&
					isset( $data['plugin'] )
					&&
					plugin_basename( __FILE__ ) === $data['plugin']
				) {
					/**
					 * Before plugin update
					 * The plugin was already download and extracted to a temp directory
					 * and it's right before being replaced with the new downloaded version
					 */
					do_action( 'fw_plugin_pre_update' );
				}

				return $result;
			}

			public static function _filter_fw_check_if_plugin_post_update( $result, $data ) {
				if (
					!is_wp_error($result)
					&&
					isset( $data['plugin'] )
					&&
					plugin_basename( __FILE__ ) === $data['plugin']
				) {
					/**
					 * After plugin successfully updated
					 */
					do_action( 'fw_plugin_post_update' );
				}

				return $result;
			}

			public static function _action_fw_automatic_updates_complete($results) {
				if (!isset($results['plugin'])) {
					return;
				}

				foreach ($results['plugin'] as $plugin) {
					if (plugin_basename( __FILE__ ) === strtolower($plugin->item->plugin)) {
						do_action( 'fw_automatic_update_complete', $plugin->result );
						break;
					}
				}
			}
		}
		_FW_Update_Hooks::_init();
	}
}
