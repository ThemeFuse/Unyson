<?php

if (!defined('ABSPATH')) {
    die();
}

if (!defined('WP_CLI')) {
	return;
}

require __DIR__ . '/components/fw-cli-command.php';

$fw_cli_commands = glob( __DIR__ . '/commands/*.php' );

if (count($fw_cli_commands)) {
	foreach ( $fw_cli_commands as $command_class_file ) {
		require_once $command_class_file;
	}
}