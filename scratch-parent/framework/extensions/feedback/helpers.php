<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Returns a custom Walker class object to use when rendering the reviews.
 */
function fw_ext_feedback_get_listing_walker() {
	return apply_filters( 'fw_ext_feedback_listing_walker', new Walker_Comment() );
}