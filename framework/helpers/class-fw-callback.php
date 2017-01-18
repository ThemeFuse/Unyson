<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Callback {
	/**
	 * @var $callback string|array
	 */
	private $callback;

	/**
	 * @var array $args
	 */
	private $args;

	public function __construct( $callback, array $args = array() ) {
		$this->callback = $callback;
		$this->args     = $args;
	}

	public function get_callback() {
		return $this->callback;
	}

	public function get_args() {
		return $this->args;
	}

	public function execute() {
		return call_user_func_array( $this->callback, $this->args );
	}
}