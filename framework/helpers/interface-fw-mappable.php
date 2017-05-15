<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

interface FW_Mappable {
	/**
	 * @param $f Callable
	 *
	 * @return FW_Mappable
	 */
	public function map( $f );
}