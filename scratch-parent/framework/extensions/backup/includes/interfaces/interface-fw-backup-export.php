<?php if (!defined('FW')) die('Forbidden');

interface FW_Backup_Interface_Export
{
	public function export(FW_Backup_Interface_Feedback $feedback);
}
