<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Process_Apply_Age_Limit
{
	private $seconds_in_unit;
	private $post_type;
	private $meta;
	private $feedback;
	private $cron_id;
	private $age_limit;

	public function __construct($seconds_in_unit, $post_type, FW_Backup_Service_Post_Meta $meta, FW_Backup_Interface_Feedback $feedback)
	{
		$this->seconds_in_unit = $seconds_in_unit;
		$this->post_type = $post_type;
		$this->meta = $meta;
		$this->feedback = $feedback;
	}

	public function run($cron_id, $age_limit_in_units)
	{
		if (empty($age_limit_in_units)) {
			return;
		}

		$this->feedback->set_task(__('Removing obsolete backup copies...', 'fw'));

		$this->cron_id = $cron_id;
		$this->age_limit = time() - $age_limit_in_units*$this->seconds_in_unit;
		$this->foreach_post('publish');
	}

	private function foreach_post($post_status = 'publish', $done = 0)
	{
		$default = array(
			'post_type' => $this->post_type,
			'posts_per_page' => 50
		);

		$offset = 0;
		while (true) {
			$a = get_posts(array_merge($default, compact('post_status', 'offset')));
			if (empty($a)) {
				break;
			}
			foreach ($a as $post) {
				$this->apply_age_limit($post, $done + $offset);
				$offset += 1;
			}
		}

		return $offset;
	}

	private function apply_age_limit(WP_Post $post)
	{
		$this->meta->set_post_id($post->ID);
		if ($this->meta->get_cron_id() == $this->cron_id) {
			$post_date = strtotime($this->meta->get_post_date());
			if ($post_date < $this->age_limit) {
				wp_trash_post($post->ID);
			}
		}
	}
}
