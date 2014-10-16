<?php if (!defined('FW')) die('Forbidden');

/**
 * @var FW_Extension_Backup $backup
 * @var FW_Backup_Cron_Job $cron_job
 */

$backup = fw()->extensions->get('backup');
$post_type = get_post_type_object($backup->post_type()->get_post_type());

$active = array();
$inactive = array();
$backup_now = array();

foreach ($backup->cron()->get_cron_job_list() as $cron_job) {
	if ($cron_job->is_active()) {
		$active[] = $cron_job;
	}
	else {
		$inactive[] = $cron_job;
	}
	if ($cron_job->get_backup_now()) {
		$backup_now[] = $cron_job;
	}
}

?>

<p class="backup-subtitle description" id="backup-subtitle">Here you can create a backup schedule for your website.</p>

<div id="backup-container">

	<?php if ($subject = $backup->action()->get_feedback_subject()): ?>
		<div id="backup-feedback-container" data-subject="<?php echo esc_attr($subject) ?>">
			<?php $backup->render('feedback', compact('subject')) ?>
		</div>
	<?php endif ?>

	<?php if (count($active) == 0): ?>

		<div class="backup-alert error below-h2">
			<p><strong>Important:</strong> No backup schedule created yet! We advise you to do it asap!</p>
		</div>

	<?php else: ?>

		<?php foreach ($inactive as $cron_job): ?>
			<div class="backup-alert error below-h2">
				<p><strong>Important:</strong> No <em><?php echo esc_html($cron_job->get_title()) ?></em> schedule created yet!</p>
			</div>
		<?php endforeach ?>

		<?php foreach ($active as $cron_job): ?>
			<div class="backup-alert updated below-h2">
				<p>
					<a href="<?php echo esc_html($backup->action()->url_backup_unschedule($cron_job->get_id())) ?>" class="backup-icon-remove"></a>
					<strong><?php echo esc_html($cron_job->get_title()) ?> schedule active:</strong>
					<?php echo $cron_job->get_schedule_title() ?> |
					<?php echo $backup->get_storage($cron_job->get_storage(), $cron_job->get_id())->get_title('on') ?> |
					<?php echo $cron_job->get_next_at_title() ?>
				</p>
			</div>
		<?php endforeach ?>

	<?php endif ?>

	<div class="backup-controls">

		<a href="#" data-action="backup-settings" class="button button-primary">Edit Backup Schedule</a>

		<?php if ($a = $backup->action()->get_feedback_subject()): ?>
			<a href="<?php echo esc_attr($backup->action()->url_backup_cancel($a)) ?>" class="button" data-action="backup-spinner">Cancel</a>
		<?php else: ?>

			<?php foreach ($backup_now as $cron_job): ?>
				<a href="<?php echo esc_attr($backup->action()->url_backup_now($cron_job->get_id())) ?>" class="button" data-action="backup-spinner">Create <?php echo esc_html($cron_job->get_title()) ?> Now</a>
			<?php endforeach ?>

			<?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
				<a href="<?php echo esc_attr($backup->action()->url_backup_demo_install()) ?>" class="button" data-action="backup-spinner">Create Demo Install</a>
			<?php endif ?>

		<?php endif ?>

	</div>

</div>

<?php if (defined('WP_DEBUG') && WP_DEBUG): ?>

	<h3>Demo Install</h3>

	<?php if ($post_id = $backup->get_demo_install()): ?>

		<?php $backup_info = $backup->get_backup_info($post_id) ?>

		<table class="wp-list-table widefat fixed posts">
			<tbody>
			<tr class="status-publish hentry alternate iedit author-self level-0">
				<td class="description column-description">
					<div style="margin-left: 0.5em;">
						<p>
							<?php echo $backup->format()->format_date_time_gmt($backup_info->get_storage_file_time()) ?>
						</p>
						<p>
							<?php echo esc_html($backup_info->get_theme()) ?>
							| <a href="<?php echo esc_attr($backup->action()->url_backup_download($post_id)) ?>">Download</a>
							| <a href="<?php echo esc_attr($backup->action()->url_backup_delete($post_id)) ?>">Delete</a>
						</p>
					</div>
				</td>
			</tr>
			</tbody>
		</table>

	<?php else: ?>

		<p>No Demo Install has been created yet</p>

	<?php endif ?>

	<br/>

<?php endif ?>

<h3>Backup Archive</h3>

<?php

	$a = (array) wp_count_posts($post_type->name);
	unset($a['trash']);

	if (array_sum($a) == 0):

?>

	<p id="backup-empty"><?php echo esc_html($post_type->labels->not_found) ?></p>
	<style type="text/css">
		#posts-filter { display: none; }
	</style>

<?php endif ?>
