<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command_Backup extends FW_CLI_Command {
	/**
	 * @return FW_Extension_Backups|null
	 */
	protected static function extension() {
		return fw_ext('backups');
	}

	/**
	 * Check user environment
	 */
	public static function check() {
		if (!self::extension()) {
			WP_CLI::error("Backup extension is not active");
			exit();
		}

		if (version_compare(fw_ext('backups')->manifest->get_version(), '2.0.23', '<')) {
			WP_CLI::error("Backup extension is outdated. v2.0.23 or newer is required");
			exit();
		}

		if (!is_writable($dir = self::extension()->get_tmp_dir())) {
			$owner = fileowner($dir);
			if (function_exists('posix_getpwuid')) {
				$owner = posix_getpwuid($owner);
				$owner = $owner['name'];
			}

			WP_CLI::error('You have no write permissions for '. $dir .' Execute the command as user: '. $owner);
			exit();
		}
	}

	/**
	 * @internal
	 */
	public static function __action_log_step() {
		//WP_CLI::log('.'); // this makes new line for each dot
		echo '.';
	}

	/**
	 * @internal
	 * @param FW_Ext_Backups_Task_Collection $collection
	 */
	public static function __action_log_success(FW_Ext_Backups_Task_Collection $collection) {
		WP_CLI::success(':)');
	}

	/**
	 * @internal
	 * @param FW_Ext_Backups_Task $task
	 */
	public static function __action_log_fail(FW_Ext_Backups_Task $task) {
		WP_CLI::error(
			is_wp_error($task->get_result()) ? $task->get_result()->get_error_message() : 'Unknown'
		);
	}

	/**
	 * @internal
	 * @param FW_Ext_Backups_Task_Collection $collection
	 */
	public static function __action_log_fails(FW_Ext_Backups_Task_Collection $collection) {
		WP_CLI::error('Execution Failed');
	}

	public static function __feedback_hooks($enable) {
		foreach (array(
			'fw:ext:backups:tasks:start' => array(
				'callback' => array(__CLASS__, '__action_log_step'),
			),
			'fw:ext:backups:task:executed' => array(
				'callback' => array(__CLASS__, '__action_log_step'),
			),
			'fw:ext:backups:task:fail' => array(
				'callback' => array(__CLASS__, '__action_log_fail'),
			),
			'fw:ext:backups:tasks:fail' => array(
				'callback' => array(__CLASS__, '__action_log_fails'),
			),
			'fw:ext:backups:tasks:success' => array(
				'callback' => array(__CLASS__, '__action_log_success'),
			),
		) as $hook => $data) {
			$enable
				? add_action($hook, $data['callback'], 10, isset($data['accepted_args']) ? $data['accepted_args'] : 1)
				: remove_action($hook, $data['callback']);
		}
	}

	/**
	 * Full Backup
	 * Will save your uploads, database, themes and plugins
	 */
	public function full($params, $args) {
		self::check();

		self::__feedback_hooks(true);
		self::extension()->tasks()->do_backup(true);
		self::__feedback_hooks(false);
	}

	/**
	 * Content Backup
	 * Will save your uploads and database without private data like users, admin email, etc.
	 */
	public function content($params, $args) {
		self::check();

		self::__feedback_hooks(true);
		self::extension()->tasks()->do_backup(false);
		self::__feedback_hooks(false);
	}

	/**
	 * List Backup Archives
	 */
	public function archives($params, $args) {
		self::check();

		$time_format = get_option('date_format') .' '. get_option('time_format');

		$items = array();
		foreach (self::extension()->get_archives() as $name => $data) {
			$items[] = array(
				'name' => $name,
				'type' => $data['full'] ? 'full' : 'content',
				'time' => get_date_from_gmt( gmdate('Y-m-d H:i:s', $data['time']), $time_format )
			);
		}

		\WP_CLI\Utils\format_items('table', $items, array('name', 'type', 'time'));
	}

	/**
	 * Delete a Backup Archive
	 */
	public function remove($params, $args) {
		self::check();

		if (empty($params[0])) {
			WP_CLI::warning('Archive name is required');
			exit();
		}

		$name = $params[0];
		$archives = self::extension()->get_archives();

		if (!isset($archives[$name])) {
			WP_CLI::error('Archive does not exist');
			exit();
		}

		if (@unlink($archives[$name]['path'])) {
			WP_CLI::success('Archive deleted');
		} else {
			WP_CLI::error('Cannot delete');
		}
	}

	/**
	 * Restore from a Backup Archive
	 */
	public function restore($params, $args) {
		self::check();

		if (empty($params[0])) {
			WP_CLI::warning('Archive name is required');
			exit();
		}

		$name = $params[0];
		$archives = self::extension()->get_archives();

		if (!isset($archives[$name])) {
			WP_CLI::error('Archive does not exist');
			exit();
		}

		$wp_filesystem_credentials = array();

		self::__feedback_hooks(true);
		self::extension()->tasks()->do_restore(
			$archives[$name]['full'],
			$archives[$name]['path'],
			$wp_filesystem_credentials
		);
		self::__feedback_hooks(false);
	}
}

WP_CLI::add_command( 'unyson backup', 'FW_CLI_Command_Backup' );
