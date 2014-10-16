<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Feedback
{
	private $task;
	private $size;
	private $progress;
	private $description;

	public function set_task($task, $size = 0)
	{
		$this->task = $task;
		$this->size = $size;
		$this->progress = '';
		$this->description = false;
	}

	public function set_progress($progress, $description = false)
	{
		$this->progress = $progress;
		$this->description = $description;
	}

	public function get_task() { return $this->task; }
	public function get_size() { return $this->size; }
	public function get_progress() { return $this->progress; }
	public function get_description() { return $this->description; }
}
