<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command_Backup_Demo extends FW_CLI_Command {
	/**
	 * @return FW_Extension_Backups_Demo|null
	 */
	protected static function extension() {
		return fw_ext('backups-demo');
	}

	/**
	 * Check user environment
	 */
	protected static function check() {
		FW_CLI_Command_Backup::check();
	}

	/**
	 * List Demos
	 */
	public function demos($params, $args) {
		self::check();

		$items = array();
		foreach (self::extension()->get_demos() as $id => $demo) {
			/** @var FW_Ext_Backups_Demo $demo */
			$items[] = array(
				'id' => $id,
				'title' => $demo->get_title(),
			);
		}

		\WP_CLI\Utils\format_items('table', $items, array('id', 'title'));
	}

	/**
	 * Install a Demo
	 */
	public function install($params, $args) {
		self::check();

		if (empty($params[0])) {
			WP_CLI::warning('Demo id is required');
			exit();
		}

		$id = $params[0];
		$demos = self::extension()->get_demos();

		if (!isset($demos[$id])) {
			WP_CLI::error('Demo does not exist');
			exit();
		}

		FW_CLI_Command_Backup::__feedback_hooks(true);
		self::extension()->do_install($demos[$id]);
		FW_CLI_Command_Backup::__feedback_hooks(false);
	}
}

WP_CLI::add_command( 'unyson backup-demo', 'FW_CLI_Command_Backup_Demo' );
