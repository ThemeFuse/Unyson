<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Cron
{
	public function __construct()
	{
		$this->add_actions();
		$this->add_filters();
	}

	public function get_schedule($cron_id)
	{
		$a = wp_get_schedule('fw_backup_cron', array($cron_id));

		if ($a === false) {
			return 'disabled';
		}

		return $a;
	}

	public function get_schedule_list()
	{
		// The very first key=value pair is default
		$schedules = array(
			'disabled' => __('Disabled', 'fw'),
		);

		foreach (wp_get_schedules() as $id => $schedule) {
			if (strpos($id, 'backup.') === 0) {
				$schedules[$id] = $schedule['display'];
			}
		}

		return $schedules;
	}

	public function schedule_backup_now($post_id)
	{
		wp_schedule_single_event(time(), 'fw_backup_now', array($post_id));
	}

	public function schedule_backup_demo_install($post_id)
	{
		wp_schedule_single_event(time(), 'fw_backup_demo_install', array($post_id));
	}

	public function reschedule()
	{
		// NOTE
		// If cron previously registered was not in here
		// it will not be unscheduled now, but next time
		// it will be fired.
		foreach (array_keys($this->get_cron_job_list()) as $cron_id) {

			$this->unschedule($cron_id);

			$schedule_id = $this->backup()->settings()->get_cron_schedule($cron_id);
			$schedules = wp_get_schedules();
			if (isset($schedules[$schedule_id])) {
				$schedule_interval = $schedules[$schedule_id]['interval'];
				$completed_at = $this->backup()->settings()->get_cron_completed_at($cron_id);
				// First time backup should be made right now. Then each time an interval was passed.
				wp_schedule_event(max($completed_at + $schedule_interval, time()), $schedule_id, 'fw_backup_cron', array($cron_id));
			}
		}
	}

	private function unschedule($cron_id)
	{
		wp_clear_scheduled_hook('fw_backup_cron', array($cron_id));
	}

	public function next_scheduled($cron_id)
	{
		return wp_next_scheduled('fw_backup_cron', array($cron_id));
	}

	public function get_cron_job($cron_id)
	{
		$a = $this->get_cron_job_list();

		if (isset($a[$cron_id])) {
			return $a[$cron_id];
		}

		throw new FW_Backup_Exception_Not_Found("Cron Job Not Found [$cron_id]");
	}

	/**
	 * @return FW_Backup_Cron_Job[]
	 */
	public function get_cron_job_list()
	{
		/**
		 * @var FW_Backup_Interface_Storage $storage_local
		 */

		static $cron_job_list;

		if ($cron_job_list === null) {
//			$cron_job_list = $this->debug_cron_job_list();
//			return $cron_job_list;

			$template = array(
				array('cron_full', true, __('Full Backup', 'fw'), new FW_Backup_Export_Full()),
				array('cron_database', $this->backup()->debug, __('Database Backup', 'fw'), new FW_Backup_Export_Database()),
			);

			$schedules = $this->get_schedule_list();

			$cron_job_list = array();
			foreach ($template as $a) {
				list ($cron_id, $show_backup_now_button, $title, $exporter) = $a;

				$schedule_id = $this->get_schedule($cron_id);
				if ($schedule_id === 'disabled') {
					$schedule_title = false;
					$next_at_title = false;
				}
				else {
					$schedule_title = $schedules[$schedule_id];
					$next_at_title = sprintf(__('Next Backup on %s', 'fw'), $this->backup()->format()->format_date_time_gmt($this->next_scheduled($cron_id)));
				}

				$storage = $this->backup()->settings()->get_cron_storage($cron_id);

				$cron_job_list[$cron_id] = new FW_Backup_Cron_Job($cron_id, $show_backup_now_button, $title, $schedule_title, $next_at_title, $storage, $exporter);
			}
		}

		return $cron_job_list;
	}

	private function add_actions()
	{
		add_action('fw_backup_cron', array($this, '_action_fw_backup_cron'));
		add_action('fw_backup_now', array($this, '_action_fw_backup_now'));
		add_action('fw_backup_demo_install', array($this, '_action_fw_backup_demo_install'));
	}

	private function add_filters()
	{
		add_filter('cron_schedules', array($this, '_filter_cron_schedules'), 5);
	}

	/**
	 * @internal
	 *
	 * @var $cron_id
	 */
	public function _action_fw_backup_cron($cron_id)
	{
		try {
			$this->backup()->action()->do_backup_background_cron($cron_id);
		}
		catch (FW_Backup_Exception_Not_Found $exception) {
			$this->unschedule($cron_id);
		}
		catch (Exception $exception) {
		}
	}

	/**
	 * @internal
	 *
	 * @var $post_id
	 */
	public function _action_fw_backup_now($post_id)
	{
		try {
			$this->backup()->action()->do_backup_background_run($post_id);
		}
		catch (Exception $exception) {
		}
	}

	/**
	 * @internal
	 *
	 * @var $post_id
	 */
	public function _action_fw_backup_demo_install($post_id)
	{
		try {
			$this->backup()->action()->do_backup_background_demo_install($post_id);
		}
		catch (Exception $exception) {
		}
	}

	/**
	 * @internal
	 *
	 * @var $schedules
	 */
	public function _filter_cron_schedules($schedules)
	{
		if ($this->backup()->debug) {
			$schedules['backup.1min'] = array('interval' => MINUTE_IN_SECONDS, 'display' => __('1 min', 'fw'));
			$schedules['backup.4min'] = array('interval' => 4*MINUTE_IN_SECONDS, 'display' => __('4 min', 'fw'));
		}

		$schedules['backup.daily'] = array('interval' => DAY_IN_SECONDS, 'display' => __('Daily', 'fw'));
		$schedules['backup.weekly'] = array('interval' => WEEK_IN_SECONDS, 'display' => __('Weekly', 'fw'));
		$schedules['backup.monthly'] = array('interval' => 4*WEEK_IN_SECONDS, 'display' => __('Monthly', 'fw'));

		return $schedules;
	}

	private function debug_cron_job_list()
	{
		return array(
			'cron_full' => new FW_Backup_Cron_Job(
					'cron_full',
					true,
					__('Full Backup', 'fw'),
					__('Daily', 'fw'),
					sprintf(__('Next Backup on %s', 'fw'), date('d.m.Y H:i:s', $this->next_scheduled('cron_full'))),
					$this->backup()->settings()->get_cron_storage('cron_full'),
					// new FW_Backup_Export_Full()
					new FW_Backup_Export_Debug()
				),
			'cron_database' => new FW_Backup_Cron_Job(
					'cron_database',
					true,
					__('Database Backup', 'fw'),
					false,
					false,
					$this->backup()->settings()->get_cron_storage('cron_database'),
					// new FW_Backup_Export_Database(),
					new FW_Backup_Export_Debug()
				),
		);
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
