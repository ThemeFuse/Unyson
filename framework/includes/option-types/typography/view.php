<?php if (!defined('FW')) {
	die('Forbidden');
}
/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 * @var  array $fonts
 */

{
	$wrapper_attr = $option['attr'];

	unset(
	$wrapper_attr['value'],
	$wrapper_attr['name']
	);
}

{
	$option['value'] = array_merge(array(
		'size'   => 12,
		'family' => 'Arial',
		'style'  => '400',
		'color'  => '#000000',
	), (array)$option['value']);

	$data['value'] = array_merge($option['value'], is_array($data['value']) ? $data['value'] : array());
}
?>
<div <?php echo fw_attr_to_html($wrapper_attr) ?>>

	<div class="fw-option-typography-option fw-option-typography-option-size fw-border-box-sizing fw-col-sm-2" style="display: <?php echo (!isset($option['components']['size']) || $option['components']['size'] != false) ? 'block' : 'none' ?>;">
		<select data-type="size" name="<?php echo esc_attr($option['attr']['name']) ?>[size]" class="fw-option-typography-option-size-input">
		<?php for ($i = 9; $i <= 70; $i++): ?>
			<option value="<?php echo esc_attr($i) ?>" <?php echo $data['value']['size'] === $i ? ' selected="selected" ' : ''; ?>><?php echo $i ?>px</option>
		<?php endfor; ?>
		</select>
	</div>

	<div class="fw-option-typography-option fw-option-typography-option-family fw-border-box-sizing fw-col-sm-5"
	     style="display: <?php echo ( ! isset( $option['components']['family'] ) || $option['components']['family'] != false ) ? 'block' : 'none'; ?>;">
		<select data-type="family" data-value="<?php echo esc_attr($data['value']['family']); ?>"
		        name="<?php echo esc_attr( $option['attr']['name'] ) ?>[family]"
		        class="fw-option-typography-option-family-input"></select>
	</div>

	<div class="fw-option-typography-option fw-option-typography-option-style fw-border-box-sizing fw-col-sm-3" style="display: <?php echo (!isset($option['components']['family']) || $option['components']['family'] != false) ? 'block' : 'none'; ?>;">
		<select data-type="style" name="<?php echo esc_attr($option['attr']['name']) ?>[style]" class="fw-option-typography-option-style-input">
		<?php if (in_array($data['value']['family'], $fonts['standard'])): ?>
		<?php foreach (
			array(
				'300'       => 'Thin',
				'300italic' => 'Thin/Italic',
				'400'       => 'Normal',
				'400italic' => 'Italic',
				'700'       => 'Bold',
				'700italic' => 'Bold/Italic',
			)
			as $key => $style): ?>
				<option value="<?php echo esc_attr($key) ?>" <?php if ($data['value']['style'] === $key): ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars($style) ?></option>
		<?php endforeach; ?>
		<?php else: ?>
		<?php foreach ($fonts['google'][$data['value']['family']]['variants'] as $variant): ?>
			<option value="<?php echo esc_attr($variant) ?>" <?php if ($data['value']['style'] === $variant): ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars(ucfirst($variant)) ?></option>
		<?php endforeach; ?>
		<?php endif; ?>
		</select>
	</div>

	<div class="fw-option-typography-option fw-option-typography-option-color fw-border-box-sizing fw-col-sm-2" data-type="color" style="display: <?php echo (!isset($option['components']['color']) || $option['components']['color'] != false) ? 'block' : 'none' ?>;">
	<?php
	echo fw()->backend->option_type('color-picker')->render(
		'color',
		array(
			'label' => false,
			'desc'  => false,
			'type'  => 'color-picker',
			'value' => $option['value']['color']
		),
		array(
			'value' => $data['value']['color'],
			'id_prefix' => 'fw-option-' . $id . '-typography-option-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
		)
	)
	?>
	</div>

</div>
