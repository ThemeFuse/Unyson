<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Return the analytics code.
 * @return string
 */
function fw_ext_get_analytics() {
	return fw()->extensions->get('analytics')->get_analytics_code();
}