<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_File_Local implements FW_Backup_Interface_File
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

	public function get_download_url()
	{
		return site_url(str_replace(DIRECTORY_SEPARATOR, '/', substr($this->path, strlen(realpath(ABSPATH)))));
	}
}
