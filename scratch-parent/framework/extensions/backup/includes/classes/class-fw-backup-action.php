<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Action
{
	public function __construct()
    {
	    if (is_admin()) {
		    $this->add_admin_actions();
		    $this->add_admin_filters();
	    }
    }

	public function is_backup_restore()
	{
		$nonce = FW_Request::REQUEST('_wpnonce');
		return wp_verify_nonce($nonce, 'backup-restore');
	}

	public function is_auto_install()
	{
		$nonce = FW_Request::REQUEST('_wpnonce');
		return wp_verify_nonce($nonce, 'backup-auto-install');
	}

	public function get_feedback_subject()
	{
		if (FW_Request::GET('post_type') == $this->backup()->post_type()->get_post_type()) {
			return FW_Request::GET('feedback', false);
		}

		return false;
	}

	public function url_feedback($post_id)
	{
		if ($a = $this->backup()->get_backup_info($post_id)) {
			if ($a->is_finished()) {
				return false;
			}
		}

		return $this->backup()->post_type()->get_url(array('feedback' => $post_id));
	}

	public function url_backup_page()
	{
		return $this->backup()->post_type()->get_url();
	}

	public function url_backup_now($cron_id)
	{
		return $this->action_url('backup-now', array('cron' => $cron_id));
	}

	public function url_backup_demo_install()
	{
		return $this->action_url('backup-demo-install');
	}

	public function url_backup_unschedule($cron_id)
	{
		return $this->action_url('backup-unschedule', array('cron' => $cron_id));
	}

	public function url_backup_cancel($post_id)
	{
		if ($a = $this->backup()->get_backup_info($post_id)) {
			if (!$a->is_finished()) {
				return $this->action_url('backup-cancel', array('post' => $post_id));
			}
		}

		return false;
	}

	public function url_backup_download($post_id)
	{
		if ($a = $this->backup()->get_backup_info($post_id)) {
			if ($a->is_completed()) {
				return $this->action_url('backup-download', array('post' => $post_id));
			}
		}

		return false;
	}

	public function url_backup_trash($post_id)
	{
		return $this->wp_nonce_url_noesc("post.php?action=trash&amp;post=$post_id", "trash-post_$post_id");
	}

	public function url_backup_delete($post_id)
	{
		return $this->action_url('backup-delete', array('post' => $post_id));
	}

	public function url_backup_auto_install_page()
	{
		if ($this->backup()->get_auto_install_dir()) {
			return admin_url('tools.php?page=auto-install');
		}

		return false;
	}

	public function url_backup_auto_install()
	{
		if ($this->backup()->get_auto_install_dir()) {
			return $this->wp_nonce_url_noesc(admin_url('tools.php?page=auto-install'), 'backup-auto-install');
		}

		return false;
	}

	public function url_backup_restore($post_id)
	{
		if ($a = $this->backup()->get_backup_info($post_id)) {
			$post_type = $this->backup()->post_type()->get_post_type();
			$url = admin_url("edit.php?post_type=$post_type&post=$post_id");
			return $this->wp_nonce_url_noesc($url, 'backup-restore');
		}

		return false;
	}

	private function add_admin_actions()
	{
		add_action('admin_init', array($this, '_admin_action_admin_init'));
	}

	private function add_admin_filters()
	{
		add_filter('admin_memory_limit', array($this, '_admin_filter_admin_memory_limit'));
	}

	/**
	 * @internal
	 */
	public function _admin_action_admin_init()
	{
		$nonce = FW_Request::REQUEST('_wpnonce');

		// Auto-Install

		// Is a part of process of getting rid of update notifications after auto-install
		if (isset($_GET['auto-install-redirect'])) {
			wp_redirect($_GET['auto-install-redirect']);
		}

		// Auto Install (a process of restoring from **demo install** archive)

		if (wp_verify_nonce($nonce, 'backup-auto-install')) {
			$this->do_backup_auto_install();
		}

		// Feedback

		if ($post_id = $this->get_feedback_subject()) {
			if (!$this->backup()->get_feedback($post_id)) {
				wp_redirect($this->url_backup_page());
				exit;
			}
		}

		// Backup

		if (wp_verify_nonce($nonce, 'backup-now')) {
			$this->do_backup_now(FW_Request::GET('cron'));
		}

		if (wp_verify_nonce($nonce, 'backup-cancel')) {
			$this->do_backup_cancel(FW_Request::GET('post'));
		}

		if (wp_verify_nonce($nonce, 'backup-delete')) {
			$this->do_backup_delete(FW_Request::GET('post'));
		}

		if (wp_verify_nonce($nonce, 'backup-unschedule')) {
			$this->do_backup_unschedule(FW_Request::GET('cron'));
		}

		if (wp_verify_nonce($nonce, 'backup-download')) {
			$this->do_backup_download(FW_Request::GET('post'));
		}

		// Demo Install

		if (wp_verify_nonce($nonce, 'backup-demo-install')) {
			$this->do_backup_demo_install();
		}

		// Restore

		if ($this->is_backup_restore()) {
			$this->do_backup_restore(FW_Request::GET('post'));
		}
	}

	/**
	 * @internal
	 *
	 * @var $limit
	 * @return string
	 */
	public function _admin_filter_admin_memory_limit($limit)
	{
		if ($this->is_backup_restore() || $this->is_auto_install()) {
			// @ini_set('memory_limit', '1024M') for srdb class
			return '1024M';
		}

		return $limit;
	}





	private function do_backup_now($cron_id)
	{
		try {
			$cron = $this->backup()->cron()->get_cron_job($cron_id);
			$this->backup()->get_storage($cron->get_storage(), $cron_id)->ping(new FW_Backup_Feedback_Void());

			$post_id = $this->backup()->post_type()->insert();

			$backup_info = new FW_Backup_Info();
			$backup_info->set_cron_job($cron_id);
			$backup_info->set_storage($cron->get_storage());
			$backup_info->set_queued_at(time());
			$this->backup()->update_backup_info($post_id, $backup_info);

			// Without it Feedback page won't be opened
			$feedback = new FW_Backup_Feedback();
			$feedback->set_task(__('Waiting for start...', 'fw'));
			$this->backup()->update_feedback($post_id, $feedback);

			$this->backup()->cron()->schedule_backup_now($post_id);
			wp_redirect($this->url_feedback($post_id));
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add('backup-now', $exception->getMessage(), 'error');
			wp_redirect($this->backup()->post_type()->get_url());
		}

		exit;
	}

	private function do_backup_demo_install()
	{
		try {
			$storage = 'backup-storage-local';
			$this->backup()->get_storage($storage)->ping(new FW_Backup_Feedback_Void());

			$post_id = $this->backup()->post_type()->insert(array(
				'post_title' => __('Demo Install', 'fw'),
				'post_status' => 'trash',
			));

			$theme = explode('/', get_template());
			$theme = $theme[0];
			$theme = preg_replace('/[- ]+/', ' ', $theme);
			$theme = ucwords($theme);

			$backup_info = new FW_Backup_Info();
			$backup_info->set_theme($theme);
			$backup_info->set_storage($storage);
			$backup_info->set_queued_at(time());
			$this->backup()->update_backup_info($post_id, $backup_info);

			// Without it Feedback page won't be opened
			$feedback = new FW_Backup_Feedback();
			$feedback->set_task(__('Waiting for start...', 'fw'));
			$this->backup()->update_feedback($post_id, $feedback);

			$this->backup()->cron()->schedule_backup_demo_install($post_id);
			wp_redirect($this->url_feedback($post_id));
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add('backup-demo-install', $exception->getMessage(), 'error');
			wp_redirect($this->backup()->post_type()->get_url());
		}

		exit;
	}






	public function do_backup_background_cron($cron_id)
	{
		$cron = $this->backup()->cron()->get_cron_job($cron_id);
		$post_id = $this->backup()->post_type()->insert();

		$backup_info = new FW_Backup_Info();
		$backup_info->set_cron_job($cron_id);
		$backup_info->set_storage($cron->get_storage());
		$this->backup()->update_backup_info($post_id, $backup_info);

		// Without it Feedback page won't be opened
		$feedback = new FW_Backup_Feedback();
		$feedback->set_task(__('Initialization...', 'fw'));
		$this->backup()->update_feedback($post_id, $feedback);

		$this->do_backup_background_run($post_id);
	}

	public function do_backup_background_run($post_id)
	{
		$backup_info = $this->backup()->get_backup_info($post_id);

		try {
			$cron_job = $this->backup()->cron()->get_cron_job($backup_info->get_cron_job());

			$storage = $this->backup()->get_storage($cron_job->get_storage(), $cron_job->get_id());
			$exporter = $cron_job->get_exporter();
			$feedback = new FW_Backup_Feedback_Commit(FW_Backup_Callable::make(array($this->backup(), 'update_feedback'), $post_id));

			$backup_info->set_started_at(time());
			$this->backup()->update_backup_info($post_id, $backup_info);

			$process = new FW_Backup_Process_Backup($storage, $exporter, $feedback, $backup_info);
			$process->run();
		}
		catch (FW_Backup_Exception $exception) {
		}

		$this->backup()->update_backup_info($post_id, $backup_info);
		$this->backup()->delete_feedback($post_id);

		$post = get_post($post_id);
		if (isset($cron_job)) {
			$post->post_title = $cron_job->get_title();
		}
		$post->post_status = 'publish';
		if (isset($exception)) {
			$post->post_content = $exception->getMessage();
		}
		wp_update_post($post);

		if ($backup_info->is_completed()) {
			/**
			 * @var $cron_job
			 */
			$this->backup()->settings()->set_cron_completed_at($cron_job->get_id(), time());
			// Apply age limit
			$walker = new FW_Backup_Walker_Apply_Age_Limit($cron_job->get_id());
			$this->backup()->post_type()->foreach_post(array($walker, 'walk'));
			array_map('wp_trash_post', $walker->get_result());
		}
	}

	public function do_backup_background_demo_install($post_id)
	{
		/**
		 * @var FW_Extension_Backup_Storage_Local $storage_local
		 */

		$backup_info = $this->backup()->get_backup_info($post_id);

		try {
			$name_prefix = sanitize_file_name(strtolower($backup_info->get_theme()));
			$storage_local = $this->backup()->get_storage('backup-storage-local');
			$storage = new FW_Backup_Storage_Local_With_Prefix($storage_local, $name_prefix);
			$exporter = new FW_Backup_Export_Demo_Install();
			$feedback = new FW_Backup_Feedback_Commit(FW_Backup_Callable::make(array($this->backup(), 'update_feedback'), $post_id));

			$process = new FW_Backup_Process_Backup($storage, $exporter, $feedback, $backup_info);
			$process->run();
		}
		catch (FW_Backup_Exception $exception) {
		}

		$this->backup()->update_backup_info($post_id, $backup_info);
		$this->backup()->delete_feedback($post_id);

		// Move demo-install to the trash, this will hides it from *All* tab
		$post = get_post($post_id);
		$post->post_title = __('Demo Install', 'fw');
		$post->post_status = 'trash';
		if (isset($exception)) {
			$post->post_content = $exception->getMessage();
		}
		wp_update_post($post);

		// Remove obsolete demo-install archives
		if ($backup_info->is_completed()) {
			array_map('wp_delete_post', array_diff($this->backup()->get_demo_install_list(), array($post_id)));
		}
		else {
			// Something went wrong with the *demo-install* process. Backup
			// archive has nothing in it.
			wp_delete_post($post_id);
		}

		wp_redirect($this->backup()->post_type()->get_url());
		exit;
	}





	private function do_backup_unschedule($cron_id)
	{
		$this->backup()->settings()->set_cron_schedule($cron_id, 'disabled');
		wp_redirect($this->backup()->post_type()->get_url());
		exit;
	}

	private function do_backup_download($post_id)
	{
		try {
			$backup_info = $this->backup()->get_backup_info($post_id);
			if (!$backup_info || !$backup_info->is_completed()) {
				throw new FW_Backup_Exception(__('Could not download backup file', 'fw'));
			}

			$storage = $this->backup()->get_storage($backup_info->get_storage(), $backup_info->get_cron_job());
			$storage->download($backup_info->get_storage_file());

			throw new FW_Backup_Exception(__('Storage layer should never return from *download* method', 'fw'));
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add('backup-download', $exception->getMessage(), 'error');
			wp_redirect($this->backup()->post_type()->get_url());
		}

		exit;
	}

	private function do_backup_delete($post_id)
	{
		wp_delete_post($post_id);
		wp_redirect($this->backup()->post_type()->get_url());
		exit;
	}

	private function do_backup_cancel($post_id)
	{
		try {
			$backup_info = $this->backup()->get_backup_info($post_id);
			if (!$backup_info) {
				throw new FW_Backup_Exception(__('Could not cancel backup process', 'fw'));
			}

			if (!$backup_info->is_finished()) {
				if (!$backup_info->is_cancelled()) {
					$backup_info->set_cancelled_at(time());
					$this->backup()->update_backup_info($post_id, $backup_info);
				}
			}

			$url = $this->url_feedback($post_id);
			if (!$url) {
				$url = $this->backup()->post_type()->get_url();
			}

			wp_redirect($url);
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add('backup-cancel', $exception->getMessage(), 'error');
			wp_redirect($this->backup()->post_type()->get_url());
		}

		exit;
	}

	private function do_backup_restore($post_id)
	{
		try {
			$process = new FW_Backup_Process_Restore();
			$process->run($post_id);
		}
		catch (FW_Backup_Exception_Request_File_System_Credentials $exception) {
			$this->backup()->set_request_filesystem_credentials($exception->get_html());
			return;
		}
		catch (FW_Backup_Exception_Method_Not_Allowed $exception) {
			return;
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add('backup-restore', $exception->getMessage(), 'error');
		}

		wp_redirect($this->url_backup_page());
		exit;
	}

	private function do_backup_auto_install()
	{
		try {
			$process = new FW_Backup_Process_Auto_Install();
			$process->run();
		}
		catch (FW_Backup_Exception_Request_File_System_Credentials $exception) {
			$this->backup()->set_request_filesystem_credentials($exception->get_html());
			return;
		}
		catch (FW_Backup_Exception_Method_Not_Allowed $exception) {
			return;
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add('backup-auto-install', $exception->getMessage(), 'error');
		}

		wp_redirect($this->url_backup_auto_install_page());
		exit;
	}





	private function action_url($action, $param = array())
	{
		$post_type = $this->backup()->post_type()->get_post_type();
		$q = 'edit.php?' . http_build_query(array_merge(compact('post_type'), $param));
		return $this->wp_nonce_url_noesc($q, $action);
	}

	private function wp_nonce_url_noesc($actionurl, $action = -1, $name = '_wpnonce')
	{
		$actionurl = str_replace('&amp;', '&', $actionurl);
		return add_query_arg($name, wp_create_nonce($action), $actionurl);
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
