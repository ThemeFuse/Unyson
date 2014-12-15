<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $extension_name
 * @var array  $extension_data
 * @var string $extension_title
 * @var string $link_delete
 * @var string $link_extension
 * @var string $tab
 * @var bool   $is_supported
 */

?>
<h2 class="fw-extension-page-title">
	<span class="fw-pull-right">
		<?php
		switch ($tab) {
			case 'settings':
				if (!file_exists($extension_data['path'] .'/readme.md.php')) {
					break;
				}
				if ($is_supported) {
					// do not show install instructions for supported extensions
					break;
				}
				?><a href="<?php echo esc_attr($link_extension) ?>&extension=<?php echo esc_attr($extension_name) ?>&tab=docs" class="button-primary"><?php _e('Install Instructions', 'fw') ?></a><?php
				break;
			case 'docs':
				if (!fw()->extensions->get($extension_name) || !fw()->extensions->get($extension_name)->get_settings_options()) {
					break;
				}
				?><a href="<?php echo esc_attr($link_extension) ?>&extension=<?php echo esc_attr($extension_name) ?>" class="button-primary"><?php _e('Settings', 'fw') ?></a><?php
				break;
		}
		?>
	</span>

	<?php
	switch ($tab) {
		case 'settings':
			echo sprintf(__('%s Settings', 'fw'), $extension_title);
			break;
		case 'docs':
			echo sprintf(__('%s Install Instructions', 'fw'), $extension_title);
			break;
		default:
			echo __('Unknown tab:', 'fw') . ' ' . fw_htmlspecialchars($tab);
	}
	?>
</h2>
<br/>
