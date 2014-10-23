<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Sitemap options array
 */

function fw_ext_seo_sitemap_get_posts_types_options() {
	$post_types = fw()->extensions->get( 'seo-sitemap' )->get_allowed_post_types();
	$prefix     = fw()->extensions->get( 'seo-sitemap' )->get_name() . '-';
	$options    = array();

	foreach ( $post_types as $post_type ) {
		$post = get_post_type_object( $post_type );

		$option = array(
			'label' => $post->labels->name,
			'text'  => __( 'Check if you want to exclude this page', 'fw' ),
			'type'  => 'checkbox',
			'value' => false
		);

		$options[ $prefix . 'exclude-custom-post-' . $post_type ] = $option;
	}

	return $options;
}

function fw_ext_seo_sitemap_get_taxonomies_options() {
	$taxonomies = fw()->extensions->get( 'seo-sitemap' )->get_allowed_taxonomies();
	$prefix     = fw()->extensions->get( 'seo-sitemap' )->get_name() . '-';
	$options    = array();

	foreach ( $taxonomies as $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );

		$option = array(
			'label' => $tax->labels->name,
			'text'  => __( 'Check if you want to exclude this category', 'fw' ),
			'type'  => 'checkbox',
			'value' => false
		);

		$options[ $prefix . 'exclude-taxonomy-' . $taxonomy ] = $option;
	}

	return $options;
}

function fw_ext_seo_sitemap_get_settings_options() {
	$ext_name = fw()->extensions->get( 'seo-sitemap' )->get_name();
	$prefix   = $ext_name . '-';

	return array(
		$ext_name => array(
			'title'   => __( 'Sitemap', 'fw' ),
			'type'    => 'tab',
			'options' => array(
				$prefix . 'box' => array(
					'title' => __('Sitemap Settings', 'fw'),
					'type'    => 'box',
					'options' => array(
						$prefix . 'group-sitemap-button' => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'sitemap-button' => array(
									'label' => __( 'View Sitemap', 'fw' ),
									'desc'  => __( 'Press button to view sitemap file', 'fw' ),
									'type'  => 'html',
									'html'  => '<a href="' . fw_ext_seo_sitemap_get_stiemap_link() . '" target="_blank" class="button-secondary">' . __( 'XML Sitemap', 'fw' ) . '</a>',
									'value' => ''
								)
							)
						),
						$prefix . 'group-search-engines' => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'search-engies-pings' => array(
									'label' => __( 'Search Engines', 'fw' ),
									'type'  => 'html',
									'html'  => __( 'After adding content the extension will automatically ping to:', 'fw' ) . ' <strong>' . fw_ext_seo_sitemap_get_search_engines_names( false ) . '</strong>',
									'value' => ''
								)
							)
						),
						$prefix . 'group-custom-posts'   => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'exclude-custom-posts-html' => array(
									'label' => __( 'Exclude Pages', 'fw' ),
									'type'  => 'html',
									'html'  => __( 'Please check the pages you do not want to include in sitemap', 'fw' ),
									'value' => ''
								),
								fw_ext_seo_sitemap_get_posts_types_options()
							)
						),
						$prefix . 'group-taxonomies'     => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'exclude-taxonomies-html' => array(
									'label' => __( 'Exclude Categories', 'fw' ),
									'type'  => 'html',
									'html'  => __( 'Please check the categories you do not want to include in sitemap', 'fw' ),
									'value' => ''
								),
								fw_ext_seo_sitemap_get_taxonomies_options()
							)
						),
						$prefix . 'update-sitemap'       => array(
							'label' => __( 'Update Sitemap', 'fw' ),
							'desc'  => __( 'Update sitemap XML file', 'fw' ),
							'type'  => 'html',
							'html'  => '<a href="#" onclick="fw_ext_seo_sitemap_update(this); return false;" class="button-secondary">' . __( 'Update XML Sitemap', 'fw' ) . '</a><span class="spinner"></span>
							<span class="sitemap-update-response sitemap-successfully">' . __( 'The sitemap was updated successfully', 'fw' ) . '</span>
							<span class="sitemap-update-response sitemap-unsuccessfully">' . __( 'Unable to update the sitemap', 'fw' ) . '</span>
							',
							'value' => ''
						),
					)
				),

			)
		)
	);
}