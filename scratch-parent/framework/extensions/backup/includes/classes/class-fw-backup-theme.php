<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Theme
{
	public function __construct()
	{
		if (is_admin()) {
			$this->add_admin_actions();
		}
	}

	private function add_admin_actions()
	{
		add_action('after_switch_theme', array($this, '_admin_action_after_switch_theme'));
	}

	/**
	 * @internal
	 */
	public function _admin_action_after_switch_theme()
	{
		if ($a = $this->backup()->action()->url_backup_auto_install_page()) {
			wp_redirect($a);
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
