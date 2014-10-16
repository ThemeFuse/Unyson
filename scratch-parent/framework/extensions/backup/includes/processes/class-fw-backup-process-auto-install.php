<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Process_Auto_Install
{
	public function run()
	{
		$fs = new FW_Backup_Helper_File_System();
		$db = new FW_Backup_Helper_Database();
		$auto_install_dir = $this->backup()->get_auto_install_dir();

		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];

		// This forces *Restore* page to be opened event if
		// request_filesystem_credentials is not required. In the
		// latter case JavaScript will submit the page automatically
		// which opens up a *Restore in Progress* popup.
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new FW_Backup_Exception_Method_Not_Allowed();
		}

		$restore = new FW_Backup_Process_Restore();
		$restore->request_filesystem_credentials();

		try {
			$fs->check_permissions();

			// Do Full Backup before Auto Install
			$this->backup()->action()->do_backup_background_cron('cron_full');

			// Replace uploads directory
			$t = $fs->replace($fs->map($upload_dir), $fs->map("$auto_install_dir/uploads"));

			// Move backup directory from trashed dir into new upload dir
			if ($t) {
				$fs->move_existing("$t/backup", $fs->map("$upload_dir/backup"));
				// Remove trashed dir because we made Full Backup of the site
				$fs->rmdir($t);
			}

			$db->import("$auto_install_dir/database.sql", true, true, true);
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add('auto-install', $exception->getMessage(), 'error');
			// otherwise flash messages wont show
			wp_redirect($this->backup()->action()->url_backup_auto_install_page());
			exit;
		}

		// get rid of update notifications
		wp_redirect(admin_url('update-core.php?force-check=1&auto-install-redirect=' . esc_url(admin_url())));
		exit;
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
