<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Returns the breadcrumbs HTML
 *
 * @param string $separator, separator symbol that will be set between elements
 *
 * @return string
 */
function fw_ext_breadcrumbs_render( $separator = ">" ) {
	return fw()->extensions->get( 'breadcrumbs' )->render( $separator, false, 'breadcrumbs' );
}