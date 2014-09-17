<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Title&Mata options array
 */

/**
 * Array of options that will appear in framework settings
 *
 * @internal
 *
 * @return array
 */
function fw_ext_seo_titles_meta_get_settings_options() {
	$ext_name = fw()->extensions->get( 'seo-titles-metas' )->get_name();
	$prefix   = $ext_name . '-';

	return array(
		$ext_name => array(
			'title'   => __( 'Titles & Meta', 'fw' ),
			'type'    => 'tab',
			'options' => array(
				'homepage'                  => array(
					'title'   => __( 'Homepage', 'fw' ),
					'type'    => 'box',
					'options' => array(
						$prefix . 'homepage-title'       => array(
							'label' => __( 'Homepage Title', 'fw' ),
							'desc'  => __( 'Set homepage title format', 'fw' ),
							'type'  => 'seo-tags',
							'value' => '%%sitename%% | %%sitedesc%%'
						),
						$prefix . 'homepage-description' => array(
							'label' => __( 'Homepage Description', 'fw' ),
							'desc'  => __( 'Set homepage description', 'fw' ),
							'type'  => 'textarea',
							'value' => ''
						),
						fw()->extensions->get( 'seo-titles-metas' )->use_meta_keywords(
							array(
								$prefix . 'homepage-metakeywords' => array(
									'label' => __( 'Homepage Meta Keywords', 'fw' ),
									'desc'  => __( 'Set homepage meta keywords', 'fw' ),
									'type'  => 'seo-tags',
									'value' => ''
								),
							)
						),
					)
				),
				'custom_posts_options'      => array(
					'title'   => __( 'Pages', 'fw' ),
					'type'    => 'box',
					'options' => fw()->extensions->get( $ext_name )->get_custom_pots_options(
						array(
							'title'       => array(
								'label' => __( 'Title', 'fw' ),
								'desc'  => __( 'Set title format', 'fw' ),
								'type'  => 'seo-tags',
								'value' => '%%title%% | %%sitename%%',
								'help'  => sprintf( "%s%s",
									__( 'Here are some tags examples:<br/>', 'fw' ),
									__( '<span>%%sitename%%</span><br/>
										<span>%%currentdate%%</span><br/>
										<span>%%title%%</span>', 'fw' )
								),
							),
							'description' => array(
								'label' => __( 'Description', 'fw' ),
								'desc'  => __( 'Set description format', 'fw' ),
								'type'  => 'seo-tags',
								'value' => '',
								'help'  => sprintf( "%s%s",
									__( 'Here are some tags examples:<br/>', 'fw' ),
									__( '<span>%%sitename%%</span><br/>
										<span>%%currentdate%%</span><br/>
										<span>%%title%%</span>', 'fw' )
								),
							),
							fw()->extensions->get( 'seo-titles-metas' )->use_meta_keywords(
								array(
									'metakeywords' => array(
										'label' => __( 'Meta Keywords', 'fw' ),
										'desc'  => __( 'Set meta keywords', 'fw' ),
										'type'  => 'seo-tags',
										'value' => '',
										'help'  => sprintf( "%s%s",
											__( 'Here are some tags examples:<br/>', 'fw' ),
											__( '<span>%%sitename%%</span><br/>
										<span>%%currentdate%%</span><br/>
										<span>%%title%%</span>', 'fw' )
										),
									)
								)
							),
							'noindex'     => array(
								'label' => __( 'Meta Robots', 'fw' ),
								'desc'  => __( 'noindex, follow', 'fw' ),
								'type'  => 'checkbox',
								'value' => false,
							),
						)
					),
				),
				'custom_taxonomies_options' => array(
					'title'   => __( 'Taxonomies', 'fw' ),
					'type'    => 'box',
					'options' => fw()->extensions->get( $ext_name )->get_taxonomies_options(
						array_merge(
							array(
								'title'       => array(
									'label' => __( 'Title', 'fw' ),
									'desc'  => __( 'Set title format', 'fw' ),
									'type'  => 'seo-tags',
									'value' => '%%title%% | %%description%%',
									'help'  => sprintf( "%s%s",
										__( 'Here are some tags examples:<br/>', 'fw' ),
										__( '<span>%%sitename%%</span><br/>
										<span>%%currentdate%%</span><br/>
										<span>%%title%%</span>', 'fw' )
									),
								),
								'description' => array(
									'label' => __( 'Description', 'fw' ),
									'desc'  => __( 'Set description format', 'fw' ),
									'type'  => 'seo-tags',
									'value' => '',
									'help'  => sprintf( "%s%s",
										__( 'Here are some tags examples:<br/>', 'fw' ),
										__( '<span>%%sitename%%</span><br/>
										<span>%%currentdate%%</span><br/>
										<span>%%title%%</span>', 'fw' )
									),
								),
								fw()->extensions->get( 'seo-titles-metas' )->use_meta_keywords(
									array(
										'metakeywords' => array(
											'label' => __( 'Meta Keywords', 'fw' ),
											'desc'  => __( 'Set meta keywords', 'fw' ),
											'type'  => 'seo-tags',
											'value' => '',
											'help'  => sprintf( "%s%s",
												__( 'Here are some tags examples:<br/>', 'fw' ),
												__( '<span>%%sitename%%</span><br/>
										<span>%%currentdate%%</span><br/>
										<span>%%title%%</span>', 'fw' )
											),
										)
									)
								),
								'noindex'     => array(
									'label' => __( 'Meta Robots', 'fw' ),
									'desc'  => __( 'noindex, follow', 'fw' ),
									'type'  => 'checkbox',
									'value' => false,
								),
							)
						)
					)
				),
				'other_pages_options'       => array(
					'title'   => __( 'Other', 'fw' ),
					'type'    => 'box',
					'options' => array(
						$prefix . 'author-archive-group' => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'author-archive-title'       => array(
									'label' => __( 'Author Page Title', 'fw' ),
									'desc'  => __( 'Set author page title format', 'fw' ),
									'type'  => 'seo-tags',
									'value' => '%%author_name%% | %%sitename%%'
								),
								$prefix . 'author-archive-description' => array(
									'label' => __( 'Author Page Description', 'fw' ),
									'desc'  => __( 'Set author page description', 'fw' ),
									'type'  => 'textarea',
									'value' => ''
								),
								fw()->extensions->get( 'seo-titles-metas' )->use_meta_keywords(
									array(
										$prefix . 'author-archive-metakeywords' => array(
											'label' => __( 'Author Meta Keywords', 'fw' ),
											'desc'  => __( 'Set author page meta keywords', 'fw' ),
											'type'  => 'seo-tags',
											'value' => ''
										),
									)
								),
								$prefix . 'author-archive-noindex'     => array(
									'label' => __( 'Metarobots', 'fw' ),
									'desc'  => __( 'noindex, follow', 'fw' ),
									'type'  => 'checkbox',
									'value' => false
								),
								$prefix . 'author-archive-disable'     => array(
									'label' => __( 'Disable Author Archives', 'fw' ),
									'desc'  => __( 'Disable Author archives SEO settings', 'fw' ),
									'type'  => 'checkbox',
									'value' => false
								)
							)
						),
						$prefix . 'date-archive-group'   => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'date-archive-title'       => array(
									'label' => __( 'Date Achieves Title', 'fw' ),
									'desc'  => __( 'Set date achieves title format', 'fw' ),
									'type'  => 'seo-tags',
									'value' => '%%date%% | %%sitename%%'
								),
								$prefix . 'date-archive-description' => array(
									'label' => __( 'Date Achieves Description', 'fw' ),
									'desc'  => __( 'Set date achieves description', 'fw' ),
									'type'  => 'textarea',
									'value' => ''
								),
								fw()->extensions->get( 'seo-titles-metas' )->use_meta_keywords(
									array(
										$prefix . 'date-archive-metakeywords' => array(
											'label' => __( 'Date achieves Meta Keywords', 'fw' ),
											'desc'  => __( 'Set date achieves meta keywords', 'fw' ),
											'type'  => 'seo-tags',
											'value' => ''
										),
									)
								),
								$prefix . 'date-archive-noindex'     => array(
									'label' => __( 'Metarobots', 'fw' ),
									'desc'  => __( 'noindex, follow', 'fw' ),
									'type'  => 'checkbox',
									'value' => false
								),
								$prefix . 'date-archive-disable'     => array(
									'label' => __( 'Disable Date Archives', 'fw' ),
									'desc'  => __( 'Disable date archives SEO settings', 'fw' ),
									'type'  => 'checkbox',
									'value' => false
								)
							)
						),
						$prefix . 'search-page-group'    => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'search-page-title' => array(
									'label' => __( 'Search Page Title', 'fw' ),
									'desc'  => __( 'Set search page title format', 'fw' ),
									'type'  => 'seo-tags',
									'value' => '%%searchphrase%%',
									'help'  => sprintf( "%s%s",
										__( 'Here are some tags examples:<br/>', 'fw' ),
										__( '<span>%%sitename%%</span><br/>
										<span>%%currentdate%%</span><br/>
										<span>%%title%%</span>', 'fw' )
									),
								)
							)
						),
						$prefix . 'not-found-group'      => array(
							'type'    => 'group',
							'options' => array(
								$prefix . 'not-found-title' => array(
									'label' => __( '404 Page Title', 'fw' ),
									'desc'  => __( 'Set 404 page title format', 'fw' ),
									'type'  => 'seo-tags',
									'value' => '404 Not Found'
								)
							)
						),
					)
				)
			)
		)
	);
}

/**
 * Array of options that will appear in taxonomies editor
 *
 * @internal
 *
 * @return array
 */
function fw_ext_seo_titles_meta_get_taxonomies_options() {
	$ext_name = fw()->extensions->get( 'seo-titles-metas' )->get_name();
	$prefix   = $ext_name . '-';

	return array(
		$prefix . 'title'       => array(
			'label' => __( 'Page Title', 'fw' ),
			'desc'  => __( 'Set title format', 'fw' ),
			'type'  => 'text',
			'value' => ''
		),
		$prefix . 'description' => array(
			'label' => __( 'SEO Description', 'fw' ),
			'desc'  => __( 'Set description format', 'fw' ),
			'type'  => 'textarea',
			'value' => ''
		)
	);
}

/**
 * Array of options that will appear in custom posts editor
 * @return array
 */
function fw_ext_seo_titles_meta_get_post_types_options() {
	$ext_name = fw()->extensions->get( 'seo-titles-metas' )->get_name();
	$prefix   = $ext_name . '-';

	return array(
		$ext_name . 'tab' => array(
			'title'   => __( 'Titles & Meta', 'fw' ),
			'type'    => 'tab',
			'options' => array(
				$prefix . 'title'       => array(
					'label' => __( 'Page Title', 'fw' ),
					'desc'  => __( 'Set title format', 'fw' ),
					'type'  => 'text',
					'value' => ''
				),
				$prefix . 'description' => array(
					'label' => __( 'Description', 'fw' ),
					'desc'  => __( 'Set description format', 'fw' ),
					'type'  => 'textarea',
					'value' => ''
				),
				fw()->extensions->get( 'seo-titles-metas' )->use_meta_keywords(
					array(
						$prefix . 'metakeywords' => array(
							'label' => __( 'Metakeywords', 'fw' ),
							'desc'  => __( 'Set meta keywords', 'fw' ),
							'type'  => 'text',
							'value' => ''
						),
					)
				),
			)
		)
	);
}

/**
 * Array of options that will appear in framework SEO General settings
 *
 * @internal
 *
 * @return array
 */
function fw_ext_seo_titles_meta_get_general_settings_options() {
	$ext_name = fw()->extensions->get( 'seo-titles-metas' )->get_name();
	$prefix   = $ext_name . '-';

	return array(
		$prefix . 'general-options' => array(
			'type'    => 'group',
			'options' => array(
				$prefix . 'metakeywords' => array(
					'label' => __( 'Use Meta Keywords', 'fw' ),
					'desc'  => __( 'Allow the use of meta keywords in posts and taxonomies', 'fw' ),
					'type'  => 'checkbox',
					'value' => false
				)
			),
		),
	);
}