<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command extends WP_CLI_Command {

	protected $params = array();

	protected $args = array();

	public function __construct($params, $args) {
		$this->params = $params;
		$this->args = $args;

		if( $this->call_command() ) {
			return true;
		}
	}

	protected function call_command() {
		WP_CLI::line();
		if ( isset($this->params[0]) ) {
			$method = $this->params[0];
			foreach( array($method, $method . '_') as $command ) {
				if ( method_exists($this, $command) ) {
					$this->{$command}();
					return true;
				}
			}

			WP_CLI::error('Unknown command');
		}

		return false;
	}

	protected function wp_error($response) {
		if (isset($response->errors)) {
			foreach( $response->errors as $key => $message) {
				if (isset($message[0])) {
					WP_CLI::error($message[0]);
				}
			}
		}
	}

}