<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'url'    => array(
		'type'  => 'text',
		'label' => __( 'Insert Video URL', 'fw' ),
		'desc'  => __( 'Insert Video URL to embed this video', 'fw' )
	),
	'width'  => array(
		'type'  => 'text',
		'label' => __( 'Video Width', 'fw' ),
		'desc'  => __( 'Enter a value for the width', 'fw' ),
		'value' => 300
	),
	'height' => array(
		'type'  => 'text',
		'label' => __( 'Video Height', 'fw' ),
		'desc'  => __( 'Enter a value for the height', 'fw' ),
		'value' => 200
	)
);
