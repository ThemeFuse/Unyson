<?php if (!defined('FW')) die('Forbidden');

require_once dirname(__FILE__) . '/includes/interfaces/interface-fw-backup-feedback.php';
require_once dirname(__FILE__) . '/includes/interfaces/interface-fw-backup-export.php';
require_once dirname(__FILE__) . '/includes/interfaces/interface-fw-backup-storage.php';
require_once dirname(__FILE__) . '/includes/interfaces/interface-fw-backup-process.php';
require_once dirname(__FILE__) . '/includes/classes/class-fw-extension-backup-storage.php';

class FW_Extension_Backup extends FW_Extension
{
	public $debug = 0;

	/**
	 * @var FW_Backup_Post_Type
	 */
	private $post_type;

	/**
	 * @var FW_Backup_Cron
	 */
	private $cron;

	/**
	 * @var FW_Backup_Settings
	 */
	private $settings;

	/**
	 * @var FW_Backup_Action
	 */
	private $action;

	/**
	 * @var FW_Backup_Ajax
	 */
	private $ajax;

	/**
	 * @var FW_Backup_Menu
	 */
	private $menu;

	/**
	 * @var FW_Backup_Theme
	 */
	private $theme;

	/**
	 * @var FW_Backup_Format
	 */
	private $format;

	private $request_filesystem_credentials;





	public function post_type()
	{
		return $this->post_type;
	}

	public function cron()
	{
		return $this->cron;
	}

	public function settings()
	{
		return $this->settings;
	}

	public function action()
	{
		return $this->action;
	}

	public function ajax()
	{
		return $this->ajax;
	}

	public function menu()
	{
		return $this->menu;
	}

	public function theme()
	{
		return $this->theme;
	}

	public function format()
	{
		return $this->format;
	}





	public function render_str($rel, $param = array())
	{
		return $this->render_view($rel, $param);
	}

	public function render($rel, $param = array())
	{
		echo $this->render_view($rel, $param);
	}





	/**
	 * @internal
	 */
	public function _init()
	{
		$this->add_actions();

        if (is_admin()) {
            $this->add_admin_actions();
        }
	}

	private function add_actions()
	{
		add_action('fw_extensions_init', array($this, '_action_fw_extensions_init'));
	}

	private function add_admin_actions()
	{
		add_action('admin_enqueue_scripts', array($this, '_admin_action_admin_enqueue_scripts'));
	}

	/**
	 * @internal
	 */
	public function _action_fw_extensions_init()
	{
		$this->action = new FW_Backup_Action();
		$this->cron = new FW_Backup_Cron($this);
		$this->settings = new FW_Backup_Settings($this->get_storage_list());
		$this->post_type = new FW_Backup_Post_Type();
		$this->format = new FW_Backup_Format();

		if (is_admin()) {
			$this->ajax = new FW_Backup_Ajax();
			$this->menu = new FW_Backup_Menu();
			$this->theme = new FW_Backup_Theme();
		}
	}

	/**
	 * @internal
	 *
	 * @var $hook
	 */
	public function _admin_action_admin_enqueue_scripts($hook)
	{
		global $post_type;

		$d = $this->get_declared_URI();
		$v = $this->manifest->get_version();
		$backup_restore = false;

		if ($hook == 'edit.php' && $post_type == $this->post_type->get_post_type()) {

			// FIXME where to put this .css files?
			// FIXME replace img/*.png by glyphicons

			wp_enqueue_media();

			wp_enqueue_style('backup', "$d/static/css/admin.css", array(), $v);
			wp_enqueue_script('backup', "$d/static/js/backup.js", array('jquery'), $v);

			if ($this->action()->is_backup_restore()) {
				$backup_restore = true;
			}

			if ($this->action()->get_feedback_subject()) {
				wp_enqueue_script('backup-feedback', "$d/static/js/feedback.js", array('jquery'), $v);
			}
		}
		else if ($this->action()->is_auto_install()) {
			$backup_restore = true;
		}

		if ($backup_restore) {
			wp_enqueue_style('backup', "$d/static/css/admin.css", array(), $v);
			wp_enqueue_style('backup-restore', "$d/static/css/restore.css", array(), $v);
			wp_enqueue_script('backup-restore', "$d/static/js/restore.js", array('jquery'), $v);
		}
	}

    /**
     * @return FW_Backup_Interface_Storage[]
     */
    private function get_storage_list()
    {
		static $storage_list;

		if ($storage_list === null) {
			$storage_list = array();
			foreach ($this->get_children() as $child) {
				if ($child instanceof FW_Backup_Interface_Storage) {
					$storage_list[$child->get_name()] = $child;
				}
			}
		}

		return $storage_list;
    }

	public function get_storage($storage_id, $cron_id = false)
	{
		/**
		 * @var FW_Backup_Interface_Storage $storage
		 */

		$storage = fw()->extensions->get($storage_id);

		if (!$storage instanceof FW_Backup_Interface_Storage) {
			throw new FW_Backup_Exception_Not_Found(sprintf(__('Storage Not Found [%s]', 'fw'), $storage_id));
		}

		// Configure storage
		if ($cron_id) {
			$storage_options = $this->settings()->get_cron_storage_options($cron_id, $storage_id);
			$storage->set_storage_options($storage_options);
		}

		return $storage;
	}





	// This directory is used by FW_Extension_Storage_Local and
	// by FW_Backup_Export_File_System classes. The first class
	// is used it to store backup copies. The second class used
	// it to exclude this directory from backup archives.
	public function get_backup_dir()
	{
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/backup';
	}

	public function get_auto_install_dir()
	{
		$auto_install_dir = dirname(fw_get_framework_directory()) . '/auto-install';

		if (is_dir($auto_install_dir)) {
			return $auto_install_dir;
		}

		return false;
	}





	// Feedback

	/**
	 * @param $post_id
	 * @return FW_Backup_Feedback|false
	 */
	public function get_feedback($post_id)
	{
		$a = get_post_meta($post_id, 'feedback', true);

		if ($a instanceof FW_Backup_Feedback) {
			return $a;
		}

		return false;
	}

	public function update_feedback($post_id, FW_Backup_Feedback $feedback)
	{
		if ($backup_info = $this->get_backup_info($post_id)) {
			if ($backup_info->is_cancelled()) {
				throw new FW_Backup_Exception_Cancelled();
			}
		}

		update_post_meta($post_id, 'feedback', $feedback);
	}

	public function delete_feedback($post_id)
	{
		delete_post_meta($post_id, 'feedback');
	}





	// Request Filesystem Credentials

	public function set_request_filesystem_credentials($html)
	{
		$this->request_filesystem_credentials = $html;
	}

	public function get_request_filesystem_credentials()
	{
		return $this->request_filesystem_credentials;
	}





	// Backup

	/**
	 * @param int $post_id
	 * @return FW_Backup_Info|false
	 */
	public function get_backup_info($post_id)
	{
		$a = get_post_meta($post_id, 'backup_info', true);

		if ($a instanceof FW_Backup_Info) {
			return $a;
		}

		return false;
	}

	public function update_backup_info($post_id, FW_Backup_Info $backup_info)
	{
		$now = time();

		if (!$backup_info->get_created_at()) {
			$backup_info->set_created_at($now);
		}
		$backup_info->set_updated_at($now);

		update_post_meta($post_id, 'backup_info', $backup_info);
	}





	// Demo Install

	public function get_demo_install_list()
	{
		$walker = new FW_Backup_Walker_Demo_Install();
		$this->post_type()->foreach_post(array($walker, 'walk'), array('post_status' => 'trash'));
		return $walker->get_result();
	}

	public function get_demo_install()
	{
		foreach ($this->get_demo_install_list() as $post_id) {
			return $post_id;
		}

		return false;
	}
}