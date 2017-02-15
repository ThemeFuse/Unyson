<?php

if (!defined('ABSPATH')) {
    die();
}

/**
 * Manage Unyson plugin.
 *
 * ## EXAMPLES
 *
 *	# Unyson version
 *	$ wp unyson version
 *
 *	# Extensions
 *	$ wp unyson extensions --help
 *
 *	# List of all extensions.
 *	$ wp unyson extensions list
 *
 *	# Theme Settings
 *	$ wp unyson theme-settings --help
 *
 *	# Full theme settings structure.
 *	$ wp unyson theme-settings get --format=json
 *
 */
class FW_CLI_Command_Unyson extends FW_CLI_Command {

	public function version() {
		WP_CLI::line( fw()->manifest->get_version() );
	}

}

WP_CLI::add_command( 'unyson', 'FW_CLI_Command_Unyson' );