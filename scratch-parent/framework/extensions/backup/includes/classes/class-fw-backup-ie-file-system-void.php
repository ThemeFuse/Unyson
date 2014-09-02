<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_IE_File_System_Void extends FW_Backup_IE_File_System
{
	public function __construct() {}
	public function import(ZipArchive $zip) {}
	public function export(ZipArchive $zip) {}
}
