<?php if (!defined('FW')) die('Forbidden');

interface FW_Backup_Interface_Cron
{
	public function is_active();

	/**
	 * Whether to display Create *** Now button
	 *
	 * @return bool
	 */
	public function has_backup_now();

	public function get_id();
	public function get_title();
	public function get_schedule_title();
	public function get_next_at_title();

	/**
	 * @return FW_Backup_Interface_Storage
	 */
	public function get_storage();
	public function get_storage_id();

	// A list of what a backup should include e.g. array('db', 'fs')
	public function get_backup_contents();
}
