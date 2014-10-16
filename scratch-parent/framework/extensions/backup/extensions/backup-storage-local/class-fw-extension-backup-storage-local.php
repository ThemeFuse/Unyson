<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Backup_Storage_Local extends FW_Extension_Backup_Storage
{
    public function get_title($context = null)
    {
        return ($context == 'on') ? __('Locally', 'fw') : __('Local', 'fw');
    }

	public function download($storage_file)
	{
		if (!$storage_file instanceof FW_Backup_Storage_File_Local) {
			throw new FW_Backup_Exception('$backup_file should be of class FW_Backup_File_Local');
		}

		$file = $storage_file->get_path();

		if (!is_readable($file)) {
			throw new FW_Backup_Exception("Cannot read file $file");
		}

		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="'.addslashes(basename($file)).'"');

		readfile($file);
		exit;

		// Direct links to uploads/backup dir is forbidden for security reasons.
		// wp_redirect(site_url(str_replace(DIRECTORY_SEPARATOR, '/', substr($storage_file->get_path(), strlen(realpath(ABSPATH))))));
		// exit;
	}

    public function ping(FW_Backup_Interface_Feedback $feedback)
    {
        $this->get_backup_dir();
    }

    public function move($file, FW_Backup_Interface_Feedback $feedback, $name_prefix = false)
    {
	    if (!$name_prefix) {
		    $name_prefix = 'backup';
	    }

		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$date = date('Y_m_d-H_i_s', filemtime($file));

	    // Make name guessing a little bit harder
	    $random = bin2hex(fw_secure_rand(6));

	    $target = $this->get_backup_dir() . DIRECTORY_SEPARATOR . "$name_prefix-$date-$random.$ext";

		$feedback->set_task(sprintf(__('Storing as %s', 'fw'), $target));

		if (!@rename($file, $target)) {
			$error = error_get_last();
			throw new FW_Backup_Exception(sprintf(__('rename(%s, %s) failed with message "%s"', 'fw'), $file, $target, $error['message']));
		}

		return new FW_Backup_Storage_File_Local(realpath($target));
    }

    public function fetch($storage_file, FW_Backup_Interface_Feedback $feedback)
    {
        if (!$storage_file instanceof FW_Backup_Storage_File_Local) {
            throw new FW_Backup_Exception('$backup_file should be of class FW_Backup_File_Local');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'backup');
        if (!@copy($storage_file->get_path(), $tmp)) {
            $error = error_get_last();
            throw new FW_Backup_Exception(sprintf(__('copy(%s, %s) failed with message "%s"', 'fw'), $storage_file->get_path(), $tmp, $error['message']));
        }

        return $tmp;
    }

    public function remove($storage_file, FW_Backup_Interface_Feedback $feedback)
    {
        if (!$storage_file instanceof FW_Backup_Storage_File_Local) {
            throw new FW_Backup_Exception('$backup_file should be of class FW_Backup_File_Local');
        }

        if (!@unlink($storage_file->get_path())) {
            $error = error_get_last();
            throw new FW_Backup_Exception(sprintf('unlink(%s) failed with message "%s"', $storage_file->get_path(), $error['message']));
        }
    }

    private function get_backup_dir()
    {
        $backup_dir = $this->backup()->get_backup_dir();

	    if (!file_exists($backup_dir)) {
		    if (! @wp_mkdir_p($backup_dir, 0777, true)) {
			    throw new FW_Backup_Exception(__('Cannot create local wp-content/uploads/backup directory. Not enough permissions?', 'fw'));
		    }
	    }

	    if (!file_exists("$backup_dir/index.php")) {
		    $index = <<< EOF
<?php

header('HTTP/1.0 403 Forbidden');
die('<h1>Forbidden</h1>');

EOF;
		    if (@file_put_contents("$backup_dir/index.php", $index) === false) {
			    throw new FW_Backup_Exception(__('Cannot create local wp-content/uploads/backup/index.php file. Not enough permissions?', 'fw'));
		    }
	    }

	    if (!file_exists("$backup_dir/.htaccess")) {
		    $htaccess = <<< EOF
Deny from all

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule . - [R=404,L]
</IfModule>

EOF;

		    if (@file_put_contents("$backup_dir/.htaccess", $htaccess) === false) {
			    throw new FW_Backup_Exception(__('Cannot create local wp-content/uploads/backup/.htaccess file. Not enough permissions?', 'fw'));
		    }
	    }

	    return $backup_dir;
    }
}
