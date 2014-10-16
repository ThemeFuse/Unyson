<?php if (!defined('FW')) die('Forbidden');

/**
 * Class FW_Backup_Service_File_System
 *
 * Generic functionality for working with File System
 */
class FW_Backup_Helper_File_System
{
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

    public function file_list_exclude($base, $exclude = array())
    {
        // normalize $root
        $base = realpath($base);
        $base = str_replace('/', DIRECTORY_SEPARATOR, $base);

        // normalize $exclude
        $tmp = array();
        foreach ($exclude as $value) {
            $tmp[] = str_replace('/', DIRECTORY_SEPARATOR, $value);
        }
        $exclude = $tmp;

        // FIXME not enough permissions to some dirs ( ! ) Warning: scandir(/path/to/dir): failed to open dir: Permission denied
        $file_list = array();
        $file_size = array();
        foreach ($this->file_list($base, true) as $file) {

            // do not include $root into archive
            if ($file == $base) {
                continue;
            }

            // exclude files starting with prefix listed in $exclude
            foreach ($exclude as $value) {
                if (strpos($file, $value) === 0) {
                    continue 2;
                }
            }

            // standard error message lacks $file
            if (($size = filesize($file)) === false) {
                trigger_error("\n\n\n$file\n\n\n");
            }
            else {
                $file_list[] = $file;
                $file_size[] = $size;
            }

        }

        return array($file_list, $file_size);
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

    public function append_zip(ZipArchive $zip, $file_list, $root, $local_prefix, FW_Backup_Interface_Feedback $feedback)
    {
        $feedback->set_task(__('Adding files to archive...', 'fw'), count($file_list));

        foreach ($file_list as $index => $file) {
	        $local_name = substr($file, strlen($root));
	        // $root = /var/www
	        // $file = /var/www/index.php
	        // $local_name = /index.php
	        $local_name = ltrim($local_name, DIRECTORY_SEPARATOR);
	        $local_name = $local_prefix . $local_name;
	        // fix for Windows
	        $local_name = str_replace('\\', '/', $local_name);
            if (is_dir($file)) {
                $zip->addEmptyDir($local_name);
            }
            else {
                if (is_readable($file)) {
                    $zip->addFile($file, $local_name);
                }
                else {
                    // $log->append("error: could not read file $file");
                }
            }
            $feedback->set_progress($index);
        }
    }





	public function map($path)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		$s = new FW_Backup_Helper_String();
		return $s->replace_prefix($path, ABSPATH, $wp_filesystem->abspath());
	}

	public function trash($file)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		if (!$wp_filesystem->exists($file)) {
			return false;
		}

		$timestamp = date('Y_m_d-H_i_s', current_time('timestamp'));
		$t = "$file.$timestamp";

		// instead of removing original dir, rename it
		$this->move($file, $t);

		return $t;
	}

	public function find($path)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		$ret = array();

		if ($wp_filesystem->is_dir($path)) {
			$rtrim = rtrim($path, '/');
			// Include $path into result
			foreach ($wp_filesystem->dirlist("$path/..") as $key => $value) {
				if ($key == basename($rtrim)) {
					$ret[$rtrim] = $value;
					unset($ret[$rtrim]['files']);
					break;
				}
			}
			$this->find_to_array($wp_filesystem->dirlist($path, true, true), $ret, $rtrim);
		}
		else {
			$this->find_to_array($wp_filesystem->dirlist($path, true, true), $ret, dirname($path));
		}

		return $ret;
	}

	private function find_to_array($dir_list, &$ret, $dir)
	{
		foreach ($dir_list as $key => $value) {
			$path = "$dir/$key";
			$ret[$path] = $value;
			if (isset($value['files'])) {
				unset($ret[$path]['files']);
				$this->find_to_array($value['files'], $ret, $path);
			}
		}
	}

	public function copy($source, $destination)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		if ($wp_filesystem->exists($destination)) {
			throw new FW_Backup_Exception("File already exists [$destination]");
		}

		foreach ($this->find($source) as $file => $attr) {

			$copy_to = $destination . substr($file, strlen($source));

			// BUG **isdir** attribute is present on FTP but absent on direct access
			if ((isset($attr['isdir']) && $attr['isdir']) || $wp_filesystem->is_dir($file)) {
				$this->mkdir($copy_to);
			}
			else {
				if (!$wp_filesystem->copy($file, $copy_to)) {
					throw new FW_Backup_Exception("copy failed [$file, $copy_to]");
				}
			}
		}
	}

	public function replace($source, $replacement)
	{
		$t = $this->trash($source);
		$this->copy($replacement, $source);
		return $t;
	}

	public function mkdir($path)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		if (!$wp_filesystem->mkdir($path)) {
			throw new FW_Backup_Exception("mkdir failed [$path]");
		}
	}

	public function move($source, $destination)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		if (!$wp_filesystem->exists($source)) {
			throw new FW_Backup_Exception("File does not exists [$source]");
		}

		if ($wp_filesystem->exists($destination)) {
			throw new FW_Backup_Exception("File already exists [$destination]");
		}

		if (!$wp_filesystem->move($source, $destination)) {
			throw new FW_Backup_Exception("Could not rename [$source] to [$destination]");
		}
	}

	public function move_existing($source, $destination)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		if ($wp_filesystem->exists($source)) {
			$this->move($source, $destination);
		}
	}

	public function rmdir($target)
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		if (!$wp_filesystem->rmdir($target, true)) {
			throw new FW_Backup_Exception("Could not delete directory: $target");
		}
	}

	public function check_permissions()
	{
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		$d = $wp_filesystem->abspath() . uniqid('check_permissions_');
		if ($wp_filesystem->mkdir($d)) {
			$wp_filesystem->rmdir($d);
			return true;
		}

		throw new FW_Backup_Exception('File System: Not Enough Permissions');
	}
}
