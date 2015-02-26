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
		'family' => 'Oswald',
		'style'  => '400',
		'subsets'  => 'latin',
	), (array)$option['value']);

	$data['value'] = array_merge($option['value'], array_filter((array)$data['value']));
	
}
?>
<div <?php echo fw_attr_to_html($wrapper_attr) ?>>

	<div class="fw-option-webfonts-option fw-option-webfonts-option-family fw-border-box-sizing fw-col-sm-5"
	     style="display: <?php echo ( ! isset( $option['components']['family'] ) || $option['components']['family'] != false ) ? 'block' : 'none'; ?>;">
		<select data-type="family" data-value="<?php echo $data['value']['family']; ?>"
		        name="<?php echo esc_attr( $option['attr']['name'] ) ?>[family]"
		        class="fw-option-webfonts-option-family-input">
		</select>
	</div>

	<div class="fw-option-webfonts-option fw-option-webfonts-option-style fw-border-box-sizing fw-col-sm-4" style="display: <?php echo (!isset($option['components']['family']) || $option['components']['family'] != false) ? 'block' : 'none'; ?>;">
		<select data-type="style" name="<?php echo esc_attr($option['attr']['name']) ?>[style]" class="fw-option-webfonts-option-style-input">
			
			<?php foreach ($fonts['google'][$data['value']['family']]['variants'] as $variant): ?>
				<option value="<?php echo esc_attr($variant) ?>" <?php if ($data['value']['style'] == $variant): ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars(ucfirst($variant)) ?></option>
			<?php endforeach; ?>

		</select>
	</div>
	
	<div class="fw-option-webfonts-option fw-option-webfonts-option-subsets fw-border-box-sizing fw-col-sm-3" style="display: <?php echo (!isset($option['components']['family']) || $option['components']['family'] != false) ? 'block' : 'none'; ?>;">
		<select data-type="subsets" name="<?php echo esc_attr($option['attr']['name']) ?>[subsets]" class="fw-option-webfonts-option-subsets-input">
		
			<?php foreach ($fonts['google'][$data['value']['family']]['subsets'] as $subset): ?>
				<option value="<?php echo esc_attr($subset) ?>" <?php if ($data['value']['subsets'] == $subset): ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars(ucfirst($subset)) ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
