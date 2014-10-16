<?php if (!defined('FW')) die('Forbidden');

/**
 * @var $post_id
 * @var FW_Extension_Backup $backup
 */

$backup = fw()->extensions->get('backup');

$time = $status = $cron = $storage = __('N/A', 'fw');
$action_list = array();

$backup_info = $backup->get_backup_info($post_id);

if (!$backup_info) {
	$backup_info = new FW_Backup_Info();
	$backup_info->set_failed_at(time());
	$backup_info->set_finished_at(time());
}

	try {
		$cron = $backup->cron()->get_cron_job($backup_info->get_cron_job())->get_title();
	}
	catch (FW_Backup_Exception $exception) {
	}

	try {
		$storage = $backup->get_storage($backup_info->get_storage())->get_title('on');
	}
	catch (FW_Backup_Exception $exception) {
	}

	if ($backup_info->get_theme()) {
		$cron = __('Demo Install', 'fw');
	}

	if ($backup_info->is_imported()) {
		$status = __('Imported', 'fw');
		$time = $backup_info->get_storage_file_time();
	}
	elseif ($backup_info->is_completed()) {
		$status = false;
		$time = $backup_info->get_storage_file_time();
	}
	elseif ($backup_info->is_cancelled()) {
		if ($backup_info->is_finished()) {
			$status = __('Cancelled', 'fw');
		}
		else {
			$status = __('Cancelling', 'fw');
		}
		$time = $backup_info->get_cancelled_at();
	}
	elseif ($backup_info->is_failed()) {
		$status = __('Failed', 'fw');
		$time = $backup_info->get_failed_at();
	}
	elseif ($backup_info->is_started()) {
		$status = __('Running', 'fw');
		$time = time();
	}
	elseif ($backup_info->is_queued()) {
		$status = __('Queued', 'fw');
		$time = $backup_info->get_queued_at();
	}

	if ($href = $backup->action()->url_backup_download($post_id)) {
		$action_list[] = fw_html_tag('a', compact('href'), __('Download', 'fw'));
	}

	if ($href = $backup->action()->url_backup_cancel($post_id)) {
		$action_list[] = fw_html_tag('a', compact('href'), __('Cancel', 'fw'));
	}

	if ($backup_info->is_finished()) {
		$action_list[] = fw_html_tag('a', array('href' => $backup->action()->url_backup_trash($post_id)), __('Delete', 'fw'));
	}

?>
<div style="float: left;">
	<p><input type="radio" name="backup-radio" value="<?php echo esc_attr($backup->action()->url_backup_restore($post_id)) ?>" /></p>
</div>
<div style="margin-left: 2em;">
	<p>
		<?php
			echo $backup->format()->format_date_time_gmt($time);
			if ($status) {
				echo ': ', esc_html($status);
			}
		?>
	</p>
	<p>
		<?php echo implode(' | ', array_merge(array(esc_html($cron), esc_html($storage)), $action_list)) ?>
	</p>
</div>
