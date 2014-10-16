<?php if (!defined('FW')) die('Forbidden');

/**
 * Collect posts too old to keep
 */
class FW_Backup_Walker_Apply_Age_Limit
{
	private $cron_id;
	private $oldest_possible_gmt;
	private $result = array();

	public function __construct($cron_id)
	{
		$this->cron_id = $cron_id;
		if ($lifetime = $this->backup()->settings()->get_cron_lifetime($cron_id)) {
			$this->oldest_possible_gmt = current_time('timestamp', true) - $lifetime*($this->backup()->debug ? MINUTE_IN_SECONDS : DAY_IN_SECONDS);
		}
		else {
			$this->oldest_possible_gmt = 0;
		}
	}

	public function get_result()
	{
		return $this->result;
	}

	public function walk(WP_Post $post)
	{
		if ($a = $this->backup()->get_backup_info($post->ID)) {
			if ($a->get_cron_job() == $this->cron_id) {
				if (strtotime($post->post_date_gmt) < $this->oldest_possible_gmt) {
					$this->result[] = $post->ID;
				}
			}
		}
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
