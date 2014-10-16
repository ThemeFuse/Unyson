<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Export_Full implements FW_Backup_Interface_Export
{
    public function export(FW_Backup_Interface_Feedback $feedback)
    {
        $db = new FW_Backup_Export_Database();
        $fs = new FW_Backup_Export_File_System();
        $zip_file = sprintf('%s/backup-full-%s.zip', sys_get_temp_dir(), date('Y_m_d-H_i_s'));
	    $tmp_file = array();

        try {
            touch($zip_file);
            $zip = new ZipArchive();
            if ($zip->open($zip_file) !== true) {
                throw new FW_Backup_Exception(__('Could not create .zip file', 'fw'));
            }

            $fs->append_zip($zip, ABSPATH, '', $feedback);
            $zip->addFile($tmp_file[] = $db->export_sql($feedback), 'database.sql');

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
}
