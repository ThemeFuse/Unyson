<?php if (!defined('FW')) die('Forbidden');

/**
 * Interface FW_Backup_Interface_Feedback
 *
 * (x) Full Backup: Querying database...
 * (x) Full Backup: Scanning file system...
 * (x) Full Backup: Compressing...
 * (x) Full Backup: Sending to Dropbox as xxxxxx.zip: 2.5MB sent at 1MB/sec [5%]
 * (x) Full Backup: Cancelling...
 */
interface FW_Backup_Interface_Feedback
{
	public function set_task($task_title, $progress_total = 0);
	public function set_task_progress($progress_complete, $progress_title = null);

	public function get_task_title();
	public function get_task_progress();
	public function get_task_progress_title();
}
