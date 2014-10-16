<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Debug
{
	public static function append($message)
	{
		file_put_contents('/home/www/debug.txt', sprintf("[%s]: %s\n", self::timestamp(), $message), FILE_APPEND);
	}

	private static function timestamp()
	{
		list ($msec, $sec) = explode(' ' , microtime());
		return sprintf('%s.%s', date('Y-m-d H:i:s', $sec + get_option('gmt_offset')*60*60), substr($msec, 2, 4));
	}
}
