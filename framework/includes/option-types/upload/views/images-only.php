<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array  $wrapper_attr
 * @var array  $input_attr
 * @var bool   $is_empty if when rendered the view has a valid attachment to display
 * @var array  $l10n The localization strings
 */
?>
<div <?php echo fw_attr_to_html($wrapper_attr); ?>>
	<input type="hidden" <?php echo fw_attr_to_html($input_attr); ?>/>
	<?php if ($is_empty): ?>
		<div class="thumb">
			<img src="<?php echo fw_get_framework_directory_uri('/static/img/no-image.png'); ?>" class="no-image-img" alt="<?php esc_attr_e('No image', 'fw') ?>"/>
		</div>
	<?php else: ?>
		<?php
		$id                     = $input_attr['value'];
		$attachment_thumb_url   = wp_get_attachment_thumb_url($id);
		$attachment_filename    = basename(get_attached_file($id, true));
		$attachment_url         = wp_get_attachment_url($id);
		?>
		<div class="thumb" data-attid="<?php echo esc_attr($id); ?>" data-origsrc="<?php echo esc_attr($attachment_url); ?>">
			<img src="<?php echo esc_attr($attachment_thumb_url); ?>" alt="<?php echo esc_attr($attachment_filename); ?>"/>
			<a href="#" class="dashicons fw-x clear-uploads-thumb"></a>
		</div>
	<?php endif; ?>
	<p><a href="#"><?php echo $is_empty ? $l10n['button_add'] : $l10n['button_edit']; ?></a></p>

	<br class="thumb-template-empty fw-hidden" data-template="<?php echo fw_htmlspecialchars(
		'<img src="'. fw_get_framework_directory_uri('/static/img/no-image.png') .'" class="no-image-img" alt="'. esc_attr__('No image', 'fw') .'"/>'
	); ?>">
	<br class="thumb-template-not-empty fw-hidden" data-template="<?php echo fw_htmlspecialchars(
		'<img src="<%- data.src %>" alt="<%- data.alt %>"/>'.
		'<a href="#" class="dashicons fw-x clear-uploads-thumb"></a>'
	); ?>">

	<!-- fixes https://github.com/ThemeFuse/Unyson/issues/1309 -->
	<?php echo fw_html_tag('input', array(
		'type' => 'hidden',
		'name' => '_fake[url]',
		'value' => intval($input_attr['value']) ? wp_get_attachment_url($input_attr['value']) : '',
		'class' => 'fw-option-type-upload-image-url'
	)); ?>
</div>