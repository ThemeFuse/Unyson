<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );

/**
 * @since 2.4.10
 */
abstract class FW_Type {
	/**
	 * @return string Unique type
	 */
	abstract public function get_type();
}
