<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$background_image = array(
	'value'   => 'none',
	'choices' => array(
		'none' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/no_pattern.jpg'),
			'css'  => array(
				'background-image' => 'none'
			)
		),
		'bg-1' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/diagonal_bottom_to_top_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/diagonal_bottom_to_top_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
		'bg-2' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/diagonal_top_to_bottom_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/diagonal_top_to_bottom_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
		'bg-3' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/dots_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/dots_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
		'bg-4' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/romb_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/romb_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
		'bg-5' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/square_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/square_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
		'bg-6' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/noise_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/noise_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
		'bg-7' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/vertical_lines_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/vertical_lines_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
		'bg-8' => array(
			'icon' => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/waves_pattern_preview.jpg'),
			'css'  => array(
				'background-image'  => 'url("' . fw_get_template_customizations_directory_uri('/extensions/styling/static/images/patterns/waves_pattern.png') . '")',
				'background-repeat' => 'repeat',
			)
		),
	)
);

$styles = array(
	'black' => array(
		'name'   => 'Black',
		'icon'   => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/black_predefined_style.jpg'),
		'blocks' => array(
			'header'  => array(
				'h1'          => array(
					'size'   => 18,
					'family' => 'Merienda One',
					'style'  => 'regular',
					'color'  => '#ffffff'
				),
				'links'       => '#ffffff',
				'links_hover' => '#f17e12',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#111111',
						'secondary' => '#111111'
					),
					'background-image' => $background_image,
				),
			),
			'content' => array(
				'h2'          => array(
					'size'   => 24,
					'family' => 'Merienda One',
					'style'  => 'regular',
					'color'  => '#2b2b2b'
				),
				'h3'          => array(
					'size'   => 22,
					'family' => 'Merienda One',
					'style'  => 'regular',
					'color'  => '#2b2b2b'
				),
				'p'           => array(
					'size'   => 16,
					'family' => 'Open Sans',
					'style'  => 'regular',
					'color'  => '#2b2b2b'
				),
				'links'       => '#f17e12',
				'links_hover' => '#834a15',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#ffffff',
						'secondary' => '#ffffff'
					),
					'background-image' => $background_image,
				),
			),
			'sidebar' => array(
				'h1'          => array(
					'size'   => 11,
					'family' => 'Lato',
					'style'  => '900',
					'color'  => '#ffffff'
				),
				'links'       => '#ffffff',
				'links_hover' => '#f17e12',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#111111',
						'secondary' => '#111111'
					),
					'background-image' => $background_image,
				),
			),
			'footer'  => array(
				'h1'          => array(
					'size'   => 11,
					'family' => 'Lato',
					'style'  => '900',
					'color'  => '#ffffff'
				),
				'links'       => '#ffffff',
				'links_hover' => '#f17e12',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#111111',
						'secondary' => '#111111'
					),
					'background-image' => $background_image,
				),
			)
		)
	),
	'green' => array(
		'name'   => 'Green',
		'icon'   => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/green_predefined_style.jpg'),
		'blocks' => array(
			'header'  => array(
				'h1'          => array(
					'size'   => 18,
					'family' => 'Philosopher',
					'style'  => 'regular',
					'color'  => '#ffffff'
				),
				'links'       => '#04d19b',
				'links_hover' => '#34fdbe',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#006c4f',
						'secondary' => '#006c4f'
					),
					'background-image' => $background_image,
				),
			),
			'content' => array(
				'h2'          => array(
					'size'   => 24,
					'family' => 'Philosopher',
					'style'  => 'regular',
					'color'  => '#2b2b2b'
				),
				'h3'          => array(
					'size'   => 22,
					'family' => 'Philosopher',
					'style'  => 'regular',
					'color'  => '#2b2b2b'
				),
				'p'           => array(
					'size'   => 16,
					'family' => 'Gafata',
					'style'  => 'regular',
					'color'  => '#2b2b2b'
				),
				'links'       => '#006c4f',
				'links_hover' => '#00a77a',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#ffffff',
						'secondary' => '#ffffff'
					),
					'background-image' => $background_image,
				),
			),
			'sidebar' => array(
				'h1'          => array(
					'size'   => 12,
					'family' => 'Philosopher',
					'style'  => 'regular',
					'color'  => '#ffffff'
				),
				'links'       => '#04d19b',
				'links_hover' => '#34fdbe',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#006c4f',
						'secondary' => '#006c4f'
					),
					'background-image' => $background_image,
				),
			),
			'footer'  => array(
				'h1'          => array(
					'size'   => 12,
					'family' => 'Philosopher',
					'style'  => 'regular',
					'color'  => '#ffffff'
				),
				'links'       => '#04d19b',
				'links_hover' => '#34fbde',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#006c4f',
						'secondary' => '#006c4f'
					),
					'background-image' => $background_image,
				),
			),
		)
	),
	'blue'  => array(
		'name'   => 'Blue',
		'icon'   => fw_get_template_customizations_directory_uri('/extensions/styling/static/images/blue_predefined_style.jpg'),
		'blocks' => array(
			'header'  => array(
				'h1'          => array(
					'size'   => 18,
					'family' => 'Fugaz One',
					'style'  => 'regular',
					'color'  => '#ffffff'
				),
				'links'       => '#b7d3f5',
				'links_hover' => '#ffffff',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#206bb6',
						'secondary' => '#206bb6'
					),
					'background-image' => $background_image,
				),
			),
			'content' => array(
				'h2'          => array(
					'size'   => 24,
					'family' => 'Fugaz One',
					'style'  => 'regular',
					'color'  => '#11385e'
				),
				'h3'          => array(
					'size'   => 22,
					'family' => 'Fugaz One',
					'style'  => 'regular',
					'color'  => '#11385e'
				),
				'p'           => array(
					'size'   => 16,
					'family' => 'Lato',
					'style'  => 'regular',
					'color'  => '#11385e'
				),
				'links'       => '#206bb6',
				'links_hover' => '#11385e',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#ffffff',
						'secondary' => '#ffffff'
					),
					'background-image' => $background_image,
				),
			),
			'sidebar' => array(
				'h1'          => array(
					'size'   => 11,
					'family' => 'Lato',
					'style'  => '700',
					'color'  => '#ffffff'
				),
				'links'       => '#b7d3f5',
				'links_hover' => '#ffffff',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#206bb6',
						'secondary' => '#206bb6'
					),
					'background-image' => $background_image,
				),
			),
			'footer'  => array(
				'h1'          => array(
					'size'   => 11,
					'family' => 'Lato',
					'style'  => '700',
					'color'  => '#ffffff'
				),
				'links'       => '#b7d3f5',
				'links_hover' => '#ffffff',
				'background'  => array(
					'background-color' => array(
						'primary'   => '#206bb6',
						'secondary' => '#206bb6'
					),
					'background-image' => $background_image,
				),
			),
		)
	)
);
return apply_filters( 'fw_ext_styling_predefined_styles', $styles );
