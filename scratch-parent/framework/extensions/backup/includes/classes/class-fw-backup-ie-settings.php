<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_IE_Settings implements FW_Backup_Interface_IE
{
	private $name;

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function import($fp)
	{
		$json = '';
		while ($line = fgets($fp)) {
			$json .= $line;
		}

		fw_set_db_extension_data('backup', $this->name, json_decode($json, true));
	}

	public function export($fp)
	{
		fwrite($fp, json_encode(fw_get_db_extension_data('backup', $this->name)));
	}
}
