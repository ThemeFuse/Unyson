<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Helper_String
{
	public function replace_prefix($string, $prefix, $replacement)
	{
		if (strpos($string, $prefix) === 0) {
			return $replacement . substr($string, strlen($prefix));
		}

		return $string;
	}
}
