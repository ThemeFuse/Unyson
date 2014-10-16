<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Process_Backup implements FW_Backup_Interface_Process
{
	private $storage;
	private $exporter;
	private $feedback;
	private $backup_info;

	public function __construct(FW_Backup_Interface_Storage $storage, FW_Backup_Interface_Export $exporter, FW_Backup_Interface_Feedback $feedback, FW_Backup_Info $backup_info)
	{
		$this->storage = $storage;
		$this->exporter = $exporter;
		$this->feedback = $feedback;
		$this->backup_info = $backup_info;
	}

	public function get_backup_info()
	{
		return $this->backup_info;
	}

	public function run()
	{
		try {
			$f = $this->feedback;

			$f->set_task(__('Checking storage layer...', 'fw'));
			$this->storage->ping($f);

			// Export whatever necessary into a file
			$f->set_task(__('Exporting...', 'fw'));
			$file = $this->exporter->export($f);

			// Collect information about file (its time and what's inside)
			$r = new FW_Backup_Reflection_Backup_Archive();
			$file_time = filemtime($file);
			$file_contents = $r->inspect_file($file);

			$f->set_task(__('Moving file to a persistent storage...', 'fw'));
			$storage_file = $this->storage->move($file, $f);

			$f->set_task(__('Finishing...', 'fw'));

			// Record the time the backup was complete on and meta about storage file
			$this->backup_info->set_storage($this->storage->get_name());
			$this->backup_info->set_storage_file($storage_file);
			$this->backup_info->set_storage_file_time($file_time);
			$this->backup_info->set_storage_file_contents($file_contents);
			$this->backup_info->set_completed_at(time());
		}
		catch (FW_Backup_Exception_Cancelled $exception) {
			$this->backup_info->set_cancelled_at(time());
		}
		catch (FW_Backup_Exception $exception) {
			$this->backup_info->set_failed_at(time());
		}

		$this->backup_info->set_finished_at(time());

		// Remove temporary file
		if (isset($file) && file_exists($file)) {
			unlink($file);
		}

		if (isset($exception)) {
			throw $exception;
		}
	}
}
