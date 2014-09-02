<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'type'    => array(
		'type'    => 'select',
		'label'   => __( 'Select Box Type', 'fw' ),
		'choices' => array(
			''         => __( 'Default', 'fw' ),
			'vertical' => __( 'Vertical line', 'fw' )
		)
	),
	'icon'    => array(
		'type'  => 'icon',
		'label' => 'Choose an Icon',
	),
	'title'   => array(
		'type'  => 'text',
		'label' => __( 'Title of the Box', 'fw' ),
	),
	'content' => array(
		'type'  => 'textarea',
		'label' => __( 'Content', 'fw' ),
		'desc'  => __( 'Enter the desired content', 'fw' ),
	),
);