<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'table_purpose' => array(
		'type'    => 'select',
		'label'   => __( 'Table Styling', 'fw' ),
		'help'    => 'There you can select some styling for your table.',
		'desc'    => __( 'Choose table styling options', 'fw' ),
		'choices' => array(
			'pricing' => __( 'Use the table as a pricing table', 'fw' ),
			'tabular' => __( 'Use the table to display tabular data', 'fw' )
		),
	),
	'table'         => array(
		'type'  => 'table-builder',
		'label' => false,
		'desc'  => false,
	)
);