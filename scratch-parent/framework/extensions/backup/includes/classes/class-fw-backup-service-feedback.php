<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Service_Feedback implements FW_Backup_Interface_Feedback
{
	private $post_meta;

	private $task_title;
	private $task_progress;
	private $task_progress_total;
	private $task_progress_title;

	private $commit_at;
	private $commit_timeout = 0.2; // 5 times per second

	public function __construct(FW_Backup_Service_Post_Meta $post_meta)
	{
		$this->post_meta = $post_meta;
	}

	public function set_task($task_title, $progress_total = 0)
	{
		$this->task_title = $task_title;
		$this->task_progress = null;
		$this->task_progress_total = $progress_total;
		$this->task_progress_title = null;
		$this->commit(true);
	}

	public function set_task_progress($task_progress_complete, $task_progress_title = null)
	{
		if ($this->task_progress_total) {
			$this->task_progress = number_format($task_progress_complete/$this->task_progress_total*100, 2).'%';
		}
		else {
			$this->task_progress = $task_progress_complete;
		}
		$this->task_progress_title = $task_progress_title;
		$this->commit($task_progress_complete == $this->task_progress_total);
	}

	public function get_task_title()
	{
		return $this->task_title;
	}

	public function get_task_progress()
	{
		return $this->task_progress;
	}

	public function get_task_progress_title()
	{
		return $this->task_progress_title;
	}

	// Internals

	private function commit($force = false)
	{
		if ($force || microtime(true) - $this->commit_at > $this->commit_timeout) {
			if ($this->post_meta->get_cancelled()) {
				throw new FW_Backup_Exception_Cancelled();
			}
			$this->post_meta->set_progress($this);
			$this->commit_at = microtime(true);
		}
	}
}
