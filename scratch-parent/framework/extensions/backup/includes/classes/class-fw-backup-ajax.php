<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Ajax
{
	public function __construct()
    {
	    if (is_admin()) {
		    $this->add_admin_actions();
	    }
    }

    private function add_admin_actions()
    {
		add_action('wp_ajax_backup-settings-save', array($this, '_admin_action_wp_ajax_backup_settings_save'));
		add_action('wp_ajax_backup-feedback', array($this, '_admin_action_wp_ajax_backup_feedback'));
    }

	/**
	 * @internal
	 */
	public function _admin_action_wp_ajax_backup_settings_save()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error();
		}

		$settings = $this->backup()->settings();

		$values = fw_get_options_values_from_input($settings->get_options(), FW_Request::POST('values'));
		$settings->save($values);

		wp_send_json_success();
	}

	/**
	 * @internal
	 */
	public function _admin_action_wp_ajax_backup_feedback()
	{
		$subject = FW_Request::POST('subject');

		if ($feedback = $this->backup()->get_feedback($subject)) {
			$html = $this->backup()->render_str('feedback', compact('subject'));
			wp_send_json_success(compact('html'));
		}

		wp_send_json_error(array('error' => 'No feedback was found'));
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
