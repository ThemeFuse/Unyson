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
	$defaults = array(
		'family' => 'Helvetica',
		'style'  => 'normal',
		'weight'  => '400',
	);
	$option['value'] = array_merge($defaults, (array)$option['value']);

	$data['value'] = array_merge($option['value'], array_filter((array)$data['value']));
	
}
?>
<div <?php echo fw_attr_to_html($wrapper_attr) ?>>

	<div class="fw-option-standard-fonts-option fw-option-standard-fonts-option-family fw-border-box-sizing fw-col-sm-5"
	     style="display: <?php echo ( ! isset( $option['components']['family'] ) || $option['components']['family'] != false ) ? 'block' : 'none'; ?>;">
		<select data-type="family" data-value="<?php echo $data['value']['family']; ?>" name="<?php echo esc_attr( $option['attr']['name'] ) ?>[family]" class="fw-option-standard-fonts-option-family-input">
		
			<?php foreach ($fonts['standard'] as $key => $font): ?>
				<option value="<?php echo esc_attr($font) ?>" <?php if ($data['value']['family'] == $font): ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars(ucfirst($font)) ?></option>
			<?php endforeach; ?>
		
		</select>
	</div>

	<div class="fw-option-standard-fonts-option fw-option-standard-fonts-option-style fw-border-box-sizing fw-col-sm-4" style="display: <?php echo (!isset($option['components']['family']) || $option['components']['family'] != false) ? 'block' : 'none'; ?>;">
		<select data-type="style" name="<?php echo esc_attr($option['attr']['name']) ?>[style]" class="fw-option-standard-fonts-option-style-input">
			
			<?php foreach ( array( 'regular' => 'Normal', 'italic' => 'Italic' ) as $key => $style): ?>
					<option value="<?php echo esc_attr($key) ?>" <?php if ($data['value']['style'] == $key): ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars($style) ?></option>
			<?php endforeach; ?>

		</select>
	</div>
	
	<div class="fw-option-standard-fonts-option fw-option-standard-fonts-option-weight fw-border-box-sizing fw-col-sm-3" style="display: <?php echo (!isset($option['components']['family']) || $option['components']['family'] != false) ? 'block' : 'none'; ?>;">
		<select data-type="weight" name="<?php echo esc_attr($option['attr']['name']) ?>[weight]" class="fw-option-standard-fonts-option-weight-input">
			
			<?php foreach ( array( '200' => 'Thinner', '300' => 'Thin', '400' => 'Regular', '700' => 'Bold', '900' => 'Strong' ) as $key => $weight): ?>
					<option value="<?php echo esc_attr($key) ?>" <?php if ($data['value']['weight'] == $key): ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars($weight) ?></option>
			<?php endforeach; ?>

		</select>
	</div>
	
</div>
