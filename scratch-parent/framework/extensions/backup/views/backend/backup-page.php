<?php if (!defined('FW')) die('Forbidden');

/**
 * @var FW_Extension_Backup $backup
 * @var FW_Backup_Interface_Cron $cron
 */

$backup = fw()->extensions->get('backup');
$active = array();
$inactive = array();
$backup_now = array();

foreach ($backup->service_list('FW_Backup_Interface_Cron') as $cron) {
	if ($cron->is_active()) {
		$active[] = $cron;
	}
	else {
		$inactive[] = $cron;
	}
	if ($cron->has_backup_now()) {
		$backup_now[] = $cron;
	}
}

?>

<p class="backup-subtitle description" id="backup-subtitle">Here you can create a backup schedule for your website.</p>

<div id="backup-container">

	<?php if ($backup->wp_verify_nonce('backup-progress')): ?>

		<div id="backup-progress-container" data-post="<?php echo esc_attr(FW_Request::GET('post')) ?>">
			<?php echo $backup->backup_render_progress(FW_Request::GET('post')) ?>
		</div>

	<?php endif ?>

	<?php if (count($active) == 0): ?>

		<div class="backup-alert error below-h2">
			<p><strong>Important:</strong> No backup schedule created yet! We advise you to do it asap!</p>
		</div>

	<?php else: ?>

		<?php foreach ($inactive as $cron): ?>
			<div class="backup-alert error below-h2">
				<p><strong>Important:</strong> No <em><?php echo esc_html($cron->get_title()) ?></em> schedule created yet!</p>
			</div>
		<?php endforeach ?>

		<?php foreach ($active as $cron): ?>
			<div class="backup-alert updated below-h2">
				<p>
					<a href="<?php echo esc_attr($backup->url_backup_unschedule($cron->get_id())) ?>" class="backup-icon-remove"></a>
					<strong><?php echo esc_html($cron->get_title()) ?> schedule active:</strong>
					<?php echo $cron->get_schedule_title() ?> |
					<?php echo $cron->get_storage()->get_title('on') ?> |
					<?php echo $cron->get_next_at_title() ?>
				</p>
			</div>
		<?php endforeach ?>

	<?php endif ?>

	<div class="backup-controls">
		<a href="#" data-action="backup-settings" data-options="<?php echo esc_attr(json_encode($backup->get_backup_settings_options())) ?>" data-values="<?php echo esc_attr(json_encode($backup->get_backup_settings_values())) ?>" class="button button-primary">Edit Backup Schedule</a>
		<?php if ($backup->wp_verify_nonce('backup-progress')): ?>
			&nbsp;&nbsp;&nbsp;<a href="<?php echo esc_attr($backup->url_backup_cancel(FW_Request::GET('post'))) ?>">Cancel</a>
		<?php else: ?>
			<?php foreach ($backup_now as $cron): ?>
				<a href="<?php echo esc_attr($backup->url_backup_now($cron->get_id())) ?>" class="button" data-action="backup-now">Create <?php echo esc_html($cron->get_title()) ?> Now</a>
			<?php endforeach ?>
		<?php endif ?>
	</div>

</div>

<h3>Backup Archive</h3>

<?php if (!$backup->debug && array_sum((array) wp_count_posts($backup->get_post_type())) == 0): ?>
	<style>#posts-filter {display: none;}</style>
	<p>Nothing Found</p>
<?php endif ?>
