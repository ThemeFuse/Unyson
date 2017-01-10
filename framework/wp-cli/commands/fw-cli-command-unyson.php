<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command_Unyson extends FW_CLI_Command {

	public function version() {
		WP_CLI::line( fw()->manifest->get_version() );
	}

}

WP_CLI::add_command( 'unyson', 'FW_CLI_Command_Unyson' );