<?php if (!defined('FW')) die('Forbidden');

interface FW_Backup_Interface_IE
{
	public function import($fp);
	public function export($fp);
}
