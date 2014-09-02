<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }
/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 */

$choices = array();

foreach ( $option['predefined'] as $style_key => $style_data ) {
	$choices[ $style_key ] = array(
		'small' => array(
			'src' => $style_data['icon'],
			'height' => 46
		),
		'data' => array(
			'settings' => $style_data
		)
	);
}

$tmp_options = array(
	'predefined' => array(
		'type'    => 'image-picker',
		'value'   => 'default',
		'label'   => __('Predefined Styles', 'fw'),
		'choices' => $choices,
		'blank'   => true
	)
);

unset($choices);

$tmp_values = array(
	'predefined' => ( ! empty( $data['value']['predefined'] ) )
		? $data['value']['predefined']
		: key( $option['predefined'] )
);

?>
<div class="fw-option-type-style-option predefined_styles">
	<?php
	echo fw()->backend->render_options(
		$tmp_options,
		$tmp_values,
		array(
			'id_prefix'   => $data['id_prefix'] . $id . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
		)
	);

	unset($tmp_options, $tmp_values);
	?>
</div>