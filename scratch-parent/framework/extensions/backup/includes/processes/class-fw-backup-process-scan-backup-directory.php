<?php if (!defined('FW')) die('Forbidden');

/**
 * Scan local directory and imports new backups into database.
 *
 * If someone put archive into uploads/backup directory then this
 * process will registers them in database to be able to use them
 * for restoration.
 */
class FW_Backup_Process_Scan_Backup_Directory
{
	public function run()
	{
		// Obtaining list of backups...

		$walker = new FW_Backup_Walker_Local_Backup_File();
		$this->backup()->post_type()->foreach_post(array($walker, 'walk'), array('post_status' => true));

		$backup_file_from_db = array();
		$backup_file_from_dir = array();

		// fix for Windows
		foreach ($walker->get_result() as $path) {
			$backup_file_from_db[] = str_replace('\\', '/', $path);
		}

		// fix for Windows
		$d = $this->backup()->get_backup_dir();
		$d = str_replace('\\', '/', $d);

		// Scanning local backup directory...

		$a = array();
		if (is_dir($d)) {
			foreach (array_diff(scandir($d), array('.', '..', 'index.php')) as $filename) {
				$a[] = "$d/$filename";
			}
			$a = array_filter($a, 'is_file');
			$a = array_filter($a, 'is_readable');
		}

		// Looking for backup archives...

		$r = new FW_Backup_Reflection_Backup_Archive();
		foreach ($a as $file) {
			$file_contents = $r->inspect_file($file);
			if ($file_contents['db'] || $file_contents['fs']) {
				$backup_file_from_dir[$file] = $file_contents;
			}
		}

		// Registering backup archives...

		$storage_id = 'backup-storage-local';
		$backup_file_new = array_diff_key($backup_file_from_dir, array_flip($backup_file_from_db));

		foreach ($backup_file_new as $file => $file_contents) {

			if ($file_contents['db'] && $file_contents['fs']) {
				$cron_job = $this->backup()->cron()->get_cron_job('cron_full');
			}
			else {
				$cron_job = $this->backup()->cron()->get_cron_job('cron_database');
			}

			if (preg_match('/(\d\d\d\d)_(\d\d)_(\d\d)-(\d\d)_(\d\d)_(\d\d)/', basename(realpath($file)), $m)) {
				$file_time = strtotime($m[1] . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4] . ':' . $m[5] . ':' . $m[6]);
			}
			else {
				$file_time = filemtime($file);
			}

			$post_id = $this->backup()->post_type()->insert();

			$backup_info = new FW_Backup_Info();
			$backup_info->set_cron_job($cron_job->get_id());
			$backup_info->set_storage($storage_id);
			$backup_info->set_storage_file(new FW_Backup_Storage_File_Local(realpath($file)));
			$backup_info->set_storage_file_time($file_time);
			$backup_info->set_storage_file_contents($file_contents);
			$backup_info->set_imported_at(time());
			$backup_info->set_completed_at(time());
			$backup_info->set_finished_at(time());
			$this->backup()->update_backup_info($post_id, $backup_info);

			$post = get_post($post_id);
			$post->post_title = $cron_job->get_title();
			$post->post_status = 'publish';
			wp_update_post($post);

		}

		if (empty($backup_file_new)) {
			return false;
		}

		wp_cache_flush();
		return sprintf(__('%d new backups was found', 'fw'), count($backup_file_new));
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
