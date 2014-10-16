<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Post_Type
{
	public function __construct()
	{
		$this->add_actions();

		if (is_admin()) {
			$this->add_admin_actions();
			$this->add_admin_filters();
		}
	}





	public function get_post_type()
	{
		return 'fw_backup';
	}

	/**
	 * @param array $param
	 * @return WP_Post[]
	 */
	public function get_posts($param = array())
	{
		return get_posts(array_merge(array('post_type' => $this->get_post_type()), $param));
	}

	public function foreach_post($callable, $param = array())
	{
		$index = 0;
		$offset = 0;
		$posts_per_page = 200;

		while (true) {
			$post_list = $this->get_posts(array_merge($param, compact('offset', 'posts_per_page')));
			if (empty($post_list)) {
				break;
			}
			foreach ($post_list as $post) {
				call_user_func($callable, $post, $index++);
			}
			$offset += count($post_list);
		}
	}

	public function get_url($param = array(), $prefix = 'edit.php')
	{
		return admin_url("$prefix?" . http_build_query(array_merge(array('post_type' => $this->get_post_type()), $param)));
	}

	public function insert($post = array())
	{
		return wp_insert_post(array_merge(array('post_type' => $this->get_post_type()), $post));
	}

	public function count()
	{
		return wp_count_posts($this->get_post_type());
	}





	private function add_actions()
	{
		add_action('init', array($this, '_action_init'));
		add_action('trashed_post', array($this, '_action_trashed_post'));
		add_action('before_delete_post', array($this, '_action_before_delete_post'));
	}

	private function add_admin_actions()
	{
		$post_type = $this->get_post_type();

		add_action('admin_init', array($this, '_admin_action_admin_init'));
		add_action('current_screen', array($this, '_admin_action_current_screen'));
		add_action('admin_enqueue_scripts', array($this, '_admin_action_admin_enqueue_scripts'));
		add_action("manage_{$post_type}_posts_custom_column", array($this, '_admin_action_manage_xxx_posts_custom_column'), 10, 2);
	}

	private function add_admin_filters()
	{
		$screen_id = 'edit-' . $this->get_post_type();

		add_filter('post_row_actions', array($this, '_admin_filter_post_row_actions'), 10, 2);
		add_filter("views_{$screen_id}", array($this, '_admin_filter_views_xxx_hack'));
		add_filter("manage_{$screen_id}_columns", array($this, '_admin_filter_manage_xxx_columns'));
	}

	/**
	 * @internal
	 */
	public function _action_init()
	{
		register_post_type($this->get_post_type(), array(
			'labels' => array(
				'name'               => __('Backups', 'fw'),
				'singular_name'      => __('Backup', 'fw'),
				'add_new'            => __('Add New', 'fw'),
				'add_new_item'       => __('Add New Backup', 'fw'),
				'edit_item'          => __('Edit Backup', 'fw'),
				'new_item'           => __('New Backup', 'fw'),
				'all_items'          => __('Backup', 'fw'), // __('All Backups', 'fw'),
				'view_item'          => __('View Backup', 'fw'),
				'search_items'       => __('Search Backups', 'fw'),
				'not_found'          => __('No Backup Archive has been created yet', 'fw'),
				'not_found_in_trash' => __('Nothing found in Trash', 'fw'),
				'parent_item_colon'  => ''
			),
			'public'                => false,
			'publicly_queryable'    => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'show_in_menu'          => 'tools.php',

			// WordPress: Disable “Add New” on Custom Post Type
			// http://stackoverflow.com/a/16675677
			'capability_type' => 'post',
			'capabilities' => array(
				'create_posts' => false,
				'read' => 'edit_files',
				'read_post' => 'edit_files',
				'edit_post' => 'edit_files',
				'delete_post' => 'edit_files',
				'edit_posts' => 'edit_files',
				'delete_posts' => 'edit_files',
				'publish_posts' => 'edit_files',
			),
			'map_meta_cap' => true,
		));
	}

	/**
	 * @internal
	 */
	public function _admin_action_current_screen(WP_Screen $current_screen)
	{
		if ($current_screen->post_type == $this->get_post_type() && $current_screen->base == 'edit') {
			// Scan $backup_dir only when **Backup** page were open for viewing.
			// Previously was in _admin_action_admin_enqueue_scripts, but this was
			// too late. Only posts already in database was displayed.
			if (!$this->backup()->action()->get_feedback_subject() && !$this->backup()->action()->is_backup_restore()) {
				$process = new FW_Backup_Process_Scan_Backup_Directory();
				if ($a = $process->run()) {
					FW_Flash_Messages::add('scan-backup-dir', $a, 'success');
				}
			}
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_admin_init()
	{
		// Get rid of "1 post moved to the Trash"
		if (!$this->backup()->debug) {
			if (isset($_GET['post_type']) && $_GET['post_type'] == $this->get_post_type() && isset($_GET['trashed'])) {
				wp_redirect($this->get_url());
				exit;
			}
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_admin_enqueue_scripts($hook)
	{
		global $post_type;

		if ($hook == 'edit.php' && $post_type == $this->get_post_type()) {

			$d = $this->backup()->get_declared_URI();
			$v = $this->backup()->manifest->get_version();

			if (!$this->backup()->debug) {
				wp_enqueue_style('backup-post-type', "$d/static/css/post-type.css", array(), $v);
			}

		}
	}

	/**
	 * @internal
	 *
	 * @var $post_id
	 */
	public function _action_trashed_post($post_id)
	{
		if (get_post_type($post_id) != $this->get_post_type()) {
			return;
		}

		if (!$this->backup()->debug) {
			wp_delete_post($post_id);
		}
	}

	/**
	 * @internal
	 *
	 * @var $post_id
	 */
	public function _action_before_delete_post($post_id)
	{
		if (get_post_type($post_id) != $this->get_post_type()) {
			return;
		}

		try {
			if ($backup_info = $this->backup()->get_backup_info($post_id)) {
				if ($backup_info->is_completed()) {
					$storage = fw()->extensions->get($backup_info->get_storage());
					if ($storage instanceof FW_Backup_Interface_Storage) {
						// Initialize storage layer
						$storage_options = $this->backup()->settings()->get_cron_storage_options($backup_info->get_cron_job(), $backup_info->get_storage());
						$storage->set_storage_options($storage_options);
						// Remove file from persistent storage
						$storage->remove($backup_info->get_storage_file(), new FW_Backup_Feedback_Void());
					}
				}
			}
		}
		catch (FW_Backup_Exception $exception) {
			FW_Flash_Messages::add("backup-remove-$post_id", $exception->getMessage(), 'error');
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_manage_xxx_posts_custom_column($column_name, $post_id)
	{
		if ($this->backup()->debug) {
			return;
		}

		if ($column_name == 'description') {
			$this->backup()->render('table-cell-description', compact('post_id'));
		}
	}

	/**
	 * @internal
	 */
	public function _admin_filter_post_row_actions($actions, $post)
	{
		if ($this->backup()->debug && get_post_type($post) == $this->get_post_type()) {

			unset($actions['edit']);
			unset($actions['inline hide-if-no-js']);

			$post_id = get_post($post)->ID;

			if ($href = $this->backup()->action()->url_feedback($post_id)) {
				$actions['feedback'] = fw_html_tag('a', compact('href'), __('Watch', 'fw'));
			}

			if ($href = $this->backup()->action()->url_backup_cancel($post_id)) {
				$actions['cancel'] = fw_html_tag('a', compact('href'), __('Cancel', 'fw'));
			}

			if ($href = $this->backup()->action()->url_backup_download($post_id)) {
				$actions['download'] = fw_html_tag('a', compact('href'), __('Download', 'fw'));
			}

			if ($href = $this->backup()->action()->url_backup_restore($post_id)) {
				$actions['restore'] = fw_html_tag('a', compact('href'), __('Restore', 'fw'));
			}
		}

		return $actions;
	}

	/**
	 * @internal
	 *
	 * @var $views
	 * @return array
	 */
	public function _admin_filter_views_xxx_hack($views)
	{
		// NOTE This is a hack
		// Output backup content just before [All (11) | Drafts (22) | Trash (33)]

		if ($this->backup()->action()->is_backup_restore()) {
			$this->backup()->render('restore');
		}
		else {
			$this->backup()->render('page');
		}

		if ($this->backup()->debug) {
			return $views;
		}

		return array();
	}

	/**
	 * @internal
	 *
	 * @var $columns
	 * @return array
	 */
	public function _admin_filter_manage_xxx_columns($columns)
	{
		if ($this->backup()->debug) {
			return $columns;
		}

		return array(
			'description' => false, // Do not show checkbox on "Screen Options"
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
