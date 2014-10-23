<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Exception_Request_File_System_Credentials extends Exception
{
	private $html;

	public function __construct($html)
	{
		parent::__construct(__('File System Credentials Required', 'fw'));

		$this->html = $html;
	}

	public function get_html()
	{
		return $this->html;
	}
}
