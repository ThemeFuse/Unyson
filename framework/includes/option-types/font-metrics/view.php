<?php if (!defined('FW')) {
	die('Forbidden');
}


/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 */

{
	$wrapper_attr = $option['attr'];

	unset(
	$wrapper_attr['value'],
	$wrapper_attr['name']
	);
}

{
	//grab defaults
	$defaults = array(
		'font-size' => array(
			'value' => 14, 
			'properties' => array('min' => 10, 'max' => 72, 'step' => 1),
		),	
		'line-height'  => array( 
			'value' => 20, 
			'properties' => array('min' => 16, 'max' => 32, 'step' => 1),
		),	
		'letter-spacing'  => array( 
			'value' => 0, 
			'properties' => array('min' => -15, 'max' => 5, 'step' => 1),
		),	
		'transform'  => 'none',
	);	
	

	//set $data 
	$option['value'] = array_merge( $defaults, (array)$option['value']);
	$data['value'] = array_merge( $option['value'], array_filter((array)$data['value']));
	
	//format field attributes  // var_dump($data['value']['letter-spacing']['value']); or var_dump($data['value']['letter-spacing']) depends if there is actually db saved $data
	$font_size_input_attr['name']  = $option['attr']['name'].'[font-size]';
	$font_size_input_attr['value'] = ( isset($data['value']['font-size']['value']) ) ? $data['value']['font-size']['value'] : $data['value']['font-size'];
	
	$line_height_input_attr['name']  = $option['attr']['name'].'[line-height]';
	$line_height_input_attr['value'] = ( isset($data['value']['line-height']['value']) ) ? $data['value']['line-height']['value'] : $data['value']['line-height'];
	
	$letter_spacing_input_attr['name']  = $option['attr']['name'].'[letter-spacing]';
	$letter_spacing_input_attr['value'] = ( isset($data['value']['letter-spacing']['value']) ) ? $data['value']['letter-spacing']['value'] : $data['value']['letter-spacing'];
	
	//set fields classes
	$fields = array( 'font-size', 'line-height', 'letter-spacing', 'transform' );
	$items = 0;
	foreach ( $fields as $field ) {
		if ( $option['components'][$field] != false && isset($option['value'][$field]) ) {
			$items++;
		}		
	}
	$field_class = 12 / $items;

}
?>
<div <?php echo fw_attr_to_html($wrapper_attr) ?>>
	<?php if ( $option['components']['font-size'] != false && isset($option['value']['font-size']) ) { ?>
		<div class="fw-option-font-metrics-option fw-option-font-metrics-slider-option fw-option-font-metrics-option-font-size fw-border-box-sizing fw-col-sm-<?php echo $field_class; ?>" <?php echo fw_attr_to_html( $option['value']['font-size']['attr']); ?>>

			<div class="fw-irs-range-slider"></div>
			<input class="fw-irs-range-slider-hidden-input" type="hidden" <?php echo fw_attr_to_html($font_size_input_attr); ?>/>	
			
		</div>
	<?php } ?>

	<?php if ( $option['components']['line-height'] != false && isset($option['value']['line-height']) ) { ?>
		<div class="fw-option-font-metrics-option fw-option-font-metrics-slider-option fw-option-font-metrics-option-line-height fw-border-box-sizing fw-col-sm-<?php echo $field_class; ?>" <?php echo fw_attr_to_html( $option['value']['line-height']['attr']); ?>>

			<div class="fw-irs-range-slider"></div>
			<input class="fw-irs-range-slider-hidden-input" type="hidden" <?php echo fw_attr_to_html($line_height_input_attr); ?>/>	
			
		</div>
	<?php } ?>

	<?php if ( $option['components']['letter-spacing'] != false && isset($option['value']['letter-spacing']) ) { ?>
		<div class="fw-option-font-metrics-option fw-option-font-metrics-slider-option fw-option-font-metrics-option-letter-spacing fw-border-box-sizing fw-col-sm-<?php echo $field_class; ?>" <?php echo fw_attr_to_html( $option['value']['letter-spacing']['attr']); ?>>

			<div class="fw-irs-range-slider"></div>
			<input class="fw-irs-range-slider-hidden-input" type="hidden" <?php echo fw_attr_to_html($letter_spacing_input_attr); ?>/>	
			
		</div>
	<?php } ?>

	<?php if ( $option['components']['transform'] != false && isset($option['value']['transform']) ) { ?>
		<div class="fw-option-font-metrics-option fw-option-font-metrics-option-transform fw-border-box-sizing fw-col-sm-<?php echo $field_class; ?>">
			<select data-type="transform" name="<?php echo esc_attr($option['attr']['name']) ?>[transform]" class="fw-option-font-metrics-option-transform-input">
				
				<?php foreach ( array( 'none' => 'None', 'capitalize' => 'Capitalize', 'uppercase' => 'Uppercase', 'lowercase' => 'Lowercase' ) as $key => $transform): ?>
						<option value="<?php echo esc_attr($key) ?>" <?php if ( ( isset($data['value']['transform']['value']) ? $data['value']['transform']['value'] : $data['value']['transform'] ) == $key) : ?>selected="selected"<?php endif; ?>><?php echo fw_htmlspecialchars($transform) ?></option>
				<?php endforeach; ?>

			</select>
		</div>
	<?php } ?>
		
</div>
