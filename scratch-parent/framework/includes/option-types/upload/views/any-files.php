<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array  $wrapper_attr
 * @var array  $input_attr
 * @var bool   $is_empty if when rendered the view has a valid attachment to display
 * @var array  $l10n The localization strings
 */
?>
<?php
$filename = $is_empty ? '' : basename(get_attached_file($input_attr['value'], true));
?>
<div <?php echo fw_attr_to_html($wrapper_attr); ?>>
	<input type="hidden" <?php echo fw_attr_to_html($input_attr); ?> />
	<span>
		<em><?php echo $filename; ?></em>
		<a href="#" class="dashicons fw-x clear-uploads-text"></a>
	</span>
	<button class="button" type="button"><?php echo $is_empty ? $l10n['button_add'] : $l10n['button_edit']; ?></button>
</div>