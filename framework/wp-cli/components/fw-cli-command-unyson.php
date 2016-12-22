<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command_Unyson extends WP_CLI {

	public function extensions($params, $args) {
		if ( class_exists('FW_CLI_Command_Extensions') ) {
			return new FW_CLI_Command_Extensions($params, $args);
		}
	}

}

WP_CLI::add_command( 'unyson', 'FW_CLI_Command_Unyson' );