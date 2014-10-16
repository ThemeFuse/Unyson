<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Storage_File_Local
{
	private $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function get_path()
	{
		return $this->path;
	}
}
