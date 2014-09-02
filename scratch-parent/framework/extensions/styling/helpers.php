<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/*
 * Get option from styling extension
 * @var $option string
 * @var $default mixed
 * @return mixed
 */
function fw_ext_styling_get( $option, $default = null ) {

	static $options = null;

	if ( $options === null ) {
		$options = fw_get_db_extension_data( 'styling', 'options', array() );
	}

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}

	return $default;
}
