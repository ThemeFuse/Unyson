<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Export_Demo_Install implements FW_Backup_Interface_Export
{
    public function export(FW_Backup_Interface_Feedback $feedback)
    {
	    /**
	     * @var wpdb $wpdb
	     */

	    global $wpdb;

	    $db = new FW_Backup_Export_Database();
	    $fs = new FW_Backup_Export_File_System();
	    $zip_file = sprintf('%s/backup-demo-install-%s.zip', sys_get_temp_dir(), date('Y_m_d-H_i_s'));
	    $tmp_file = array();

	    try {
		    touch($zip_file);
		    $zip = new ZipArchive();
		    if ($zip->open($zip_file) !== true) {
			    throw new FW_Backup_Exception(__('Could not create .zip file', 'fw'));
		    }

		    $upload_dir = wp_upload_dir();
		    $upload_dir = $upload_dir['basedir'];
			$stylesheet_dir = get_stylesheet_directory();
		    $template_dir = get_template_directory();

		    // Do not put auto-install directory which comes with theme into archive
			$exclude = array();
		    if ($a = $this->backup()->get_auto_install_dir()) {
			    $exclude[] = $a;
		    }

		    $fs->append_zip($zip, $template_dir, basename($template_dir) . '/', $feedback, $exclude);
		    if ($stylesheet_dir != $template_dir) {
			    $fs->append_zip($zip, $stylesheet_dir, basename($stylesheet_dir) . '/', $feedback, $exclude);
		    }
		    $fs->append_zip($zip, $upload_dir, basename($template_dir) . '/auto-install/uploads/', $feedback);

		    $options_where = "WHERE option_name NOT LIKE 'fw_backup.%%' AND option_name NOT IN ('ftp_credentials', 'mailserver_url', 'mailserver_login', 'mailserver_pass', 'mailserver_port', 'admin_email')";
		    $exclude_table = array($wpdb->users);
		    $zip->addFile($tmp_file[] = $db->export_sql($feedback, $options_where, $exclude_table), basename($template_dir) . '/auto-install/database.sql');

		    $feedback->set_task(__('Compressing files...', 'fw'));
		    $zip->close();

	    }
	    catch (FW_Backup_Exception $exception) {
		    unset($zip);
		    unlink($zip_file);
	    }

	    array_map('unlink', $tmp_file);

	    if (isset($exception)) {
		    throw $exception;
	    }

	    return $zip_file;
    }

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
