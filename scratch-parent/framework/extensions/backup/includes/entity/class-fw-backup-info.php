<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Info
{
	private $cron_job;
	private $storage;
	private $storage_file;
	private $storage_file_time;
	private $storage_file_contents;

	// Only *Demo Install* backups has theme
	private $theme;

	private $created_at;
	private $updated_at;
	private $queued_at;
	private $started_at;
	private $imported_at;
	private $completed_at;
	private $cancelled_at;
	private $failed_at;
	private $finished_at;

	public function is_demo_install() { return !empty($this->theme); }

	public function is_queued() { return !!$this->queued_at; }
	public function is_started() { return !!$this->started_at; }
	public function is_imported() { return !!$this->imported_at; }
	public function is_completed() { return !!$this->completed_at; }
	public function is_cancelled() { return !!$this->cancelled_at; }
	public function is_failed() { return !!$this->failed_at; }
	public function is_finished() { return !!$this->finished_at; }

	public function has_db() { return @$this->storage_file_contents['db']; }
	public function has_fs() { return @$this->storage_file_contents['fs']; }

	public function get_theme() { return $this->theme; }
	public function get_cron_job() { return $this->cron_job; }
	public function get_storage() { return $this->storage; }
	public function get_storage_file() { return $this->storage_file; }
	public function get_storage_file_time() { return $this->storage_file_time; }

	public function get_created_at() { return $this->created_at; }
	public function get_updated_at() { return $this->updated_at; }
	public function get_queued_at() { return $this->queued_at; }
	public function get_started_at() { return $this->started_at; }
	public function get_imported_at() { return $this->imported_at; }
	public function get_completed_at() { return $this->completed_at; }
	public function get_cancelled_at() { return $this->cancelled_at; }
	public function get_failed_at() { return $this->failed_at; }
	public function get_finished_at() { return $this->finished_at; }

	public function set_theme($theme) { $this->theme = $theme; }
	public function set_cron_job($cron_job) { $this->cron_job = $cron_job; }
	public function set_storage($storage) { $this->storage = $storage; }
	public function set_storage_file($storage_file) { $this->storage_file = $storage_file; }
	public function set_storage_file_time($storage_file_time) { $this->storage_file_time = $storage_file_time; }
	public function set_storage_file_contents($storage_file_contents) { $this->storage_file_contents = $storage_file_contents; }

	public function set_created_at($created_at) { $this->created_at = $created_at; }
	public function set_updated_at($updated_at) { $this->updated_at = $updated_at; }
	public function set_queued_at($queued_at) { $this->queued_at = $queued_at; }
	public function set_started_at($started_at) { $this->started_at = $started_at; }
	public function set_imported_at($imported_at) { $this->imported_at = $imported_at; }
	public function set_completed_at($completed_at) { $this->completed_at = $completed_at; }
	public function set_cancelled_at($cancelled_at) { $this->cancelled_at = $cancelled_at; }
	public function set_failed_at($failed_at) { $this->failed_at = $failed_at; }
	public function set_finished_at($finished_at) { $this->finished_at = $finished_at; }
}
