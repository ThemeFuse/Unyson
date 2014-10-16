<?php if (!defined('FW')) die('Forbidden');

/**
 * -----------------------------------------------------
 *                      wp-config.php
 * -----------------------------------------------------
 *  http://site1.example.com | http://site2.example.com
 * -----------------------------------------------------
 * prefix = site1_           | prefix = site2_
 * db_host = localhost       | db_host = localhost
 * db_user = example         | db_user = example
 * db_password = example     | db_password = example
 * ----------------------------------------------------
 *
 * copy backup-site2.zip to http://site1.example.com
 * 1) backup-site2.zip will be imported
 *
 * on restore:
 * 1) all files will be replaced with files from backup-site2.zip
 * 2) database.sql will be imported and all site2_ prefixed will be changed to site1_
 * 3) wp-config.php should be preserved, otherwise prefix will be site2_ instead of site1_
 */
class FW_Backup_Process_Restore
{
	public function run($post_id)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		set_time_limit(0);

		$fs = new FW_Backup_Helper_File_System();
		$db = new FW_Backup_Helper_Database();

		$backup_info = $this->backup()->get_backup_info($post_id);
		if (!$backup_info || !$backup_info->is_completed()) {
			throw new FW_Backup_Exception('Cannot restore from incomplete backup');
		}

		$storage = $this->backup()->get_storage($backup_info->get_storage(), $backup_info->get_cron_job());

		// Ensure that storage layer is workable (e.g. was configured properly)
		$storage->ping(new FW_Backup_Feedback_Void());

		if ($backup_info->has_fs()) {
			$this->request_filesystem_credentials();
		}

		// This forces *Restore* page to be opened event if
		// request_filesystem_credentials is not required. In the
		// latter case JavaScript will submit the page automatically
		// which opens up a *Restore in Progress* popup.
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new FW_Backup_Exception_Method_Not_Allowed();
		}

		if ($backup_info->has_fs()) {

			if ($wp_filesystem->abspath() == '/') {
				throw new FW_Backup_Exception('WordPress was located at root directory (/). Restoration for this scenario was not implemented.');
			}

			$fs->check_permissions();
		}

		if ($backup_info->has_db()) {
			$db->check_permissions();
		}

		// 2) Do restore

		try {
			$f = new FW_Backup_Feedback_Void();

			$zip_file = $storage->fetch($backup_info->get_storage_file(), $f);

			// Try to open it as .zip archive
			$zip = new ZipArchive();
			if ($zip->open($zip_file) !== true) {
				throw new FW_Backup_Exception('Cannot open .zip file');
			}

			// Restore files (should come before database: rename wordpress dir can fail)
			if ($backup_info->has_fs()) {

				$wp = rtrim($wp_filesystem->abspath(), '/');
				$wp_new = rtrim(dirname($wp), '/') . '/' . uniqid();
				$wp_backup = $wp . '-' . date('Y_m_d-H_i_s', current_time('timestamp'));
				$backup_rel = substr($this->backup()->get_backup_dir(), strlen(ABSPATH));

				// Extract archive into temporary location
				unzip_file($zip_file, $wp_new);

				// Get rid of database.sql file, if any
				if ($wp_filesystem->exists("$wp_new/database.sql")) {
					$wp_filesystem->delete("$wp_new/database.sql");
				}

				// In imported backup files wp-config.php can contain database
				// credentials which cannot work on this host. Also, database prefix
				// can be different from the value in original wp-config.php
				if ($backup_info->is_imported()) {
					$wp_filesystem->copy("$wp/wp-config.php", "$wp_new/wp-config.php", true);
				}

				// Replace WordPress dir by version from backup
				$move = true
					&& $wp_filesystem->move($wp, $wp_backup)
					&& $wp_filesystem->move($wp_new, $wp)
					&& $wp_filesystem->move("$wp_backup/$backup_rel", "$wp/$backup_rel");
				if (!$move) {
					throw new FW_Backup_Exception('Replacing wordpress dir by version from backup failed');
				}
			}

			// Restore database
			if ($backup_info->has_db()) {
				$fp_db = $zip->getStream('database.sql');
				$db->import_fp($fp_db, false, $backup_info->is_imported(), false);
				fclose($fp_db);
			}
		}
		catch (FW_Backup_Exception $exception) {
		}

		unset($zip);
		if (isset($zip_file)) {
			unlink($zip_file);
		}

		if (isset($exception)) {
			throw $exception;
		}
	}

	public function request_filesystem_credentials()
	{
		ob_start();
		$credentials = request_filesystem_credentials(fw_current_url(), '', false, false, null);
		$request_filesystem_credentials = ob_get_clean();

		if ($credentials) {
			if (!WP_Filesystem($credentials)) {
				ob_start();
				request_filesystem_credentials(fw_current_url(), '', false, false, null);
				$request_filesystem_credentials = ob_get_clean();
			}
		}

		if ($request_filesystem_credentials) {
			throw new FW_Backup_Exception_Request_File_System_Credentials($request_filesystem_credentials);
		}
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
