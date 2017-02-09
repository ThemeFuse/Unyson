<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Form_Invalid_Submission_Exception extends Exception {

	private $errors = array();

	public function __construct( array $errors ) {
		parent::__construct();

		$this->set_errors( $errors );
	}

	public function get_errors() {
		return $this->errors;
	}

	protected function set_errors( array $errors ) {
		$this->errors = $errors;
	}
}