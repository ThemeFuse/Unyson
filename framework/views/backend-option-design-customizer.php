<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 */

{
	if (!isset($option['label'])) {
		$option['label'] = fw()->backend->option_type($option['type'])->_default_label(
			$id, $option
		);
	}

	if (!isset($option['desc'])) {
		$option['desc'] = '';
	}
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
			'fw-backend-option',
			'fw-backend-option-design-customizer',
			'fw-backend-option-type-'. $option['type'],
			'fw-row',
			'fw-clearfix',
		),
		'label' => array(
			'fw-backend-option-label',
			'responsive' => 'fw-col-xs-12',
		),
		'input' => array(
			'fw-backend-option-input',
			'fw-backend-option-input-type-'. $option['type'],
			'fw-clearfix',
			'responsive' => 'fw-col-xs-12',
		),
		'desc' => array(
			'fw-backend-option-desc',
			'responsive' => 'fw-col-xs-12',
		),
	);

	/** Additional classes for option div */
	{
		if ($help) {
			$classes['option'][] = 'with-help';
		}

		if ($option['label'] === false) {
			$classes['label']['hidden'] = 'fw-hidden';
			unset($classes['label']['responsive']);

			$classes['input']['responsive'] = 'fw-col-xs-12';
			$classes['desc']['responsive']  = 'fw-col-xs-12';
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
<div class="<?php echo esc_attr($classes['option']) ?>" id="fw-backend-option-<?php echo esc_attr($data['id_prefix'] . $id) ?>">
	<?php if ($option['label'] !== false): ?>
		<div class="<?php echo esc_attr($classes['label']) ?>">
			<div class="fw-inner fw-clearfix">
				<label for="<?php echo esc_attr($data['id_prefix']) . esc_attr($id) ?>"><span class="customize-control-title"><?php echo fw_htmlspecialchars($option['label']) ?></span></label>
				<?php if ($help): ?><div class="fw-option-help fw-option-help-in-label <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($option['desc']): ?>
		<div class="<?php echo esc_attr($classes['desc']) ?>">
			<div class="fw-inner"><span class="description customize-control-description"><?php echo ($option['desc'] ? $option['desc'] : '') ?></span></div>
		</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr($classes['input']) ?>">
		<div class="fw-inner fw-pull-<?php echo is_rtl() ? 'right' : 'left'; ?> fw-clearfix">
			<div class="fw-inner-option">
				<?php echo fw()->backend->option_type($option['type'])->render($id, $option, $data) ?>
			</div>
		</div>
	</div>
</div>
