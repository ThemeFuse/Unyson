<?php if (!defined('FW')) die('Forbidden');

/**
 * @var $subject
 */

/**
 * @var FW_Extension_Backup $backup
 */
$backup = fw()->extensions->get('backup');

if ($feedback = $backup->get_feedback($subject)) {

	$cron = __('N/A', 'fw');
	if ($backup_info = $backup->get_backup_info($subject)) {
		if ($backup_info->is_demo_install()) {
			$cron = __('Demo Install', 'fw');
		}
		else {
			try {
				$cron = $backup->cron()->get_cron_job($backup_info->get_cron_job())->get_title();
			}
			catch (FW_Backup_Exception $exception) {
			}
		}
	}

	$task = $feedback->get_task();

	if ($feedback->get_size() && is_numeric($feedback->get_progress())) {
		$progress = number_format($feedback->get_progress() / $feedback->get_size() * 100, 2) . '%';
	}
	else {
		$progress = $feedback->get_progress();
	}

	if ($feedback->get_description()) {
		$progress = $feedback->get_description() . ' [' . $progress . ']';
	}

}
else {
	$cron = $task = $progress = __('N/A', 'fw');
}

?>
<div class="backup-alert updated border-orange below-h2">
	<p>
		<i class="spinner backup-spinner"></i>
		<strong><?php echo esc_html($cron) ?></strong>:
		<?php
			echo esc_html($task);
			if ($progress) {
				if (substr($task, -3) == '...') {
					echo ' ';
				}
				else {
					echo ': ';
				}
				echo esc_html($progress);
			}
		?>
	</p>
</div>
