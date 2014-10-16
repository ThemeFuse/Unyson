<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Walker_Demo_Install
{
	private $result = array();

	public function get_result()
	{
		return $this->result;
	}

	public function walk(WP_Post $post)
	{
		if ($a = $this->backup()->get_backup_info($post->ID)) {
			if ($a->is_demo_install() && $a->is_completed()) {
				$this->result[] = $post->ID;
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
