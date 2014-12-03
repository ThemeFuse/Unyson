<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $id
 * @var  array $option
 * @var  mixed $data
 */

{
	if (!isset($option['label']))
		$option['label'] = $id;

	if (!isset($option['desc']))
		$option['desc'] = '';
}

{
	$help = false;

	if (!empty($option['help'])) {
		$help = array(
			'icon'  => 'info',
			'html'  => '{undefined}',
		);

		if (is_array($option['help'])) {
			$help = array_merge($help, $option['help']);
		} else {
			$help['html'] = $option['help'];
		}

		switch ($help['icon']) {
			case 'info':
				$help['class'] = 'dashicons dashicons-info';
				break;
			case 'video':
				$help['class'] = 'dashicons dashicons-video-alt3';
				break;
			default:
				$help['class'] = 'dashicons dashicons-smiley';
		}
	}
}

{
	$classes = array(
		'option' => array(
			'form-field',
			'fw-backend-option',
			'fw-backend-option-design-taxonomy',
			'fw-backend-option-type-'. $option['type']
		),
		'label' => array(
			'fw-backend-option-label',
		),
		'input' => array(
			'fw-backend-option-input',
			'fw-backend-option-input-type-'. $option['type'],
		),
		'desc' => array(
			'description',
			'fw-backend-option-desc',
		),
	);

	/** Additional classes for option div */
	{
		if ($help) {
			$classes['option'][] = 'with-help';
		}
	}

	/** Additional classes for input div */
	{
		$width_type = fw()->backend->option_type($option['type'])->_get_backend_width_type();

		if (!in_array($width_type, array('auto', 'fixed', 'full'))) {
			$width_type = 'auto';
		}

		$classes['input']['width-type'] = 'width-type-'. $width_type;
	}

	foreach ($classes as $key => $_classes) {
		$classes[$key] = implode(' ', $_classes);
	}
	unset($key, $_classes);
}

?>
<tr class="<?php echo esc_attr($classes['option']) ?>" id="fw-backend-option-<?php echo esc_attr($data['id_prefix']) . esc_attr($id) ?>">
	<th scope="row" valign="top" class="<?php echo esc_attr($classes['label']) ?>">
		<label for="<?php echo $data['id_prefix'] . esc_attr($id) ?>"><?php echo fw_htmlspecialchars($option['label']) ?></label>
		<?php if ($help): ?><div class="fw-option-help fw-option-help-in-label fw-visible-xs-block <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
	</th>
	<td>
		<div class="<?php echo esc_attr($classes['input']) ?>">
			<div class="fw-inner fw-pull-<?php echo is_rtl() ? 'right' : 'left'; ?>">
				<?php if ($help): ?><div class="fw-option-help fw-option-help-in-input fw-pull-right fw-hidden-xs <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
				<div class="fw-inner-option">
					<?php echo fw()->backend->option_type($option['type'])->render($id, $option, $data) ?>
				</div>
				<div class="fw-clear"></div>
			</div>
			<div class="fw-clear"></div>
		</div>
		<?php if ($option['desc']): ?>
			<?php if ($option['type'] == 'textarea'): ?>
				<span class="description fw-option-desc"><?php echo ($option['desc'] ? $option['desc'] : '') ?></span>
			<?php else: ?>
				<p class="<?php echo esc_attr($classes['desc']) ?>"><?php echo ($option['desc'] ? $option['desc'] : '') ?></p>
			<?php endif; ?>
		<?php endif; ?>
	</td>
</tr>