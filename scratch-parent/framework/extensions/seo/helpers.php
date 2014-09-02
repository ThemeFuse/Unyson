<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Parse a string value and replaces the SEO tags, within string, with their values
 * This function needs to be used after wordpress "wp" action
 *
 * @param string $value, string value that needs to be parsed
 *
 * @return string
 */
function fw_ext_seo_parse_meta_tags( $value ) {
    if( empty( $value ) ) {
	    return $value;
    }

	if( is_array( $value ) ) {
		return $value;
	}

	return fw()->extensions->get('seo')->parse_seo_tags( $value );
}