<?php if (!defined('FW')) die('Forbidden');

interface FW_Backup_Interface_Storage
{
    public function get_name();
	public function get_title($context = null);

    public function get_storage_options();
	public function set_storage_options($values);
	// Allows storage layer to exchange auth_code to access_token
	public function before_save_storage_options($values);

	public function ping(FW_Backup_Interface_Feedback $feedback);
	public function move($file, FW_Backup_Interface_Feedback $feedback);
	public function fetch($storage_file, FW_Backup_Interface_Feedback $feedback);
	public function remove($storage_file, FW_Backup_Interface_Feedback $feedback);

	public function download($storage_file);
}
