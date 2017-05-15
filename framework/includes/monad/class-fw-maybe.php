<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Maybe extends FW_Monadic {

	/**
	 * @param $value
	 *
	 * @return FW_Maybe|FW_Nothing
	 */
	public static function create( $value ) {
		return $value === null || is_wp_error( $value )
			? new FW_Nothing( $value )
			: new FW_Maybe( $value );
	}

	private $value;


	/**
	 * @inheritdoc
	 */
	public function map( $f ) {
		return FW_Maybe::create( call_user_func( $f, $this->get_value() ) );
	}

	/**
	 * @inheritdoc
	 */
	public function get( $or_else = null ) {
		return $this->get_value();
	}

	/**
	 * Checks if the current maybe is a just
	 *
	 * @return bool
	 */
	public function is_just() {
		return true;
	}

	protected function __construct( $value ) {
		$this->value = $value;
	}

	protected function get_value() {
		return $this->value;
	}
}