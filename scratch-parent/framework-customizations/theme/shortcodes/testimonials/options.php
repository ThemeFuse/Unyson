<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'testimonials' => array(
		'label'         => __( 'Testimonials', 'fw' ),
		'popup-title'   => __( 'Add/Edit Testimonial', 'fw' ),
		'desc'          => __( 'Here you can add, remove and edit your Testimonials.', 'fw' ),
		'type'          => 'addable-popup',
		'template'      => '{{=author_name}}',
		'popup-options' => array(
			'content'       => array(
				'label' => __( 'Quote', 'fw' ),
				'desc'  => __( 'Enter the testimonial here', 'fw' ),
				'type'  => 'textarea',
				'teeny' => true
			),
			'author_avatar' => array(
				'label' => __( 'Image', 'fw' ),
				'desc'  => __( 'Either upload a new, or choose an existing image from your media library', 'fw' ),
				'type'  => 'upload',
			),
			'author_name'   => array(
				'label' => __( 'Name', 'fw' ),
				'desc'  => __( 'Enter the Name of the Person to quote', 'fw' ),
				'type'  => 'text'
			),
			'author_job'    => array(
				'label' => __( 'Position', 'fw' ),
				'desc'  => __( 'Can be used for a job description', 'fw' ),
				'type'  => 'text'
			),
			'site_name'     => array(
				'label' => __( 'Website Name', 'fw' ),
				'desc'  => __( 'Linktext for the above Link', 'fw' ),
				'type'  => 'text'
			),
			'site_url'      => array(
				'label' => __( 'Website Link', 'fw' ),
				'desc'  => __( 'Link to the Persons website', 'fw' ),
				'type'  => 'text'
			)
		)
	)
);