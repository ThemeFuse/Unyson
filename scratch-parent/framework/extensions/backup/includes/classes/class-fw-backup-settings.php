<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Settings
{
	private $storage_list;

	/**
	 * @param FW_Backup_Interface_Storage[] $storage_list
	 */
	public function __construct($storage_list)
    {
	    $this->storage_list = $storage_list;

        if (is_admin()) {
            $this->add_admin_actions();
        }
    }

	public function save($values)
	{
		foreach ($this->backup()->cron()->get_cron_job_list() as $cron_job) {
			$cron_id = $cron_job->get_id();
			$storage_choices = $values["$cron_id-storage"];
			foreach ($this->storage_list as $storage) {
				if (isset($storage_choices[$storage->get_name()])) {
					$storage_options = $storage_choices[$storage->get_name()];
					// Get a chance for a storage layer to exchange auth code to access token
					$storage_options = $storage->before_save_storage_options($storage_options);;
					$values["$cron_id-storage"][$storage->get_name()] = $storage_options;
				}
			}
		}

		$this->set_values($values);

		wp_send_json_success();
	}

    private function add_admin_actions()
    {
        add_action('admin_enqueue_scripts', array($this, '_admin_action_admin_enqueue_scripts'));
    }

    /**
     * @internal
     *
     * @var $hook
     */
    public function _admin_action_admin_enqueue_scripts($hook)
    {
        global $post_type;

        if ($hook == 'edit.php' && $post_type == $this->backup()->post_type()->get_post_type()) {

	        $d = $this->backup()->get_declared_URI();
	        $v = $this->backup()->manifest->get_version();

            // Enqueue all the necessary files for Backup Schedule dialog
            $options = $this->get_options();
            fw()->backend->enqueue_options_static($options);

	        wp_enqueue_script('backup-settings', "$d/static/js/settings.js", array('jquery'), $v);
	        wp_enqueue_style('backup-settings', "$d/static/css/settings.css", array(), $v);
	        wp_localize_script('backup-settings', 'backup_settings_i10n', array('title' => __('Backup Schedule', 'fw'), 'options' => $options, 'values' => $this->get_values()));

        }
    }

	private function set_values($values, $reschedule = true)
	{
		fw_set_db_extension_data($this->backup()->get_name(), 'settings', $values);

		if ($reschedule) {
			$this->backup()->cron()->reschedule();
		}
	}

	private function get_values()
	{
		return (array) fw_get_db_extension_data($this->backup()->get_name(), 'settings');
	}

	public function get_cron_completed_at($cron_id)
	{
		$values = $this->get_values();

		if (isset($values["$cron_id-completed_at"])) {
			return $values["$cron_id-completed_at"];
		}

		return 0;
	}

	public function set_cron_completed_at($cron_id, $completed_at)
	{
		$values = $this->get_values();
		$values["$cron_id-completed_at"] = $completed_at;
		$this->set_values($values, false);
	}

	public function set_cron_schedule($cron_id, $schedule)
	{
		$values = $this->get_values();
		$values["$cron_id-schedule"] = $schedule;
		$this->set_values($values);
	}

	public function get_cron_schedule($cron_id)
	{
		$values = $this->get_values();

		if (isset($values["$cron_id-schedule"])) {
			return $values["$cron_id-schedule"];
		}

		return 'disabled';
	}

	public function get_cron_lifetime($cron_id)
	{
		$values = $this->get_values();

		if (isset($values["$cron_id-lifetime"])) {
			return $values["$cron_id-lifetime"];
		}

		return '';
	}

	public function get_cron_storage($cron_id)
	{
		$values = $this->get_values();

		if (isset($values["$cron_id-storage"]) && isset($values["$cron_id-storage"]['selected'])) {
			$storage_id = $values["$cron_id-storage"]['selected'];
			$storage = fw()->extensions->get($storage_id);
			if ($storage instanceof FW_Backup_Interface_Storage) {
				return $storage_id;
			}
		}

		return 'backup-storage-local';
	}

	public function get_cron_storage_options($cron_id, $storage_id)
	{
		$values = $this->get_values();
		return (array) @$values["$cron_id-storage"][$storage_id];
	}

    public function get_options()
    {
        $schedule_choices = $this->backup()->cron()->get_schedule_list();
	    reset($schedule_choices);
		$schedule_default = key($schedule_choices);

//	    $options = array(
//		    $this->debug_tab('full', __('Full Backup', 'fw')),
//		    $this->debug_tab('database', __('Database Backup', 'fw')),
//		    $this->debug_tab('other', __('Other', 'fw')),
//	    );
//
//	    return $options;

	    $a = array();
	    foreach ($this->backup()->cron()->get_cron_job_list() as $cron_job) {
		    $a[] = $this->options_tab(
			    $cron_job->get_id(),
			    $cron_job->get_title(),
			    $schedule_default,
			    $schedule_choices
		    );
	    }

	    $options = call_user_func_array('array_merge', $a);
        return $options;
    }

	private function get_storage_choices($cron_id)
	{
		$storage_default = null;
		$storage_choices = array();
		$storage_choices_labels = array();
		foreach ($this->storage_list as $storage) {

			// set storage options
			$storage_options = $this->get_cron_storage_options($cron_id, $storage->get_name());
			$storage->set_storage_options($storage_options);

			$storage_choices[$storage->get_name()] = $storage->get_storage_options();
			$storage_choices_labels[$storage->get_name()] = $storage->get_title();
			// default storage layer is first in the list
			if ($storage_default === null) {
				$storage_default = $storage->get_name();
			}
			// unless there is FW_Extension_Storage_Local. In that case make it the default.
			if ($storage instanceof FW_Extension_Backup_Storage_Local) {
				$storage_default = $storage->get_name();
			}
		}

		return array($storage_default, $storage_choices, $storage_choices_labels);
	}

	private function options_tab($cron_id, $cron_title, $schedule_default, $schedule_choices)
	{
		if ($this->backup()->debug) {
			$description = __('Age limit of backups in minutes', 'fw');
			$granularity = __('&nbsp;<b>Minutes</b>', 'fw');
		}
		else {
			$description = __('Age limit of backups in days', 'fw');
			$granularity = __('&nbsp;<b>Days</b>', 'fw');
		}

		list ($storage_default, $storage_choices, $storage_choices_labels) = $this->get_storage_choices($cron_id);

		$a = array(
			$cron_id => array(
				'type' => 'tab',
				'title' => $cron_title,
				'attr' => array('data-container' => 'backup-settings'),
				'options' => array(
					"$cron_id-completed_at" => array(
						'type' => 'hidden',
						'value' => 0,
					),
					"$cron_id-schedule" => array(
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
							"$cron_id-storage" => array(
								'type' => 'multi-picker',
								'attr' => array('class' => (count($storage_choices) == 1 ? 'hidden' : '')),
								'label' => false,
								'desc' => false,
								'value' => array(
									'selected' => $storage_default,
								),
								'picker' => array(
									'selected' => array(
										'type' => 'select',
										'label' => __('Backup On', 'fw'),
										'desc' => __('Select where do you want your backup to be saved', 'fw'),
										'choices' => $storage_choices_labels,
									),
								),
								'choices' => $storage_choices,
							),
							"$cron_id-lifetime" => array(
								'type' => 'text',
								'label' => __('Backup Age Limit', 'fw'),
								'desc' => $description,
								'attr' => array(
									'style' => 'width: 90%;',
									'placeholder' => __('No Limit', 'fw'),
									'data-html-after' => $granularity,
								),
							),
						),
					),
				),
			),
		);

		return $a;
	}

	private function debug_tab($cron_id, $title)
	{
		return array(
			'type' => 'tab',
			'title' => $title,
			'attr' => array('data-container' => 'backup-settings'),
			'options' => array(
				"$cron_id-completed_at" => array(
					'type' => 'hidden',
					'value' => 0,
				),
				"$cron_id-schedule" => array(
					'type' => 'select',
					'attr' => array('data-type' => 'backup-schedule'),
					'label' => __('Backup Interval', 'fw'),
					'desc' => __('Select how often do you want to backup your website.', 'fw'),
					'value' => 'disabled',
					'choices' => array(
						'disabled' => __('Disabled', 'fw'),
						'daily' => __('Daily', 'fw'),
						'weekly' => __('Weekly', 'fw'),
						'monthly' => __('Monthly', 'fw'),
					),
				),
				'group' => array(
					'type' => 'group',
					'attr' => array('class' => 'hide-if-disabled'),
					'options' => array(
						"$cron_id-storage" => array(
							'type' => 'multi-picker',
							'label' => false,
							'desc' => false,
							'value' => array(
								'selected' => 'local',
							),
							'picker' => array(
								'selected' => array(
									'type' => 'select',
									'label' => __('Backup On', 'fw'),
									'desc' => __('Select where do you want your backup to be saved', 'fw'),
									'choices' => array(
										'local' => __('Locally', 'fw'),
										'dropbox' => __('Dropbox', 'fw'),
										'google_drive' => __('Google Drive', 'fw'),
									),
								),
							),
							'choices' => array(
								'local' => array(),
								'dropbox' => array(
									'button' => array(
										'type' => 'html',
										'label' => __('Dropbox', 'fw'),
										'desc' => __('1) Opens another window. Click Allow in the new window (you may need to login to Dropbox.com first).', 'fw'),
										'html' => '<button data-action="backup-dropbox-authorize" data-uri="#" class="button button-primary">Connect to Dropbox &amp; Authorize</button>',
									),
									'auth_code' => array(
										'type' => 'text',
										'label' => __('Authorization Code', 'fw'),
										'desc' => __('2) Enter the provided Authorization Code here', 'fw'),
										'attr' => array('data-container' => 'dropbox-auth'),
									),
									'disconnect' => array(
										'type' => 'html',
										'label' => __('Dropbox', 'fw'),
										'html' => '<button data-action="backup-dropbox-disconnect" class="button button-primary">Disconnect</button>',
									),
								),
								'google_drive' => array(),
							),
						),
						"$cron_id-lifetime" => array(
							'type' => 'text',
							'label' => __('Backup Age Limit', 'fw'),
							'desc' => __('Age limit of backups in days', 'fw'),
							'attr' => array(
								'placeholder' => __('No Limit', 'fw'),
								'style' => 'width: 90%;',
								'data-html-after' => __('&nbsp;<b>Days</b>', 'fw'),
							),
						),
					),
				),
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
