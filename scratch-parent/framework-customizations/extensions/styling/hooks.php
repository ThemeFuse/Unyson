<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );

if ( ! function_exists( '_action_print_quick_css' ) ) {
	/**
	 * Print styling css on front-end
	 * @internal
	 */
	function _action_print_quick_css() {

		$quick_css = fw_ext_styling_get('quick_css', '');
		if(!empty($quick_css)) {
			echo '<style type="text/css">' . $quick_css . '</style>';
		}
	}
	add_action( 'wp_head', '_action_print_quick_css', 100 );
}
