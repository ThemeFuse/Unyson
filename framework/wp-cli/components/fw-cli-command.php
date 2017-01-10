<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command extends WP_CLI_Command {

	protected $params = array();

	protected $args = array();

	protected function wp_error($response) {
		if (isset($response->errors)) {
			foreach( $response->errors as $key => $message) {
				if (isset($message[0])) {
					WP_CLI::error($message[0]);
				}
			}
		}
	}

	protected function require_filesystem() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
	}

}