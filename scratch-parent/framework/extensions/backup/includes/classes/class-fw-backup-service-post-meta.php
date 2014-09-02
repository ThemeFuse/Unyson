<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Service_Post_Meta
{
	private $post_id;

	public function get_post_id()
	{
		return $this->post_id;
	}

	public function set_post_id($post_id)
	{
		$this->post_id = $post_id;
	}

	public function get_post_title()
	{
		return $this->get_post()->post_title;
	}

	public function get_post_date()
	{
		return $this->get_post()->post_date;
	}

	public function set_state_title($state_title, $clear_on_publish = false)
	{
		$this->update('state.title', $state_title);
		$this->update('state.clear_on_publish', $clear_on_publish);

		$post = $this->get_post();
		$post->post_title = sprintf('%s: %s', $this->get_cron_title(), $state_title);
		wp_update_post($post);
	}

	public function get_state_title()
	{
		return $this->query('state.title');
	}

	public function publish($post_content = null)
	{
		$post = $this->get_post();
		$post->post_status = 'publish';
		$post->post_content = $post_content;
		wp_update_post($post);

		// used in the end of restore for flash message
		// $this->delete('cron.title');
		$this->delete('cron.start');
		$this->delete('cron.cancel');
		$this->delete('cron.progress');
		if ($this->query('state.clear_on_publish')) {
			$this->delete('state.title');
		}
		$this->delete('state.clear_on_publish');
	}

	public  function get_cron_id()
	{
		return $this->query('cron');
	}

	public function set_cron_id($cron_id)
	{
		$this->update('cron', $cron_id);
	}

	public function get_storage_id()
	{
		return $this->query('storage');
	}

	public function set_storage_id($storage_id)
	{
		$this->update('storage', $storage_id);
	}

	public function get_backup_contents()
	{
		return $this->query('backup.contents');
	}

	public function set_backup_contents(array $backup_contents)
	{
		$this->update('backup.contents', $backup_contents);
	}

	/**
	 * @return false|FW_Backup_Interface_File
	 */
	public function get_backup_file()
	{
		$backup_file = $this->query('backup.file');

		if ($backup_file instanceof FW_Backup_Interface_File) {
			return $backup_file;
		}

		return false;
	}

	public function set_backup_file(FW_Backup_Interface_File $backup_file)
	{
		$this->update('backup.file', $backup_file);
	}

	public function get_cron_title()
	{
		return $this->query('cron.title');
	}

	public function set_cron_title($cron_title)
	{
		$this->update('cron.title', $cron_title);
	}

	public function get_progress()
	{
		return $this->query('cron.progress');
	}

	public function set_progress(FW_Backup_Interface_Feedback $feedback)
	{
		$this->update('cron.progress', array(
			'task_title' => $feedback->get_task_title(),
			'task_progress' => $feedback->get_task_progress(),
			'task_progress_title' => $feedback->get_task_progress_title(),
		));
	}

	public function get_cron_started()
	{
		return $this->query('cron.start');
	}

	public function set_cron_started()
	{
		$this->update('cron.start', time());
		$this->set_state_title(__('Running...', 'fw'));
	}

	public function get_cancelled()
	{
		return $this->query('cron.cancel');
	}

	public function set_cancelled()
	{
		$this->update('cron.cancel', time());
		$this->set_state_title(__('Cancelling...', 'fw'));
	}

	// Internal

	private function check()
	{
		if (empty($this->post_id)) {
			throw new FW_Backup_Exception('post.meta was not initialized');
		}
	}

	private function get_post()
	{
		$this->check();
		return get_post($this->post_id);
	}

	private function query($key)
	{
		$this->check();
		return get_post_meta($this->post_id, $key, true);
	}

	private function update($key, $value)
	{
		$this->check();
		fw_update_post_meta($this->post_id, $key, $value);
	}

	private function delete($key)
	{
		$this->check();
		fw_delete_post_meta($this->post_id, $key);
	}
}
