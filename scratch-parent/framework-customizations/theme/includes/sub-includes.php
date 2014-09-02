<?php if (!defined('FW')) die('Forbidden');

/*
 * Add Featured Content functionality.
 *
 * To overwrite in a plugin, define your own FW_Theme_Featured_Content class on or
 * before the 'setup_theme' hook.
 */
if ( ! class_exists( 'FW_Theme_Featured_Content' ) && 'plugins.php' !== $GLOBALS['pagenow'] ) {
	require dirname(__FILE__) .'/sub-includes/featured-content.php';
}