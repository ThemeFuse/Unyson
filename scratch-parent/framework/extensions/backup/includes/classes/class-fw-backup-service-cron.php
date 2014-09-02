<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Service_Cron implements FW_Backup_Interface_Cron
{
	private $active;
	private $backup_now;
	private $id;
	private $title;
	private $schedule_title;
	private $next_at_title;
	private $storage;
	private $storage_id;
	private $backup_contents;

	public function __construct($active, $backup_now, $id, $title, $schedule_title, $next_at_title, FW_Backup_Interface_Storage $storage, $storage_id, array $backup_contents)
	{
		$this->active = $active;
		$this->backup_now = $backup_now;
		$this->id = $id;
		$this->title = $title;
		$this->schedule_title = $schedule_title;
		$this->next_at_title = $next_at_title;
		$this->storage = $storage;
		$this->storage_id = $storage_id;
		$this->backup_contents = $backup_contents;
	}

	public function is_active()
	{
		return $this->active;
	}

	public function has_backup_now()
	{
		return $this->backup_now;
	}

	public function get_id()
	{
		return $this->id;
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

	public function get_storage_id()
	{
		return $this->storage_id;
	}

	public function get_backup_contents()
	{
		return $this->backup_contents;
	}
}
