<?php if (!defined('FW')) die('Forbidden');

/**
 * NOTE all symbolic links are replaced by contents
 */
class FW_Backup_IE_File_System // NOTE This class does not implements FW_Backup_Interface_IE
{
	private $wordpress_dir;
	private $backup_dir;
	private $fs;
	private $feedback;

	public function __construct($wordpress_dir, $backup_dir, FW_Backup_Service_File_System $fs, FW_Backup_Interface_Feedback $feedback)
	{
		$this->wordpress_dir = $wordpress_dir;
		$this->backup_dir = $backup_dir;
		$this->fs = $fs;
		$this->feedback = $feedback;
	}

	public function import(ZipArchive $zip)
	{
		if (is_dir($this->wordpress_dir)) {
			throw new FW_Backup_Exception(sprintf(__('Directory already exists: %s', 'fw'), $this->wordpress_dir));
		}

		$zip->extractTo($this->wordpress_dir);
	}

	public function export(ZipArchive $zip)
	{
		$this->feedback->set_task(__('Scanning file system...', 'fw'));

		// FIXME not enough permissions to some dirs ( ! ) Warning: scandir(/path/to/dir): failed to open dir: Permission denied
		$file_list = array();
		$file_size = array();
		foreach ($this->fs->file_list($this->wordpress_dir, true) as $file) {

			// get rid of $wordpress_dir entry
			if ($file == $this->wordpress_dir) {
				continue;
			}

			// exclude files under $backup_dir
			if (strpos($file, $this->backup_dir) === 0) {
				continue;
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

		$this->feedback->set_task(sprintf(__('%d file(s) found [%s]', 'fw'), count($file_list), $this->fs->format_bytes(array_sum($file_size))));
		$this->feedback->set_task(__('Adding files to archive...', 'fw'), count($file_list));

		foreach ($file_list as $index => $file) {
			$name_in_zip = trim(substr($file, strlen($this->wordpress_dir)), DIRECTORY_SEPARATOR);
			if (is_dir($file)) {
				$zip->addEmptyDir($name_in_zip);
			}
			else {
				if (is_readable($file)) {
					$zip->addFile($file, $name_in_zip);
				}
				else {
					// $log->append("error: could not read file $file");
				}
			}
			$this->feedback->set_task_progress($index);
		}
	}
}
