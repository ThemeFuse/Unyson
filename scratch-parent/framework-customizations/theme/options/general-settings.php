<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'general' => array(
		'title'   => __( 'General', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'general-settings' => array(
				'title'   => __( 'General Settings', 'fw' ),
				'type'    => 'tab',
				'options' => array(
					'general-box' => array(
						'title'   => __( 'General Settings', 'fw' ),
						'type'    => 'box',
						'options' => array(
							'logo' => array(
								'label' => __( 'Logo', 'fw' ),
								'desc'  => __( 'Write your website logo name', 'fw' ),
								'type'  => 'text',
								'value' => get_bloginfo( 'name' )
							),
							'favicon' => array(
								'label' => __( 'Favicon', 'fw' ),
								'desc'  => __( 'Upload a favicon image', 'fw' ),
								'type'  => 'upload'
							)
						)
					),
				)
			),
			fw()->theme->get_options( 'breadcrumbs' ),
		)
	)
);