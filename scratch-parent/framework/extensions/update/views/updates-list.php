<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $updates
 */
?>

<a name="fw-framework"></a>
<h3><?php _e('Framework', 'fw') ?></h3>
<?php if ($updates['framework']): ?>
	<?php if (is_wp_error($updates['framework'])): ?>
		<p class="wp-ui-text-notification"><?php echo $updates['framework']->get_error_message() ?></p>
	<?php else: ?>
		<form id="fw-ext-update-framework" method="post" action="update-core.php?action=fw-update-framework">
			<p><?php _e(sprintf('You have version %s installed. Update to %s.',
					fw()->manifest->get_version(),
					$updates['framework']['fixed_latest_version']
				), 'fw')
				?></p>
			<?php wp_nonce_field(-1, '_nonce_fw_ext_update_framework'); ?>
			<p><input class="button" type="submit" value="<?php echo esc_attr(__('Update Framework', 'fw')) ?>" name="update"></p>
		</form>
	<?php endif; ?>
<?php else: ?>
	<p><?php echo sprintf(__('You have the latest version of %s.', 'fw'), fw()->manifest->get_name()) ?></p>
<?php endif; ?>

<?php if ($updates['theme']): ?>
	<a name="fw-theme"></a>
	<?php $theme = wp_get_theme(); ?>
	<h3><?php _e(sprintf('%s Theme', $theme->parent()->get('Name')), 'fw') ?></h3>
	<?php if (is_wp_error($updates['theme'])): ?>
		<p class="wp-ui-text-notification"><?php echo $updates['theme']->get_error_message() ?></p>
	<?php else: ?>
		<form id="fw-ext-update-theme" method="post" action="update-core.php?action=fw-update-theme">
			<p><?php _e(sprintf('You have version %s installed. Update to %s.',
					fw()->theme->manifest->get_version(),
					$updates['theme']['fixed_latest_version']
				), 'fw')
			?></p>
			<?php wp_nonce_field(-1, '_nonce_fw_ext_update_theme'); ?>
			<p><input class="button" type="submit" value="<?php echo esc_attr(__('Update Theme', 'fw')) ?>" name="update"></p>
		</form>
	<?php endif; ?>
<?php endif; ?>

<?php if (!empty($updates['extensions'])): ?>
	<a name="fw-extensions"></a>
	<h3><?php _e('Extensions', 'fw') ?></h3>
	<form id="fw-ext-update-extensions" method="post" action="update-core.php?action=fw-update-extensions">
		<p><input class="button" type="submit" value="<?php echo esc_attr(__('Update Extensions', 'fw')) ?>" name="update"></p>
		<?php
		if (!class_exists('_FW_Ext_Update_Extensions_List_Table')) {
			fw_include_file_isolated(
				fw()->extensions->get('update')->get_declared_path('/includes/classes/class--fw-ext-update-extensions-list-table.php')
			);
		}

		$list_table = new _FW_Ext_Update_Extensions_List_Table(array(
			'extensions' => $updates['extensions']
		));

		$list_table->display();
		?>
		<?php wp_nonce_field(-1, '_nonce_fw_ext_update_extensions'); ?>
		<p><input class="button" type="submit" value="<?php echo esc_attr(__('Update Extensions', 'fw')) ?>" name="update"></p>
	</form>
<?php endif; ?>
