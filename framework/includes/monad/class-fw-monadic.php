<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

abstract class FW_Monadic implements FW_Mappable {

	/**
	 * Returns the value from inside of monad
	 *
	 * @return mixed
	 */
	abstract public function get();
}