<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 */

{
	if (!isset($option['label'])) {
		$option['label'] = fw_id_to_title($id);
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
	try {
		$responsive_classes = FW_Cache::get(
			$cache_key = 'fw:backend-option-view:responsive-classes'
		);
	} catch (FW_Cache_Not_Found_Exception $e) {
		FW_Cache::set(
			$cache_key,
			$responsive_classes = apply_filters('fw:backend-option-view:design-default:responsive-classes', array(
				'label' => 'fw-col-xs-12 fw-col-sm-3 fw-col-lg-2',
				'input' => 'fw-col-xs-12 fw-col-sm-9 fw-col-lg-10',
			))
		);
	}

	$classes = array(
		'option' => array(
			'fw-backend-option',
			'fw-backend-option-design-default',
			'fw-backend-option-type-'. $option['type'],
			'fw-row',
			'fw-clearfix',
		),
		'label' => array(
			'fw-backend-option-label',
			'responsive' => $responsive_classes['label'],
		),
		'input' => array(
			'fw-backend-option-input',
			'fw-backend-option-input-type-'. $option['type'],
			'fw-clearfix',
			'responsive' => $responsive_classes['input'],
		),
		'desc' => array(
			'fw-backend-option-desc',
			'responsive' => 'fw-col-xs-12 fw-col-sm-offset-3 fw-col-sm-9 fw-col-lg-offset-2 fw-col-lg-10',
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

		$hide_bottom_border = fw_akg( 'hide-bottom-border', $option, false );
		if( $hide_bottom_border ) {
			$classes['option'][] = 'fw-bottom-border-hidden';
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

try {
	$desc_under_label = FW_Cache::get(
		$cache_key = 'fw:backend-option-view:desc-under-label'
	);
} catch (FW_Cache_Not_Found_Exception $e) {
	FW_Cache::set(
		$cache_key,
		/**
		 * Fixes https://github.com/ThemeFuse/Unyson/issues/2143
		 * @since 2.6.9
		 */
		$desc_under_label = apply_filters('fw:backend-option-view:design-default:desc-under-label', false)
	);
}
?>
<div class="<?php echo esc_attr($classes['option']) ?>" id="fw-backend-option-<?php echo esc_attr($data['id_prefix'] . $id) ?>">
	<?php if ($option['label'] !== false): ?>
		<div class="<?php echo esc_attr($classes['label']) ?>">
			<div class="fw-inner fw-clearfix">
				<label for="<?php echo esc_attr($data['id_prefix']) . esc_attr($id) ?>"><?php echo fw_htmlspecialchars($option['label']) ?></label>
				<?php if ($help): ?><div class="fw-option-help fw-option-help-in-label fw-visible-xs-block <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
				<?php if ($option['desc'] && $desc_under_label): ?><div class="fw-clear"></div><p><em class="fw-text-muted"><?php echo ($option['desc'] ? $option['desc'] : '') ?></em></p><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr($classes['input']) ?>">
		<div class="fw-inner fw-pull-<?php echo is_rtl() ? 'right' : 'left'; ?> fw-clearfix">
			<?php if ($help): ?><div class="fw-option-help fw-option-help-in-input fw-pull-right fw-hidden-xs <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
			<div class="fw-inner-option fw-clearfix">
				<?php echo fw()->backend->option_type($option['type'])->render($id, $option, $data) ?>
			</div>
		</div>
	</div>
	<?php if ($option['desc'] && !$desc_under_label): ?>
		<div class="<?php echo esc_attr($classes['desc']) ?>">
			<div class="fw-inner"><?php echo ($option['desc'] ? $option['desc'] : '') ?></div>
		</div>
	<?php endif; ?>
</div>