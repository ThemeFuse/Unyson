<?php if (!defined('FW')) die('Forbidden');

/**
 * Log debug information in ABSPATH/fw-update.log
 * @param string $message
 * @return bool|void
 */
function _fw_update_debug_log($message) {
	/** @var WP_Filesystem_Base $wp_filesystem */
	global $wp_filesystem;

	if (!$wp_filesystem) {
		return;
	}

	$file_fs_path = fw_fix_path($wp_filesystem->abspath()) .'/fw-update.log';

	if ($wp_filesystem->exists($file_fs_path)) {
		$current_log = $wp_filesystem->get_contents($file_fs_path);

		if ($current_log === false) {
			return false;
		}
	} else {
		$current_log = '';
	}

	$message = '['. date('Y-m-d H:i:s') .'] '. $message;

	$wp_filesystem->put_contents($file_fs_path, $current_log ."\n". $message);
}

add_action('fw_plugin_pre_update', '_action_fw_update_debug_before_update', 7);
function _action_fw_update_debug_before_update() {
	_fw_update_debug_log('before plugin update');
}

add_action('fw_plugin_post_update', '_action_fw_update_debug_after_update', 7);
function _action_fw_update_debug_after_update() {
	_fw_update_debug_log('after plugin update');
}

add_action('fw_plugin_auto_update_stop', '_action_fw_update_debug_auto_update_stop', 7);
function _action_fw_update_debug_auto_update_stop() {
	_fw_update_debug_log('auto plugin prevented');
}
