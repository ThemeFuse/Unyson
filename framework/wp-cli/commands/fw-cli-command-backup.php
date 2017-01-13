<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command_Backup extends FW_CLI_Command {

	/**
	 * Check user environment
	 */
	protected function check() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			WP_CLI::error("Backups works only on UNIX");
			exit();
		}

		if (!$this->command_exist('zip')) {
			WP_CLI::error("Unknown command zip");
			exit();
		}

		if (!function_exists('shell_exec')) {
			WP_CLI::error("Unknown php function shell_exec");
		}
	}

	/**
	 * Content Backup
	 * Will save your uploads and database without private data like users, admin email, etc.
	 */
	public function content($params, $args) {
		$this->check();

		$wp_uplaod = wp_upload_dir();
		$basedir = isset($wp_uplaod['basedir']) ? $wp_uplaod['basedir'] : false;
		$backup_folder = $this->create_if_not_exists($basedir . '/fw-backup/');

		if ($basedir && $backup_folder) {
			$wp_contents = dirname($basedir);
			$zip_name = $backup_folder . 'fw-backup-' . date('Y_m_d_H_i_s') . '.zip';

			// Copy files into tempdir.
			WP_CLI::line('Create temp folder.');
			$temp = $this->create_temp_folder();

			if ( ! $temp ) {
				WP_CLI::error("Can't create the temp directory.");
			}

			WP_CLI::line('Export database.');

			// Database dump.
			$args = array(
				'dir' => $temp,
				'full' => false,
			);

			// Execute database backup task.
			fw_ext('backups')->tasks()->_get_task_type('db-export')->execute($args);

			WP_CLI::line('Copy files.');

			// Create directories structure.
			shell_exec("cd {$basedir}; find . -not -path \*fw-backup\* -type d -exec mkdir -p -- {$temp}/f/{} \;");
			// Copy files.
			shell_exec("cd {$basedir}; find . -not -path \*fw-backup\* -type f -exec cp -- {} {$temp}/f/{} \;");

			// Create zip arhive.
			WP_CLI::line('Create zip arhive.');
			shell_exec("cd {$temp}; zip -r {$zip_name} . -x *fw-backup*;");

			// Remove temp folder.
			shell_exec("rm -rf {$temp}");
		}
	}

	/**
	 * Create new temp directory in system temp folder.
	 *
	 * @return string|bool Path to temp directory.
	 */
	protected function create_temp_folder() {
		$folder = md5( time() + rand(1, 9999999) );
		$temp = sys_get_temp_dir() . '/unyson-' . $folder;
		$f_folder = $temp . '/' . 'f';

		if ( ! mkdir( $temp ) ) {
			return false;
		}

		if ( ! mkdir( $f_folder ) ) {
			return false;
		}

		return $temp;
	}

	/**
	 * @return string
	 */
	protected function create_if_not_exists($path) {
		if (!file_exists($path)) {
			mkdir($path);
		}

		return $path;
	}

}

WP_CLI::add_command( 'unyson backup', 'FW_CLI_Command_Backup' );