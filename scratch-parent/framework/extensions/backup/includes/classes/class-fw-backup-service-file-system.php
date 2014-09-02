<?php if (!defined('FW')) die('Forbidden');

/**
 * Class FW_Backup_Service_File_System
 *
 * Generic functionality for working with File System
 */
class FW_Backup_Service_File_System
{
	public function check_permissions($dir)
	{
		if (!@mkdir($dir)) {
			throw new FW_Backup_Exception(sprintf(__('Cannot create directory %s: not enough permissions?', 'fw'), $dir));
		}
		rmdir($dir);
	}

	// FIXME Does not handle recursive symlinks
	public function file_list($base, $follow_symlink = false)
	{
		$ret = array($base);

		if (is_dir($base) && ($follow_symlink || !is_link($base))) {
			$dir = scandir($base);
			if ($dir !== false) {
				foreach ($dir as $file) {

					if ($file == '.' || $file == '..') {
						continue;
					}

					# searching for 225609 files

					# $ time find /path/to/dir
					# real	0m2.199s
					# user	0m0.268s
					# sys	0m0.592s

					# $ time php file_list_function.php /path/to/dir
					# real	0m25.207s
					# user	0m23.077s
					# sys	0m1.356s
					# --------------------------------------------------
					# $ret = array_merge($ret, file_list("$base/$file"));
					# --------------------------------------------------

					# $ time php file_list_function.php /path/to/dir
					# real	0m3.549s
					# user	0m2.252s
					# sys	0m1.284s
					foreach ($this->file_list($base . DIRECTORY_SEPARATOR . $file, $follow_symlink) as $a) {
						$ret[] = $a;
					}

				}
			}
		}

		return $ret;
	}

	// http://stackoverflow.com/a/2510459
	public function format_bytes($bytes, $precision = 2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		# Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		# $bytes /= (1 << (10 * $pow));

		return round($bytes, $precision).' '.$units[$pow];
	}
}
