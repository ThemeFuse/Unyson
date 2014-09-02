<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Breadcrumbs settings options
 */

$options = array(
	'breadcrumbs' => array(
		'title'   => __( 'Breadcrumbs', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'breadcrumbs-box' => array(
				'title'   => __( 'Breadcrumbs', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'breadcrumbs-option' => array(
						'label' => false,
						'desc'  => false,
						'type'  => 'breadcrumbs'
					)
				)
			),
		)
	)
);