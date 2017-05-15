<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Nothing extends FW_Maybe {

	public function map( $f ) {
		return $this;
	}

	public function get( $or_else = null ) {
		return $or_else;
	}

	public function is_just() {
		return false;
	}
}