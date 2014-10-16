<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Menu
{
	public function __construct()
	{
		if (is_admin()) {
			$this->add_admin_actions();
		}
	}

	private function add_admin_actions()
	{
		add_action('admin_menu', array($this, '_admin_action_admin_menu'));
	}

	/**
	 * @internal
	 */
	public function _admin_action_admin_menu()
	{
		if ($this->backup()->get_auto_install_dir()) {
			// Remove Tools/Import and Tools/Export sub-menus
			remove_submenu_page('tools.php', 'import.php');
			remove_submenu_page('tools.php', 'export.php');

			add_management_page(__('Auto Install', 'fw'), __('Auto Install', 'fw'), 'manage_options', 'auto-install', array($this, '_auto_install_page'));
		}
	}

	/**
	 * @internal
	 */
	public function _auto_install_page()
	{
		if ($this->backup()->action()->is_auto_install()) {
			$this->backup()->render('auto-install-2-run');
		}
		else {
			$this->backup()->render('auto-install-1-welcome');
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
