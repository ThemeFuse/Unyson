<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Feedback_Commit implements FW_Backup_Interface_Feedback
{
	const COMMIT_TIMEOUT = 0.2;

	private $callable;
	private $feedback;
	private $commit_at;

	public function __construct($callable)
	{
		$this->callable = $callable;
		$this->feedback = new FW_Backup_Feedback();
	}

	public function set_task($task, $size = 0)
	{
		$this->feedback->set_task($task, $size);
		$this->commit(true);
	}

	public function set_progress($progress, $description = false)
	{
		$this->feedback->set_progress($progress, $description);
		$this->commit();
	}

	private function commit($force = false)
	{
		if ($force || (microtime(true) - $this->commit_at) > self::COMMIT_TIMEOUT) {
			call_user_func($this->callable, $this->feedback);
			$this->commit_at = microtime(true);
		}
	}
}
