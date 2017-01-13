<?php

if (!defined('ABSPATH')) {
    die();
}

if (!defined('WP_CLI')) {
	return;
}

require __DIR__ . '/components/fw-cli-command.php';

require_once __DIR__ . '/commands/fw-cli-command-unyson.php';
require_once __DIR__ . '/commands/fw-cli-command-extensions.php';
require_once __DIR__ . '/commands/fw-cli-command-backup.php';