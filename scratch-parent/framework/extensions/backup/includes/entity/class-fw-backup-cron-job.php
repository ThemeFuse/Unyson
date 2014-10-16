<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Cron_Job
{
	private $id;
	private $backup_now;
	private $title;
	private $schedule_title;
	private $next_at_title;
	private $storage;
	private $exporter;

	public function __construct($id, $backup_now, $title, $schedule_title, $next_at_title, $storage, FW_Backup_Interface_Export $exporter)
	{
		$this->id = $id;
		$this->backup_now = $backup_now;
		$this->title = $title;
		$this->schedule_title = $schedule_title;
		$this->next_at_title = $next_at_title;
		$this->storage = $storage;
		$this->exporter = $exporter;
	}

	public function dup($param)
	{
		$dup = clone $this;

		foreach ($param as $key => $value) {
			$dup->$key = $value;
		}

		return $dup;
	}

	public function get_id()
	{
		return $this->id;
	}

	public function is_active()
	{
		return !empty($this->schedule_title);
	}

	public function get_backup_now()
	{
		return $this->backup_now;
	}

	public function get_title()
	{
		return $this->title;
	}

	public function get_schedule_title()
	{
		return $this->schedule_title;
	}

	public function get_next_at_title()
	{
		return $this->next_at_title;
	}

	public function get_storage()
	{
		return $this->storage;
	}

	public function get_exporter()
	{
		return $this->exporter;
	}
}
