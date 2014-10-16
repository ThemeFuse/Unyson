<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Format
{
	public function format_date_time_gmt($timestamp_gmt)
	{
		$f = get_option('date_format') . ' ' . get_option('time_format');

		$s = gmdate('Y-m-d H:i:s', $timestamp_gmt);
		$s = get_date_from_gmt($s, $f);

		return $s;
	}
}
