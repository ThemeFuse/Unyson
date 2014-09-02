<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Storage_Local implements FW_Backup_Interface_Storage
{
	private $backup;
	private $feedback;

	public function __construct(FW_Extension_Backup $backup, FW_Backup_Interface_Feedback $feedback)
	{
		$this->backup = $backup;
		$this->feedback = $feedback;
	}

	// FW_Backup_Interface_Storage

	public function get_title($context = null)
	{
		return ($context == 'on') ? __('Locally', 'fw') : __('Local', 'fw');
	}

	public function ping()
	{
		$this->get_backup_dir();
	}

	public function move($file)
	{
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$date = date('Y-m-d_H-i-s');
		$target = $this->get_backup_dir() . DIRECTORY_SEPARATOR . "backup-$date.$ext";

		$this->feedback->set_task(sprintf(__('Storing as %s', 'fw'), $target));

		if (!@rename($file, $target)) {
			$error = error_get_last();
			throw new FW_Backup_Exception(sprintf(__('rename(%s, %s) failed with message "%s"', 'fw'), $file, $target, $error['message']));
		}

		return new FW_Backup_File_Local(realpath($target));
	}

	public function fetch(FW_Backup_Interface_File $backup_file)
	{
		if (!$backup_file instanceof FW_Backup_File_Local) {
			throw new FW_Backup_Exception('$backup_file should be of class FW_Backup_File_Local');
		}

		$tmp = tempnam(sys_get_temp_dir(), 'backup');
		if (!@copy($backup_file->get_path(), $tmp)) {
			$error = error_get_last();
			throw new FW_Backup_Exception(sprintf(__('copy(%s, %s) failed with message "%s"', 'fw'), $backup_file->get_path(), $tmp, $error['message']));
		}

		return $tmp;
	}

	public function remove(FW_Backup_Interface_file $backup_file)
	{
		if (!$backup_file instanceof FW_Backup_File_Local) {
			throw new FW_Backup_Exception('$backup_file should be of class FW_Backup_File_Local');
		}

		if (!@unlink($backup_file->get_path())) {
			$error = error_get_last();
			throw new FW_Backup_Exception(sprintf('unlink(%s) failed with message "%s"', $backup_file->get_path(), $error['message']));
		}
	}

	private function get_backup_dir()
	{
		$backup_dir = $this->backup->get_backup_dir(true);
		if ($backup_dir === false) {
			throw new FW_Backup_Exception(__('Cannot create local backup directory. Not enough permissions?', 'fw'));
		}

		return $backup_dir;
	}
}
