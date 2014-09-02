<?php if (!defined('FW')) die('Forbidden');

/**
 * @var $cron_title
 * @var $task_title
 * @var $task_progress
 * @var $task_progress_title
 */

?>
<div class="backup-alert updated border-orange below-h2">
	<p>
		<i class="spinner backup-spinner"></i>
		<strong><?php echo esc_html($cron_title) ?></strong>:
		<?php
			echo esc_html($task_title);
			if ($task_progress_title) {
				echo ': ', esc_html($task_progress_title);
			}
			if ($task_progress) {
				echo ' (', esc_html($task_progress), ')';
			}
		?>
	</p>
</div>
