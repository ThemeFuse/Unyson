<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Export_File_System
{
    public function append_zip(ZipArchive $zip, $root, $local_prefix, FW_Backup_Interface_Feedback $feedback, $exclude = array())
    {
        /**
         * @var FW_Extension_Backup $backup
         */

        $feedback->set_task(__('Scanning file system...', 'fw'));

        $fs = new FW_Backup_Helper_File_System();
        $backup = fw()->extensions->get('backup');

	    $exclude[] = $backup->get_backup_dir();
        list ($file_list, $file_size) = $fs->file_list_exclude($root, $exclude);

        $feedback->set_task(sprintf(__('%d file(s) found [%s]', 'fw'), count($file_list), $fs->format_bytes(array_sum($file_size))));

        $fs->append_zip($zip, $file_list, $root, $local_prefix, $feedback);
    }
}
