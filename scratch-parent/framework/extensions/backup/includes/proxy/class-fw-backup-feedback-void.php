<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Feedback_Void implements FW_Backup_Interface_Feedback
{
	public function set_task($task, $size = 0)
	{
	}

	public function set_progress($progress, $description = false)
	{
	}
}
