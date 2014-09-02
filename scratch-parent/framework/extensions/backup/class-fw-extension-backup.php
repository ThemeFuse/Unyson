<?php if (!defined('FW')) die('Forbidden');

// Intended for sub-extensions
require dirname(__FILE__) . '/includes/interfaces/interface-fw-backup-storage-factory.php';

// TODO: Scheduled | by Admin
// TODO: change dropbox client_id and client_secret
// TODO: preserve dropbox settings across restore
//
// TODO: disable Restore if backup is in progress
// TODO: remove all temporary files (when something went wrong as well as when everything is ok)
// TODO: kill hanging jobs
//
// TODO: not enough disk space
// TODO: not enough permissions to some dirs ( ! ) Warning: scandir(/path/to/dir): failed to open dir: Permission denied
// TODO: Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 34104608 bytes)
// TODO: Maximum execution time of 30 seconds exceeded in /home/www/themefuse_scratch_theme/scratch-parent/framework/extensions/backup/includes/classes/class-fw-backup-service-export-file-system.php on line 54
// TODO: what if more than one site installed on one db?
// TODO: dropbox can upload files up to 150M
// TODO: what happens when site of a backup file is about 200M, 500M, 1G, 2G, 5G, 10G?
// TODO: what if dropbox settings was changed from one account to the other? [how to access previously made backups?]

// Test Case
// =========
//
// [ ] default storage should be local
// [ ] table prefix is different from wp_
// [ ] test age limit option

class FW_Extension_Backup extends FW_Extension
{
	// The Backup will think of each backup process
	// running more than BACKUP_TIMEOUT as of crashed.
	const BACKUP_TIMEOUT = 36000; // 10 minutes

	// The Backup will think of each backup process
	// for which Cancel button was clicked CANCEL_TIMEOUT
	// ago as of crashed.
	const CANCEL_TIMEOUT = 36000; // 10 minutes

	public $debug = false;

	/**
	 * @var array
	 */
	private $param;

	/**
	 * @var array
	 */
	private $service;

	/**
	 * @var array
	 */
	private $cron = array(
		array('cron.full', 'Full Backup', array('fs', 'db'), true),
		array('cron.db', 'Database Backup', array('db'), false),
	);

	/**
	 * @var string
	 */
	private $request_filesystem_credentials;

	// Initialization

	/**
	 * @internal
	 */
	public function _init()
	{
		// On debug mode use standard WordPress tables/functionality
		if ($this->debug) {
			// Create *** Now buttons for all Recurring Tasks
			foreach ($this->cron as &$cron) {
				$cron[3] = true;
			}
		} elseif (current_user_can('edit_posts')) {
			if (isset($_GET['post_type']) && $_GET['post_type'] == $this->get_post_type() && isset($_GET['trashed'])) {
				// Get rid of "1 post moved to the Trash"
				wp_redirect(admin_url('edit.php?'.http_build_query(array('post_type' => $this->get_post_type()))));
				exit;
			}
		}

		$this->add_actions();
		$this->add_filters();

		if (is_admin()) {
			$this->add_admin_actions();
			$this->add_admin_filters();
		}
	}

	private function add_actions()
	{
		add_action('fw_extensions_init', array($this, '_action_fw_extensions_init'));
		add_action('fw_backup_cron', array($this, '_action_fw_backup_cron'));
		add_action('fw_backup_now', array($this, '_action_fw_backup_now'));
		add_action('trashed_post', array($this, '_action_trashed_post'));
		add_action('before_delete_post', array($this, '_action_before_delete_post'));
	}

	private function add_filters()
	{
		add_filter('cron_schedules', array($this, '_filter_cron_schedules'), 5);
	}

	private function add_admin_actions()
	{
		$post_type = $this->get_post_type();

		add_action('admin_init', array($this, '_admin_action_admin_init'));
		add_action('admin_enqueue_scripts', array($this, '_admin_action_admin_enqueue_scripts'));
		add_action('wp_ajax_backup-progress', array($this, '_admin_action_wp_ajax_backup_progress'));
		add_action('wp_ajax_backup-settings-save', array($this, '_admin_action_wp_ajax_backup_settings_save'));
		add_action("manage_{$post_type}_posts_custom_column", array($this, '_admin_action_manage_xxx_posts_custom_column'), 10, 2);
	}

	private function add_admin_filters()
	{
		$screen_id = "edit-{$this->get_post_type()}";

		add_filter('post_row_actions', array($this, '_admin_filter_post_row_actions'), 10, 2);
		add_filter("manage_{$screen_id}_columns", array($this, '_admin_filter_manage_xxx_columns'));
		add_filter("views_{$screen_id}", array($this, '_admin_filter_views_xxx_hack'));
	}

	// Public

	public function param($key)
	{
		if (!array_key_exists($key, $this->param)) {
			throw new FW_Backup_Exception_Parameter_Not_Found($key);
		}

		return $this->param[$key];
	}

	public function service($service_id, $instanceof = null)
	{
		if (!isset($this->service[$service_id])) {
			throw new FW_Backup_Exception_Service_Not_Found($service_id);
		}

		$service = $this->service[$service_id];

		if (isset($instanceof)) {
			if (!$service instanceof $instanceof) {
				throw new FW_Backup_Exception_Service_Invalid_Interface($service_id, $instanceof);
			}
		}

		if (strpos($service_id, 'shared.') === 0) {
			return $service;
		}

		return clone $service;
	}

	public function service_list($instanceof)
	{
		$ret = array();
		foreach ($this->service as $id => $inst) {
			if ($inst instanceof $instanceof) {
				$ret[$id] = $this->service($id);
			}
		}
		return $ret;
	}

	public function get_post_type()
	{
		return 'fw_backup';
	}

	public function get_backup_dir($create = false)
	{
		// Its important for $backup_dir parameter to use DIRECTORY_SEPARATOR.
		// service('fs') relies on this convention.

		$upload_dir = wp_upload_dir();
		$backup_dir = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['basedir']) . DIRECTORY_SEPARATOR . 'backup';

		if ($create && !file_exists($backup_dir . DIRECTORY_SEPARATOR . 'index.php')) {
			if (! @wp_mkdir_p($backup_dir, 0777, true)) {
				return false;
			}
			if (@file_put_contents("$backup_dir/index.php", '<?php header(\'HTTP/1.0 403 Forbidden\'); die(\'<h1>Forbidden</h1>\');') === false) {
				return false;
			}
		}

		return $backup_dir;
	}

	public function url_backup()
	{
		return admin_url('edit.php?'.http_build_query(array('post_type' => $this->get_post_type())));
	}

	public function url_backup_progress($post_id)
	{
		$url = admin_url('edit.php?'.http_build_query(array('post_type' => $this->get_post_type(), 'post' => $post_id)));
		return $this->wp_nonce_url_raw($url, 'backup-progress');
	}

	public function url_backup_now($cron_id)
	{
		return $this->wp_nonce_url_raw(admin_url("?cron=$cron_id"), 'backup-now');
	}

	public function url_backup_cancel($post_id)
	{
		return $this->wp_nonce_url_raw(admin_url("?post=$post_id"), 'backup-cancel');
	}

	public function url_backup_download($post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 */

		$meta = $this->service('post.meta');
		$meta->set_post_id($post_id);

		if ($a = $meta->get_backup_file()) {
			return $a->get_download_url();
		}

		return false;
	}

	public function url_backup_restore($post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 */

		$meta = $this->service('post.meta');
		$meta->set_post_id($post_id);

		if ($meta->get_backup_file($post_id)) {
			$url = admin_url('edit.php?'.http_build_query(array('post_type' => $this->get_post_type(), 'post' => $post_id)));
			return $this->wp_nonce_url_raw($url, 'backup-restore');
		}

		return false;
	}

	public function url_backup_trash($post_id)
	{
		return $this->wp_nonce_url_raw("post.php?action=trash&amp;post=$post_id", "trash-post_{$post_id}");
	}

	public function url_backup_unschedule($service_id)
	{
		return $this->wp_nonce_url_raw(admin_url('?service='.$service_id), 'backup-unschedule');
	}

	public function backup_now($cron_id)
	{
		/**
		 * @var FW_Backup_Interface_Cron $cron
		 */

		try {
			$cron = $this->service($cron_id, 'FW_Backup_Interface_Cron');

			// Ensure that storage service is workable
			$cron->get_storage()->ping();

			$post_id = $this->backup_create($cron);

			wp_schedule_single_event(time(), 'fw_backup_now', array($post_id));
			wp_cron();
			wp_redirect($this->url_backup_progress($post_id));
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add(uniqid(), $exception->getMessage(), 'error');
			wp_redirect($this->url_backup());
		}

		exit;
	}

	public function backup_cancel($post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 */

		$meta = $this->service('post.meta');
		$meta->set_post_id($post_id);

		if ($time = $meta->get_cron_started()) {
			if ($time + self::BACKUP_TIMEOUT < time()) {
				wp_delete_post($post_id);
				FW_Flash_Messages::add(uniqid(), __('Backup timeout exceeded. The backup was deleted', 'fw'));
				wp_redirect(admin_url('edit.php?post_type='.$this->get_post_type()));
				exit;
			}
		}

		if ($time = $meta->get_cancelled()) {
			if ($time + self::CANCEL_TIMEOUT < time()) {
				wp_delete_post($post_id);
				FW_Flash_Messages::add(uniqid(), __('Cancel timeout exceeded. The backup was deleted', 'fw'));
				wp_redirect(admin_url('edit.php?post_type='.$this->get_post_type()));
				exit;
			}
		}

		$meta->set_cancelled();

		wp_redirect(admin_url('edit.php?post_type='.$this->get_post_type()));
		exit;
	}

	public function backup_unschedule($cron_id)
	{
		/**
		 * @var FW_Backup_Interface_Cron $cron
		 */

		try {
			$cron = $this->service($cron_id, 'FW_Backup_Interface_Cron');
			$this->set_option($cron_id, 'schedule', 'disabled');
			$this->reschedule(); // do reschedule
			// $this->init_services_cron(); // make cron.xxx services to be up to date
			if ($this->debug) {
				FW_Flash_Messages::add(uniqid(), sprintf(__('The <strong>%s</strong> was unscheduled', 'fw'), $cron->get_title()));
			}
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add(uniqid(), $exception->getMessage(), 'error');
		}

		wp_redirect(admin_url('edit.php?post_type='.$this->get_post_type()));
		exit;
	}

	public function backup_render_progress($post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 */

		try {
			$meta = $this->service('post.meta');
			$meta->set_post_id($post_id);
			if ($a = $meta->get_progress()) {
				$a['cron_title'] = $meta->get_cron_title();
				$html = $this->render_view('backend/backup-progress', $a);
			}
		}
		catch (FW_Backup_Exception $exception) {
		}

		return isset($html) ? $html : false;
	}

	public function restore_now($post_id)
	{
		$redirect = true;

		try {
			$redirect = $this->restore_run($post_id);
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add(uniqid(), $exception->getMessage(), 'error');
		}

		if ($redirect) {
			wp_redirect($this->url_backup());
			exit;
		}
	}

	/**
	 * @internal
	 */
	public function _action_fw_extensions_init()
	{
		try {
			$this->init_param();
			$this->init_services();
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add(uniqid(), $exception->getMessage(), 'error');
		}
	}

	/**
	 * @internal
	 */
	public function _action_fw_backup_cron($cron_id)
	{
		try {
			$cron = $this->service($cron_id, 'FW_Backup_Interface_Cron');
			$post_id = $this->backup_create($cron);
			$this->backup_run($post_id);
		}
		catch (FW_Backup_Exception_Service $exception) {
			wp_clear_scheduled_hook('fw_backup_cron', array($cron_id));
		}
		catch (Exception $exception) {
		}
	}

	/**
	 * @internal
	 */
	public function _action_fw_backup_now($post_id)
	{
		try {
			$this->backup_run($post_id);
		}
		catch (Exception $exception) {
		}

		wp_clear_scheduled_hook('fw_backup_now', array($post_id));
	}

	/**
	 * @internal
	 */
	public function _action_trashed_post($post_id)
	{
		// On debug mode use standard WordPress tables/functionality
		if (!$this->debug && get_post_type($post_id) == $this->get_post_type()) {
			wp_delete_post($post_id);
		}
	}

	/**
	 * @internal
	 */
	public function _action_before_delete_post($post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 * @var FW_Backup_Interface_Storage $storage
		 */

		if (get_post_type($post_id) == $this->get_post_type()) {

			// Remove associated backup file, if any
			try {
				$meta = $this->service('shared.post.meta');
				$meta->set_post_id($post_id);
				$storage = $this->service($meta->get_storage_id(), 'FW_Backup_Interface_Storage');
				if ($a = $meta->get_backup_file()) { // take into account when backup was cancelled
					$storage->remove($a);
				}
			}
			catch (FW_Backup_Exception $exception) {
			}

		}
	}

	/**
	 * @internal
	 */
	public function _filter_cron_schedules($schedules)
	{
		// On debug mode use standard WordPress tables/functionality
		if ($this->debug) {
			$schedules['backup.1min'] = array('interval' => MINUTE_IN_SECONDS, 'display' => __('1 min', 'fw'));
			$schedules['backup.4min'] = array('interval' => 4*MINUTE_IN_SECONDS, 'display' => __('4 min', 'fw'));
		}
		$schedules['backup.daily'] = array('interval' => DAY_IN_SECONDS, 'display' => __('Daily', 'fw'));
		$schedules['backup.weekly'] = array('interval' => WEEK_IN_SECONDS, 'display' => __('Weekly', 'fw'));
		$schedules['backup.monthly'] = array('interval' => 4*WEEK_IN_SECONDS, 'display' => __('Monthly', 'fw'));
		return $schedules;
	}

	/**
	 * @internal
	 */
	public function _admin_action_admin_init()
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 */

		try {

			if ($this->wp_verify_nonce('backup-progress')) {
				$meta = $this->service['shared.post.meta'];
				$meta->set_post_id(FW_Request::GET('post'));
				if (!$meta->get_progress()) {
					wp_redirect(admin_url('edit.php?post_type='.$this->get_post_type()));
					exit;
				}
			}

			if ($this->wp_verify_nonce('backup-now')) {
				$this->backup_now(FW_Request::GET('cron'));
			}

			if ($this->wp_verify_nonce('backup-cancel')) {
				$this->backup_cancel(FW_Request::GET('post'));
			}

			if ($this->wp_verify_nonce('backup-restore')) {
				$this->restore_now(FW_Request::GET('post'));
			}

			if ($this->wp_verify_nonce('backup-unschedule')) {
				$this->backup_unschedule(FW_Request::GET('service'));
			}

		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add(uniqid(), $exception->getMessage(), 'error');
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_admin_enqueue_scripts($hook)
	{
		global $post_type;
		if ($hook == 'edit.php' && $post_type == $this->get_post_type()) {

			if ($this->wp_verify_nonce('backup-restore')) {
				wp_enqueue_style(
					"fw-ext-{$this->get_name()}-admin",
					$this->locate_URI('/static/css/admin.css'),
					array(),
					$this->manifest->get_version()
				);
				wp_enqueue_style(
					"fw-ext-{$this->get_name()}-restore",
					$this->locate_URI('/static/css/restore.css'),
					array(),
					$this->manifest->get_version()
				);
				wp_enqueue_script(
					"fw-ext-{$this->get_name()}-restore",
					$this->locate_URI('/static/js/restore.js'),
					array(),
					$this->manifest->get_version()
				);
			}
			else {

				wp_enqueue_media();
				wp_enqueue_style(
					"fw-ext-{$this->get_name()}-admin",
					$this->locate_URI('/static/css/admin.css'),
					array('fw-option-types'),
					$this->manifest->get_version()
				);

				// Enqueue all the necessary files for Backup Schedule dialog
				$options = $this->get_backup_settings_options();
				fw()->backend->render_options($options);

				// On debug mode use standard WordPress tables/functionality
				if (!$this->debug) {
					wp_enqueue_style(
						"fw-ext-{$this->get_name()}-table",
						$this->locate_URI('/static/css/table.css'),
						array(),
						$this->manifest->get_version()
					);
				}

				wp_enqueue_script(
					"fw-ext-{$this->get_name()}-backup",
					$this->locate_URI('/static/js/backup.js'),
					array(),
					$this->manifest->get_version()
				);
				wp_enqueue_script(
					"fw-ext-{$this->get_name()}-settings",
					$this->locate_URI('/static/js/settings.js'),
					array('fw', 'fw-events'),
					$this->manifest->get_version()
				);

				if ($this->wp_verify_nonce('backup-progress')) {
					wp_enqueue_script(
						"fw-ext-{$this->get_name()}-progress",
						$this->locate_URI('/static/js/progress.js'),
						array(),
						$this->manifest->get_version()
					);
				}

			}
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_wp_ajax_backup_progress()
	{
		if ($html = $this->backup_render_progress(FW_Request::POST('post'))) {
			wp_send_json_success($html);
		}

		wp_send_json_error();
	}

	/**
	 * @internal
	 */
	public function _admin_action_wp_ajax_backup_settings_save()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error();
		}

		$options = $this->get_backup_settings_options();
		$values = fw_get_options_values_from_input($options, FW_Request::POST('values'));
		fw_set_db_extension_data($this->get_name(), 'settings', $values);

		$this->reschedule(); // do reschedule
		// $this->init_services_cron(); // make cron.xxx services to be up to date

		wp_send_json_success();
	}

	/**
	 * @internal
	 */
	public function _admin_action_manage_xxx_posts_custom_column($column_name, $post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 */

		// On debug mode use standard WordPress tables/functionality
		if ($this->debug) {
			return;
		}

		if ($column_name == 'description') {
			$meta = $this->service('post.meta');
			$meta->set_post_id($post_id);
?>
			<div style="float: left;">
				<p><input type="radio" name="backup-radio" value="<?php echo $this->url_backup_restore($post_id) ?>" /></p>
			</div>
			<div style="margin-left: 2em;">
				<p><?php echo esc_html($meta->get_post_date()) ?><?php if ($state_title = $meta->get_state_title()): ?>: <?php echo esc_html($state_title) ?><?php endif ?></p>
				<p>
					<?php echo esc_html($meta->get_cron_title()) ?>
					<!-- | Scheduled -->
					<?php if ($backup_file = $meta->get_backup_file()): ?>
						<?php if ($download_url = $backup_file->get_download_url()): ?>
							| <a href="<?php echo esc_attr($download_url) ?>">Download</a>
						<?php endif ?>
					<?php endif ?>
					<?php if ($meta->get_progress()): ?>
						| <a href="<?php echo esc_html($this->url_backup_cancel($post_id)) ?>">Cancel</a>
					<?php else: ?>
						| <a href="<?php echo esc_attr($this->url_backup_trash($post_id)) ?>">Delete</a>
					<?php endif ?>
				</p>
			</div>
<?php
		}
	}

	/**
	 * @internal
	 *
	 * Optimization Note
	 * Service post.meta will be cloned ROW*6 times in the worst case
	 * and ROW*3 time in the best
	 */
	public function _admin_filter_post_row_actions($actions, $post)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 */

		// On debug mode use standard WordPress tables/functionality
		if ($this->debug && get_post_type($post) == $this->get_post_type()) {

			unset($actions['edit']);
			unset($actions['inline hide-if-no-js']);

			$post_id = get_post($post)->ID;

			if ($a = $this->url_backup_download($post_id)) {
				$actions['download'] = sprintf(__('<a href="%s">Download</a>', 'fw'), $a);
			}

			if ($a = $this->url_backup_restore($post_id)) {
				$actions['restore'] = sprintf(__('<a href="%s">Restore</a>', 'fw'), $a);
			}

			$meta = $this->service('post.meta');
			$meta->set_post_id($post_id);
			if ($meta->get_progress()) {
				$actions['progress'] = sprintf(__('<a href="%s">Progress</a>', 'fw'), $this->url_backup_progress($post_id));
				$actions['cancel'] = sprintf(__('<a href="%s">Cancel</a>', 'fw'), $this->url_backup_cancel($post_id));
			}
		}

		return $actions;
	}

	/**
	 * @internal
	 */
	public function _admin_filter_manage_xxx_columns($columns)
	{
		// On debug mode use standard WordPress tables/functionality
		if ($this->debug) {
			return $columns;
		}

		return array(
			'description' => false, // Do not show checkbox on "Screen Options"
		);
	}

	/**
	 * @internal
	 */
	public function _admin_filter_views_xxx_hack($views)
	{
		// NOTE This is a hack
		// Output backup content just before [All (11) | Drafts (22) | Trash (33)]

		if ($this->wp_verify_nonce('backup-restore')) {
			echo $this->render_view('backend/backup-restore', array('request_filesystem_credentials' => $this->request_filesystem_credentials));
		}
		else {
			echo $this->render_view('backend/backup-page');

			// On debug mode use standard WordPress tables/functionality
			if ($this->debug) {
				return $views;
			}
		}

		return array();
	}

	// Internal

	private function init_param()
	{
		$this->param = array();
		$this->param['wordpress_dir'] = realpath(ABSPATH);
		$this->param['backup_dir'] = $this->get_backup_dir();
		$this->param['backup_rel'] = trim(substr($this->param['backup_dir'], strlen($this->param['wordpress_dir'])), '/\\');
	}

	private function init_services()
	{
		$seconds_in_day = 86400;
		$seconds_in_minute = 60;

		// N O T E
		// Service names should not contain underscore character (_), for the reason
		// look at encode_storage_id and decode_storage_id methods.
		$this->service = array();

		// The post.meta service is supposed for temporary access to post meta.
		// It should be used like:
		//
		//    $meta = $this->service('post.meta')
		//    $meta->set_post_id($post_id)
		//    ...
		//
		$this->service['post.meta'] = new FW_Backup_Service_Post_Meta();

		// This services are intentionally shared. They should have the same instance
		// among all of its clients.
		$this->service['shared.post.meta'] = new FW_Backup_Service_Post_Meta();
		$this->service['shared.feedback'] = new FW_Backup_Service_Feedback($this->service['shared.post.meta']);

		$this->service['db'] = new FW_Backup_Service_Database();
		$this->service['fs'] = new FW_Backup_Service_File_System();
		$this->service['ie.settings'] = new FW_Backup_IE_Settings($this->get_name());
		$this->service['ie.history'] = new FW_Backup_IE_History($this->get_post_type());
		$this->service['ie.db'] = new FW_Backup_IE_Database($this->get_post_type(), $this->service('db'), $this->service('shared.feedback'));
		$this->service['ie.fs'] = new FW_Backup_IE_File_System($this->param['wordpress_dir'], $this->param['backup_dir'], $this->service('fs'), $this->service('shared.feedback'));
		$this->service['process.backup-restore'] = new FW_Backup_Process_Backup_Restore($this->param['wordpress_dir'], $this->param['backup_rel'], $this->service('fs'), $this->service('db'), $this->service('ie.fs'), $this->service('ie.db'), $this->service('ie.settings'), $this->service('ie.history'), $this->service('shared.feedback'));
		$this->service['process.apply-age-limit'] = new FW_Backup_Process_Apply_Age_Limit(($this->debug ? $seconds_in_minute : $seconds_in_day), $this->get_post_type(), $this->service('post.meta'), $this->service('shared.feedback'));

		// N O T E
		// One extension can introduce only one service (otherwise how to name this services?)

		// Introduce storage layer
		foreach ($this->get_children() as $child_name => $inst) {
			if ($inst instanceof FW_Backup_Interface_Storage_Factory) {
				$this->service[$child_name] = $inst->create_storage();
				continue;
			}
		}

		// Introduce cron jobs
		foreach ($this->cron as $cron) {
			list ($cron_id, $cron_title, $backup_contents, $backup_now) = $cron;
			$this->service[$cron_id] = $this->create_cron($cron_id, $cron_title, $backup_contents, $backup_now);
		}
	}

	private function create_cron($service_id, $title, array $contents, $backup_now = false)
	{
		$param = $this->get_option($service_id, 'storage');
		$storage_id = $this->decode_storage_id($param['selected']);

		$schedule_id = wp_get_schedule('fw_backup_cron', array($service_id));
		if ($schedule_id) {
			$active = true;
			$schedules = wp_get_schedules();
			$schedule_title = $schedules[$schedule_id]['display'];
			$next_at_title = sprintf(__('Next Backup on %s', 'fw'), date('d.m.Y H:i:s', wp_next_scheduled('fw_backup_cron', array($service_id))));
		}
		else {
			$active = false;
			$schedule_title = $next_at_title = __('Not Scheduled', 'fw');
		}

		$storage = $this->service($storage_id, 'FW_Backup_Interface_Storage');
		if ($storage instanceof FW_Backup_Interface_Multi_Picker_Set && isset($param['values'])) {
			$storage->set_option_values($param['values']);
		}

		return new FW_Backup_Service_Cron($active, $backup_now, $service_id, $title, $schedule_title, $next_at_title, $storage, $storage_id, $contents);
	}

	private function backup_create(FW_Backup_Interface_Cron $cron)
	{
		$post_id = wp_insert_post(array(
				'post_type' => $this->get_post_type(),
				'post_title' => sprintf(__('%s: Initialization...', 'fw'), $cron->get_title()))
		);
		if ($post_id == 0 || $post_id instanceof WP_Error) {
			throw new FW_Backup_Exception(sprintf(__('wp_insert_post(post_type=%s) failed', 'fw'), $this->get_post_type()));
		}

		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 * @var FW_Backup_Service_Feedback $feedback
		 */
		$meta = $this->service('shared.post.meta');
		$meta->set_post_id($post_id);
		$meta->set_cron_id($cron->get_id());
		$meta->set_cron_title($cron->get_title());
		$meta->set_storage_id($cron->get_storage_id());
		$meta->set_backup_contents($cron->get_backup_contents());
		$meta->set_state_title(__('Waiting for start...', 'fw'));

		// this is necessary
		$feedback = $this->service('shared.feedback');
		$feedback->set_task(__('Waiting for start...', 'fw'));

		return $post_id;
	}

	private function backup_run($post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 * @var FW_Backup_Interface_Storage $storage
		 * @var FW_Backup_Process_Backup_Restore $backup_restore
		 * @var FW_Backup_Process_Apply_Age_Limit $apply_age_limit
		 */

		set_time_limit(0);
		$meta = $this->service('shared.post.meta');
		$meta->set_post_id($post_id);

		try {
			$meta->set_cron_started();

			$storage = $this->service($meta->get_storage_id(), 'FW_Backup_Interface_Storage');
			$backup_restore = $this->service('process.backup-restore', 'FW_Backup_Process_Backup_Restore');
			$apply_age_limit = $this->service('process.apply-age-limit', 'FW_Backup_Process_Apply_Age_Limit');

			// 1) Ensure that storage service is workable
			$storage->ping();

			// A way to exclude file system from backup
			if (!in_array('fs', $meta->get_backup_contents())) {
				$backup_restore->set_ie_fs(new FW_Backup_IE_File_System_Void());
			}

			// 2) Do backup
			$backup_file = $backup_restore->backup($storage);

			// 3) Record the time it was completed
			$this->set_option($meta->get_cron_id(), 'completed_at', time());

			$meta->set_state_title(__('Complete', 'fw'), true);
			$meta->set_backup_file($backup_file);

			// 4) Remove obsolete backup files of the same type (i.e. "Full Backup" or "Database Backup")
			$apply_age_limit->run($meta->get_cron_id(), $this->get_option($meta->get_cron_id(), 'lifetime'));
		}
		catch (FW_Backup_Exception_Cancelled $exception) {
			$meta->set_state_title(__('Cancelled', 'fw'));
		}
		catch (FW_Backup_Exception $exception) {
			$meta->set_state_title(__('Failed', 'fw'));
		}

		$meta->publish(isset($exception) ? $exception->getMessage() : null);
	}

	private function restore_run($post_id)
	{
		/**
		 * @var FW_Backup_Service_Post_Meta $meta
		 * @var FW_Backup_Interface_Storage $storage
		 * @var FW_Backup_Process_Backup_Restore $backup_restore
		 * @var WP_Filesystem_Base $wp_filesystem
		 */

		global $wp_filesystem;

		ob_start();
		$credentials = request_filesystem_credentials(fw_current_url(), '', false, false, null);
		$this->request_filesystem_credentials = ob_get_clean();
		if ($credentials) {
			if (!WP_Filesystem($credentials)) {
				ob_start();
				request_filesystem_credentials(fw_current_url(), '', false, false, null);
				$this->request_filesystem_credentials = ob_get_clean();
			}
		}

		if ($this->request_filesystem_credentials) {
			return false;
		}

		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			return false;
		}

//		ob_start();
//		fw_print('Let\'s start the party!!!');
//		$this->request_filesystem_credentials = ob_get_clean();
//		return;

		set_time_limit(0);
		$meta = $this->service('shared.post.meta');
		$meta->set_post_id($post_id);

		$storage = $this->service($meta->get_storage_id(), 'FW_Backup_Interface_Storage');
		$backup_restore = $this->service('process.backup-restore', 'FW_Backup_Process_Backup_Restore');

		// 1) Do we have enough permissions for restoring file system / database?
		//    If no there is no need for fetching archive.

		if (in_array('fs', $meta->get_backup_contents())) {
			$backup_restore->check_permissions_fs();
		}
		if (in_array('db', $meta->get_backup_contents())) {
			$backup_restore->check_permissions_db();
		}

		// 2) Do restore
		$backup_restore->restore($storage, $meta->get_backup_file());

		// 3) Actualize settings
		$this->reschedule();

		FW_Flash_Messages::add(uniqid(), __('The site was restored from backup', 'fw'));

		return true;
	}

	/**
	 * @internal
	 */
	public function get_backup_settings_options()
	{
		/**
		 * @var FW_Backup_Interface_Cron $cron
		 * @var FW_Backup_Interface_Storage $storage
		 */

		if (empty($this->service)) {
			throw new FW_Backup_Exception(__('No Services: Did init_services was called?', 'fw'));
		}

		$schedule_default = 'disabled';
		$schedule_choices = array(
			'disabled' => __('Disabled', 'fw'),
		);
		foreach (wp_get_schedules() as $id => $schedule) {
			if (strpos($id, 'backup.') === 0) {
				$schedule_choices[$id] = $schedule['display'];
			}
		}

		$storage_default = null;
		$storage_choices = array();
		$storage_multi_picker_sets = array();
		foreach ($this->service_list('FW_Backup_Interface_Storage') as $service_id => $storage) {
			$storage_choices[$this->encode_storage_id($service_id)] = $storage->get_title();
			// default storage layer is Local
			if ($storage instanceof FW_Backup_Storage_Local) {
				$storage_default = $this->encode_storage_id($service_id);
			}
			if ($storage instanceof FW_Backup_Interface_Multi_Picker_Set) {
				/**
				 * @var FW_Backup_Interface_Multi_Picker_Set $storage
				 */
				$storage_multi_picker_sets[$this->encode_storage_id($service_id)] = $storage->get_multi_picker_set();
			}
		}

		$options = array();
		foreach ($this->cron as $cron) {

			list ($cron_id, $cron_title) = $cron;

            if ($this->debug) {
                $desc = __('Age limit of backups in minutes', 'fw');
            }
            else {
                $desc = __('Age limit of backups in days', 'fw');
            }

            $attr = array(
                'type' => 'text',
                'name' => 'fw_options[' . $this->get_option_name($cron_id, 'lifetime') . ']',
                'value' => $this->get_option($cron_id, 'lifetime'),
                'id' => 'fw-option-' . $this->get_option_name($cron_id, 'lifetime'),
                'placeholder' => __('No Limit', 'fw'),
                'class' => 'fw-option fw-option-type-text',
                'style' => 'width: 90%',
            );
            $html = fw_html_tag('input', $attr, '&nbsp;<b>' . ($this->debug ? __('Minutes', 'fw') : __('Days', 'fw')) . '</b>');

			$options[] = array(
				'type' => 'tab',
				'title' => $cron_title,
				'attr' => array('data-container' => 'backup-settings'),
				'options' => array(
					$this->get_option_name($cron_id, 'completed_at') => array(
						'type' => 'hidden',
						'value' => 0,
					),
					$this->get_option_name($cron_id, 'schedule') => array(
						'type' => 'select',
						'attr' => array('data-type' => 'backup-schedule'),
						'label' => __('Backup Interval', 'fw'),
						'desc' => __('Select how often do you want to backup your website.', 'fw'),
						'value' => $schedule_default,
						'choices' => $schedule_choices,
					),
					'group' => array(
						'type' => 'group',
						'attr' => array('class' => 'hide-if-disabled'),
						'options' => array(
							$this->get_option_name($cron_id, 'storage') => array(
								'type' => 'multi-picker',
								'label' => false,
								'desc' => false,
								'attr' => array('class' => 'hidden'),
								'value' => array(
									'selected' => $storage_default,
								),
								'picker' => array(
									'selected' => array(
										'type' => 'select',
										'label' => __('Backup On', 'fw'),
										'desc' => __('Select where do you want your backup to be saved', 'fw'),
										'choices' => $storage_choices,
									),
								),
								'sets' => $storage_multi_picker_sets,
							),
							$this->get_option_name($cron_id, 'lifetime') => array(
								'type' => 'html-fixed',
								'label' => __('Backup Age Limit', 'fw'),
								'desc' => $desc,
								'html' => $html,
								'value' => '',
							),
						),
					),
				),
			);
		}

		return $options;
	}

	/**
	 * @internal
	 */
	public function get_backup_settings_values()
	{
		return fw_get_db_extension_data($this->get_name(), 'settings');
	}

	private function set_backup_settings_values()
	{
	}

	private function get_option($service_id, $key)
	{
		static $is_getting_option = false;

		if ($is_getting_option) {
			// prevent recursion
			return false;
		}

		$is_getting_option = true;

		$values = fw_get_db_extension_data($this->get_name(), 'settings');

		if (empty($values)) {
			/**
			 * Settings options was never saved to database.
			 * Emulate save with default values.
			 *
			 * So on next refresh we will not need to call again
			 *  fw_get_options_values_from_input( $this->get_backup_settings_options(), array() )
			 *  I think it's resource consuming to call that on every refresh
			 */

			$options = $this->get_backup_settings_options();
			$values  = fw_get_options_values_from_input($options, array());

			fw_set_db_extension_data($this->get_name(), 'settings', $values);
		}

		$option_name = $this->get_option_name($service_id, $key);

		$is_getting_option = false;

		if (isset($values[$option_name])) {
			return $values[$option_name];
		} else {
			return false;
		}
	}

	private function get_option_name($service_id, $key)
	{
		return strtr("$service_id-$key", '.', '-');
	}

	private function set_option($service_id, $key, $value)
	{
		$option_name = $this->get_option_name($service_id, $key);

		$settings = fw_get_db_extension_data($this->get_name(), 'settings');
		$settings[$option_name] = $value;
		fw_set_db_extension_data($this->get_name(), 'settings', $settings);
	}

	// option type multi-picker does not work when values in SELECT contains dot (.)
	// i think that's because items in "sets" are selected by #<value>. if <value> will
	// contain dot (.) that will treat as #xxx.yyy i.e. element with id #xxx and class .yyy
	private function encode_storage_id($value)
	{
		return strtr($value, '.', '_');
	}

	private function decode_storage_id($value)
	{
		return strtr($value, '_', '.');
	}

	/**
	 * Reschedule all cron jobs
	 */
	private function reschedule()
	{
		/**
		 * N O T E
		 * If cron previously registered was not in here it will not be unscheduled now,
		 * but next time it will be fired.
		 */
		foreach (array_keys($this->service_list('FW_Backup_Interface_Cron')) as $cron_id) {
			wp_clear_scheduled_hook('fw_backup_cron', array($cron_id));
			$schedule_id = $this->get_option($cron_id, 'schedule');
			$schedules = wp_get_schedules();
			if (isset($schedules[$schedule_id])) {
				$schedule_interval = $schedules[$schedule_id]['interval'];
				$completed_at = $this->get_option($cron_id, 'completed_at');
				// First time backup should be made right now. Then each time an interval was passed.
				wp_schedule_event(max($completed_at + $schedule_interval, time()), $schedule_id, 'fw_backup_cron', array($cron_id));
			}
		}
	}

	// Helpers

	/**
	 * @internal
	 */
	public function wp_verify_nonce($action)
	{
		$nonce = FW_Request::GET('_wpnonce');
		return $nonce && wp_verify_nonce($nonce, $action);
	}

	/**
	 * Version of wp_nonce_url() without esc_html()
	 */
	private function wp_nonce_url_raw($action_url, $action = -1, $name = '_wpnonce')
	{
		$action_url = str_replace('&amp;', '&', $action_url);
		return add_query_arg($name, wp_create_nonce($action), $action_url);
	}
}
