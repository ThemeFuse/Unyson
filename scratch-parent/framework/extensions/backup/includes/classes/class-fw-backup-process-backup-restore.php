<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Process_Backup_Restore
{
	private $wordpress_dir;
	private $backup_rel;
	private $fs;
	private $db;
	private $ie_fs;
	private $ie_db;
	private $ie_settings;
	private $ie_history;
	private $feedback;

	public function __construct($wordpress_dir, $backup_rel, FW_Backup_Service_File_System $fs, FW_Backup_Service_Database $db, FW_Backup_IE_File_System $ie_fs, FW_Backup_Interface_IE $ie_db, FW_Backup_Interface_IE $ie_settings, FW_Backup_Interface_IE $ie_history, FW_Backup_Interface_Feedback $feedback)
	{
		$this->wordpress_dir = $wordpress_dir;
		$this->backup_rel = $backup_rel;
		$this->fs = $fs;
		$this->db = $db;
		$this->ie_fs = $ie_fs;
		$this->ie_db = $ie_db;
		$this->ie_settings = $ie_settings;
		$this->ie_history = $ie_history;
		$this->feedback = $feedback;
	}

	public function set_ie_fs(FW_Backup_IE_File_System $ie_fs)
	{
		$this->ie_fs = $ie_fs;
	}

	public function set_ie_db(FW_Backup_Interface_IE $ie_db)
	{
		$this->ie_db = $ie_db;
	}

	public function backup(FW_Backup_Interface_Storage $storage)
	{
		/**
		 * @var $backup_file
		 */

		$tmp = array(
			$tmp_zip = tempnam(sys_get_temp_dir(), 'backup'),
			$tmp_db = tempnam(sys_get_temp_dir(), 'database'),
		);
		$fp = array();

		try {

			$zip = new ZipArchive();
			if (($errno = $zip->open($tmp_zip)) !== true) {
				throw new FW_Backup_Exception(sprintf(__('$zip->open() failed: %d', 'fw'), $errno));
			}

			// Export File System
			$this->ie_fs->export($zip);

			// Export Database
			$fp[] = $fp_db = fopen($tmp_db, 'w');
			$this->ie_db->export($fp_db);
			fclose($fp_db);
			array_pop($fp);

			$zip->addFile($tmp_db, 'database.sql');

			// The most lengthy process...
			$this->feedback->set_task(__('Compressing...', 'fw'));
			$zip->close();

			// Save backup file somewhere (add .zip suffix to the file)
			$tmp[] = $tmp_zip_zip = "$tmp_zip.zip";
			rename($tmp_zip, $tmp_zip_zip);
			$backup_file = $storage->move($tmp_zip_zip);

		}
		catch (Exception $exception) {
		}

		// Clean up
		array_map('fclose', $fp);
		array_map('unlink', array_filter($tmp, 'file_exists'));

		if (isset($exception)) {
			throw $exception;
		}

		return $backup_file;
	}

	public function restore(FW_Backup_Interface_Storage $storage, FW_Backup_Interface_File $backup_file)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		if ($wp_filesystem->abspath() == '/') {
			throw new FW_Backup_Exception('WordPress was located at root directory (/). Restoration for this scenario was not implemented.');
		}

		$wp = rtrim($wp_filesystem->abspath(), '/');
		$wp_new = rtrim(dirname($wp), '/') . '/' . uniqid();
		$wp_backup = $wp . '-' . date('Y-m-d_H-i_s');
		$backup_rel = str_replace(DIRECTORY_SEPARATOR, '/', $this->backup_rel);

		$tmp = array();
		$func = array();

		try {
			// Fetch archive
			$tmp[] = $tmp_zip = $storage->fetch($backup_file);
			$zip = new ZipArchive();
			if (($errno = $zip->open($tmp_zip)) !== true) {
				throw new FW_Backup_Exception(sprintf(__('$zip->open() failed: %d'), $errno));
			}
			$func[] = array($zip, 'close');

			// Restore files (should come before database: rename wordpress dir can fail)
			if (count(array_filter(array('index.php', 'wp-config.php'), array($zip, 'statName'))) > 0) {

				// Extract archive into temporary location
				unzip_file($tmp_zip, $wp_new);

				// Get rid of database.sql file, if any
				if ($wp_filesystem->exists("$wp_new/database.sql")) {
					$wp_filesystem->delete("$wp_new/database.sql");
				}

				// Replace wordpress dir by version from backup
				$move = true
					&& $wp_filesystem->move($wp, $wp_backup)
					&& $wp_filesystem->move($wp_new, $wp)
					&& $wp_filesystem->move("$wp_backup/$backup_rel", "$wp/$backup_rel");
				if (!$move) {
					throw new FW_Backup_Exception('Replacing wordpress dir by version from backup failed');
				}
			}

			// Restore database
			if ($zip->statName('database.sql') !== false) {
				$fp = array(
					$fp_settings = tmpfile(),
					$fp_history = tmpfile(),
				);

				// Preserve Backup History and Backup Settings across restore
				$this->ie_history->export($fp_history);
				$this->ie_settings->export($fp_settings);

				// .zip streams does not support seeking
				array_map('rewind', $fp);
				$fp[] = $fp_db = $zip->getStream('database.sql');

				$this->ie_db->import($fp_db);
				$this->ie_history->import($fp_history);
				$this->ie_settings->import($fp_settings);

				array_map('fclose', $fp);
			}
		}
		catch (Exception $exception) {

			// Delete directory .zip was extracted into
			if ($wp_filesystem->exists($wp_new)) {
				$wp_filesystem->delete($wp_new, true);
			}

			// If backup copy was made
			if ($wp_filesystem->exists($wp_backup)) {
				// 3. Move backup_dir back
				if ($wp_filesystem->exists("$wp/$backup_rel")) {
					$wp_filesystem->move("$wp/$backup_rel", "$wp_backup/$backup_rel");
				}
				// 2. Delete extracted version
				if ($wp_filesystem->exists($wp)) {
					$wp_filesystem->delete($wp, true);
				}
				// 1. Move backup'ed directory backup
				$wp_filesystem->move($wp_backup, $wp);
			}
		}

		// on WAMP 2.5 with PHP 5.5.12 and Apache 2.4.9 this makes php crash
		// array_map('call_user_func', $func);
		foreach ($func as $callable) {
			call_user_func($callable);
		}

		array_map('unlink', array_filter($tmp, 'file_exists'));

		if (isset($exception)) {
			throw $exception;
		}
	}

	private function restore_direct(FW_Backup_Interface_Storage $storage, FW_Backup_Interface_File $backup_file)
	{
		$wp = $this->wordpress_dir;
		$wp_new = dirname($this->wordpress_dir) . DIRECTORY_SEPARATOR . uniqid();
		$wp_backup = $this->wordpress_dir . '-' . date('Y-m-d_H-i_s');
		$backup_rel = $this->backup_rel;

		$tmp = array();
		$func = array();

		try {
			// Fetch archive
			$tmp[] = $tmp_zip = $storage->fetch($backup_file);
			$zip = new ZipArchive();
			if (($errno = $zip->open($tmp_zip)) !== true) {
				throw new FW_Backup_Exception(sprintf(__('$zip->open() failed: %d'), $errno));
			}
			$func[] = array($zip, 'close');

			// Restore files (should come before database: rename wordpress dir can fail)
			if (count(array_filter(array('index.php', 'wp-config.php'), array($zip, 'statName'))) > 0) {
				$this->extract($zip, $wp_new);
				// Get rid of database.sql file, if any
				array_map('unlink', array_filter(array("$wp_new/database.sql"), 'file_exists'));
				$this->mv($wp, $wp_backup);
				$this->mv($wp_new, $wp);
				$this->mv("$wp_backup/$backup_rel", "$wp/$backup_rel");
			}

			// Restore database
			if ($zip->statName('database.sql') !== false) {
				$fp = array(
					$fp_settings = tmpfile(),
					$fp_history = tmpfile(),
				);

				// Preserve Backup History and Backup Settings across restore
				$this->ie_history->export($fp_history);
				$this->ie_settings->export($fp_settings);

				// .zip streams does not support seeking
				array_map('rewind', $fp);
				$fp[] = $fp_db = $zip->getStream('database.sql');

				$this->ie_db->import($fp_db);
				$this->ie_history->import($fp_history);
				$this->ie_settings->import($fp_settings);

				array_map('fclose', $fp);
			}
		}
		catch (Exception $exception) {
			// get rid of directory .zip was extracted into
			if (file_exists($wp_new)) {
				$this->rm($wp_new);
			}
			// if backup copy was made
			if (file_exists($wp_backup)) {
				// even backup directory was moved to restored dir
				if (file_exists("$wp/$backup_rel")) {
					$this->mv("$wp/$backup_rel", "$wp_backup/$backup_rel");
				}
				if (file_exists($wp)) {
					$this->rm($wp);
				}
				$this->mv($wp_backup, $wp);
			}
		}

		// on WAMP 2.5 with PHP 5.5.12 and Apache 2.4.9 this makes php crash
		// array_map('call_user_func', $func);
		foreach ($func as $callable) {
			call_user_func($callable);
		}

		array_map('unlink', array_filter($tmp, 'file_exists'));

		if (isset($exception)) {
			throw $exception;
		}
	}

	public function check_permissions_fs()
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		$wp_new = rtrim(dirname($wp_filesystem->abspath()), '/') . '/' . uniqid();

		if (!$wp_filesystem->mkdir($wp_new)) {
			throw new FW_Backup_Exception(sprintf(__('mkdir(%s) failed', 'fw'), $wp_new));
		}

		$wp_filesystem->rmdir($wp_new);
	}

	private function check_permissions_fs_direct()
	{
		$wp_new = dirname($this->wordpress_dir) . DIRECTORY_SEPARATOR . uniqid();

		if (!@mkdir($wp_new)) {
			$error = error_get_last();
			throw new FW_Backup_Exception(sprintf(__('mkdir(%s) failed with message "%s"', 'fw'), $wp_new, $error['message']));
		}

		rmdir($wp_new);
	}

	public function check_permissions_db()
	{
		$privileges = $this->db->show_privileges();

		if (!isset($privileges['DROP']) || !in_array('TABLES', $privileges['DROP'])) {
			throw new FW_Backup_Exception(__('MySQL lacks DROP TABLES privilege', 'fw'));
		}

		if (!isset($privileges['CREATE']) || !in_array('TABLES', $privileges['CREATE'])) {
			throw new FW_Backup_Exception(__('MySQL lacks CREATE TABLE privilege', 'fw'));
		}
	}

	private function extract(ZipArchive $zip, $dir)
	{
		if (!@$zip->extractTo($dir)) {
			throw new FW_Backup_Exception(sprintf(__("\$zip->extractTo(%s) failed. Not enough permissions?", 'fw'), $dir));
		}
	}

	private function mv($from, $to)
	{
		if (!@rename($from, $to)) {
			$error = error_get_last();
			throw new FW_Backup_Exception(sprintf(__('rename(%s, %s) failed with message "%s"', 'fw'), $from, $to, $error['message']));
		}
	}

	private function rm($base)
	{
		foreach (array_reverse($this->fs->file_list($base)) as $file) {
			if (is_dir($file)) {
				rmdir($file);
			}
			else {
				unlink($file);
			}
		}
	}
}
