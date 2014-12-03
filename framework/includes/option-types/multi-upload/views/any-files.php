<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array  $wrapper_attr
 * @var array  $input_attr
 * @var bool   $is_empty if when rendered the view has a valid attachment to display
 * @var array  $l10n The localization strings
 */
?>

<?php
	$selected_files_text = '';
	if (!$is_empty) {
		$decoded = json_decode($input_attr['value']);
		$files_number = count($decoded);
		$selected_files_text = $files_number === 1 ? $l10n['files_one'] : sprintf($l10n['files_more'], $files_number);
	}
?>
<div <?php echo fw_attr_to_html($wrapper_attr); ?>>
	<input type="hidden" <?php echo fw_attr_to_html($input_attr); ?> />
	<span>
		<em><?php echo $selected_files_text; ?></em>
		<a href="#" class="dashicons fw-x clear-uploads-text"></a>
	</span>
	<button class="button" type="button"><?php echo $is_empty ? $l10n['button_add'] : $l10n['button_edit']; ?></button>
</div>