<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * SEO Titles and Metas extension
 * Is sa a sub-extension of the SEO extension.
 */
class FW_Extension_Seo_Titles_Metas extends FW_Extension {
	/**
	 * Holds the the list of the allowed custom posts, that are defined in the extension configuration file
	 */
	private $allowed_post_types = array();

	/**
	 * Holds the the list of the allowed taxonomies, that are defined in the extension configuration file
	 */
	private $allowed_taxonomies = array();

	private $settings_options = null;

	/**
	 * @internal
	 */
	public function _init() {
		if ( is_admin() ) {
			$this->add_admin_filters();
		} else {
			$this->add_theme_actions();
			$this->add_theme_filters();
		}
		add_action( 'init', array( $this, '_action_set_allowed_items' ) );
	}

	/**
	 * Init the admin area filters
	 */
	private function add_admin_filters() {
		add_filter( 'fw_ext_seo_admin_options', array( $this, '_filter_set_framework_titles_metas_tab' ) );
		add_filter( 'fw_ext_seo_general_setting_admin_options', array(
			$this,
			'_filter_set_framework_titles_metas_options'
		) );
		add_filter( 'fw_ext_seo_post_type_options', array(
			$this,
			'_filter_set_custom_posts_titles_metas_metabox'
		), 10, 2 );
		add_filter( 'fw_ext_seo_taxonomy_options', array(
			$this,
			'_filter_set_taxonomies_titles_metas_options'
		), 10, 2 );
	}

	/**
	 * Init the frontend area actions
	 */
	private function add_theme_actions() {
		add_action( 'wp_head', array( $this, '_action_add_meta' ) );
	}

	/**
	 * Init the frontend area filters
	 */
	private function add_theme_filters() {
		add_filter( 'wp_title', array( $this, '_action_add_title' ), 999, 3 );
	}

	/**
	 * @internal
	 *
	 * @param null|string $index
	 *
	 * @return mixed|null
	 */
	private function get_admin_options( $index = null ) {
		if ( is_null( $this->settings_options ) ) {
			$this->settings_options = fw_get_db_extension_data( $this->get_parent()->get_name(), 'options' );
		}

		if ( is_null( $index ) ) {
			return $this->settings_options;
		}

		if ( ! isset( $this->settings_options[ $index ] ) ) {
			return null;
		}

		return $this->settings_options[ $index ];
	}

	/**
	 * Parses the options array and adds necessary prefixes and labels names for each post type
	 *
	 * @param $posts_options , options array
	 *
	 * @return array
	 */
	public function get_custom_pots_options( $posts_options = array() ) {
		if ( ! is_array( $posts_options ) || empty( $posts_options ) ) {
			return array();
		}

		$custom_posts_options = array();

		foreach ( $this->allowed_post_types as $post_type ) {
			$options = $posts_options;
			$return  = array();
			$prefix  = $this->get_name() . '-' . $post_type . '-';

			$post      = get_post_type_object( $post_type );
			$post_name = $post->labels->name;

			foreach ( $options as $key => $option ) {
				if ( is_int( $key ) && is_array( $option ) ) {
					foreach ( $option as $op_key => $op ) {
						if ( is_array( $op ) && ! isset( $options[ $prefix . $op_key ] ) ) {
							if ( isset( $op['label'] ) ) {
								$op['label'] = $post_name . ' ' . $op['label'];
							}

							$return[ $prefix . $op_key ] = $op;
						}
					}
					continue;
				}

				if ( isset( $option['label'] ) ) {
					$option['label'] = $post_name . ' ' . $option['label'];
				}
				$return[ $prefix . $key ] = $option;
			}
			$custom_posts_options[ $this->get_name() . '-' . $post_type . '-group' ] = array(
				'type'    => 'group',
				'options' => $return
			);
		}

		return $custom_posts_options;
	}

	/**
	 * Parses the options array and adds necessary prefixes and labels names for each taxonomy
	 * This functions is used to generate general options for each taxonomy in Taxonomies metabox
	 * in extension editor from Framework.
	 *
	 * @param $taxonomies_options , options array
	 *
	 * @return array
	 */
	public function get_taxonomies_options( $taxonomies_options = array() ) {
		if ( ! is_array( $taxonomies_options ) || empty( $taxonomies_options ) ) {
			return array();
		}

		$custom_posts_options = array();

		foreach ( $this->allowed_taxonomies as $taxonomy ) {
			$options = $taxonomies_options;
			$return  = array();
			$prefix  = $this->get_name() . '-' . $taxonomy . '-';

			$tax           = get_taxonomy( $taxonomy );
			$taxonomy_name = $tax->labels->name;

			foreach ( $options as $key => $option ) {
				if ( is_int( $key ) && is_array( $option ) ) {
					foreach ( $option as $op_key => $op ) {
						if ( is_array( $op ) && ! isset( $options[ $prefix . $op_key ] ) ) {
							if ( isset( $op['label'] ) ) {
								$op['label'] = $taxonomy_name . ' ' . $op['label'];
							}

							$return[ $prefix . $op_key ] = $op;
						}
					}
					continue;
				}

				if ( isset( $option['label'] ) ) {
					$option['label'] = $taxonomy_name . ' ' . $option['label'];
				}
				$return[ $prefix . $key ] = $option;
			}

			$custom_posts_options[ $this->get_name() . '-' . $taxonomy . '-group' ] = array(
				'type'    => 'group',
				'options' => $return
			);
		}

		return $custom_posts_options;
	}

	/**
	 * @param array $value , meta keywords option array
	 *
	 * @return array
	 */
	public function use_meta_keywords( $value ) {
		if ( fw_get_db_settings_option( 'seo-titles-metas-metakeywords' ) === true ) {
			return $value;
		}

		return array();
	}

	/**
	 * Init the titles-metas extension in frontend
	 * This method get the location ( posts, pages, archives, taxonomies ) SEO metas and renders them in frontend
	 * @internal
	 */
	public function _action_add_meta() {
		$location = $this->get_parent()->get_location();
		$prefix   = $this->get_name() . '-';

		$data = array();

		switch ( $location['type'] ) {
			case 'front_page' :
				$description = fw_ext_seo_parse_meta_tags( fw_get_db_settings_option( $prefix . 'homepage-description' ) );
				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				} elseif ( isset( $location['id'] ) ) {
					$description = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'description' ) );
					if ( ! empty( $description ) ) {
						$data['description'] = array(
							'content' => $description,
							'name'    => 'description'
						);
					}
				}

				if ( $this->get_admin_options( 'seo-titles-metas-metakeywords' ) === true ) {
					$meta_keywords = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'homepage-metakeywords' ) );

					if ( ! empty( $meta_keywords ) ) {
						$data['keywords'] = array(
							'content' => $meta_keywords,
							'name'    => 'keywords'
						);
					} elseif ( isset( $location['id'] ) ) {
						$meta_keywords = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'metakeywords' ) );
						if ( ! empty( $meta_keywords ) ) {
							$data['keywords'] = array(
								'content' => $meta_keywords,
								'name'    => 'description'
							);
						}
					}
				}

				break;
			case 'blog_page' :
				$description = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'description' ) );
				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				}

				$meta_keywords = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'metakeywords' ) );
				if ( ! empty( $meta_keywords ) ) {
					$data['keywords'] = array(
						'content' => $meta_keywords,
						'name'    => 'description'
					);
				}

				break;
			case 'author_archive' :
				$description = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'author-archive-description' ) );
				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				}

				if ( $this->get_admin_options( 'seo-titles-metas-metakeywords' ) === true ) {
					$meta_keywords = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'author-archive-metakeywords' ) );

					if ( ! empty( $meta_keywords ) ) {
						$data['keywords'] = array(
							'content' => $meta_keywords,
							'name'    => 'keywords'
						);
					}
				}

				break;
			case 'date_archive' :
				$description = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'date-archive-description' ) );
				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				}

				if ( $this->get_admin_options( 'seo-titles-metas-metakeywords' ) === true ) {
					$meta_keywords = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'date-archive-metakeywords' ) );

					if ( ! empty( $meta_keywords ) ) {
						$data['keywords'] = array(
							'content' => $meta_keywords,
							'name'    => 'keywords'
						);
					}
				}

				break;
			case 'singular' :
				if ( ! in_array( $location['post_type'], $this->allowed_post_types ) ) {
					break;
				}

				$data = array();

				$description = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'description' ) );
				if ( empty( $description ) ) {
					$description = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['post_type'] . '-description' ) );
				}

				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				}

				$meta_keywords = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'metakeywords' ) );
				if ( empty( $meta_keywords ) ) {
					$meta_keywords = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['post_type'] . '-metakeywords' ) );
				}

				if ( ! empty( $meta_keywords ) ) {
					$data['keywords'] = array(
						'content' => $meta_keywords,
						'name'    => 'keywords'
					);
				}

				$robots = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['post_type'] . '-noindex' ) );
				if ( ! is_null( $robots ) && $robots == true ) {
					$data['robots'] = array(
						'content' => 'noindex,follow',
						'name'    => 'robots'
					);
				}
				break;
			case 'category' :
				if ( ! in_array( 'post_tag', $this->allowed_taxonomies ) ) {
					break;
				}

				$data = array();

				$description = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], 'category', $prefix . 'description' ) );
				if ( empty( $description ) ) {
					$description = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'category-description' ) );
				}

				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				}

				$meta_keywords = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], 'category', $prefix . 'metakeywords' ) );
				if ( empty( $meta_keywords ) ) {
					$meta_keywords = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'category-metakeywords' ) );
				}

				if ( ! empty( $meta_keywords ) ) {
					$data['keywords'] = array(
						'content' => $meta_keywords,
						'name'    => 'keywords'
					);
				}

				$robots = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'category-noindex' ) );
				if ( ! is_null( $robots ) && $robots == true ) {
					$data['robots'] = array(
						'content' => 'noindex,follow',
						'name'    => 'robots'
					);
				}
				break;
			case 'tag' :
				if ( ! in_array( 'post_tag', $this->allowed_taxonomies ) ) {
					break;
				}

				$data = array();

				$description = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], 'post_tag', $prefix . 'description' ) );
				if ( empty( $description ) ) {
					$description = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'post_tag-description' ) );
				}

				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				}

				$meta_keywords = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], 'post_tag', $prefix . 'metakeywords' ) );
				if ( empty( $meta_keywords ) ) {
					$meta_keywords = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'post_tag-metakeywords' ) );
				}

				if ( ! empty( $meta_keywords ) ) {
					$data['keywords'] = array(
						'content' => $meta_keywords,
						'name'    => 'keywords'
					);
				}

				$robots = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'post_tag-noindex' ) );
				if ( ! is_null( $robots ) && $robots == true ) {
					$data['robots'] = array(
						'content' => 'noindex,follow',
						'name'    => 'robots'
					);
				}
				break;
			case 'taxonomy' :
				if ( ! in_array( $location['taxonomy_type'], $this->allowed_taxonomies ) ) {
					break;
				}

				$data = array();

				$description = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], $location['taxonomy_type'], $prefix . 'description' ) );
				if ( empty( $description ) ) {
					$description = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['taxonomy_type'] . '-description' ) );
				}

				if ( ! empty( $description ) ) {
					$data['description'] = array(
						'content' => $description,
						'name'    => 'description'
					);
				}


				$meta_keywords = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], $location['taxonomy_type'], $prefix . 'metakeywords' ) );
				if ( empty( $meta_keywords ) ) {
					$meta_keywords = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['taxonomy_type'] . '-metakeywords' ) );
				}

				if ( ! empty( $meta_keywords ) ) {
					$data['keywords'] = array(
						'content' => $meta_keywords,
						'name'    => 'keywords'
					);
				}

				$robots = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['taxonomy_type'] . '-noindex' ) );
				if ( ! is_null( $robots ) && $robots == true ) {
					$data['robots'] = array(
						'content' => 'noindex,follow',
						'name'    => 'robots'
					);
				}
				break;
		}

		$data = apply_filters( 'fw_ext_seo_titles_metas_load_metas', $data, $location );

		foreach ( $data as $meta ) {
			if ( isset( $meta['view'] ) ) {
				echo $this->render_view( $meta['view'], $meta );
			} else {
				echo $this->render_view( 'meta', $meta );
			}
		}
	}

	/**
	 * Init the titles-metas extension in frontend
	 * This method get the location ( posts, pages, archives, taxonomies ) SEO titles and returns to the wp_title
	 *
	 * @param $title , current wordpress title, before being processed
	 * @param $sep , worpdress title separator
	 * @param $sepdirection , wordpress separator direction
	 *
	 * @return string
	 * @internal
	 */
	public function _action_add_title( $title, $sep, $sepdirection ) {
		$location = $this->get_parent()->get_location();
		$prefix   = $this->get_name() . '-';

		switch ( $location['type'] ) {
			case '404' :
				$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'not-found-title' ) );
				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}
				break;
			case 'search' :
				$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'search-page-title' ) );
				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}
				break;
			case 'author_archive' :
				$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'author-archive-title' ) );
				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}
				break;
			case 'date_archive' :
				$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'date-archive-title' ) );
				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}
				break;
			case 'front_page' :
				$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'homepage-title' ) );
				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				} elseif ( isset( $location['id'] ) ) {
					$fw_title = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'title' ) );

					if ( ! empty( $fw_title ) ) {
						$title = $fw_title;
					}
				}
				break;
			case 'blog_page' :
				$fw_title = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'title' ) );

				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}
				break;
			case 'singular' :
				if ( ! in_array( $location['post_type'], $this->allowed_post_types ) ) {
					break;
				}

				$fw_title = fw_ext_seo_parse_meta_tags( fw_get_db_post_option( $location['id'], $prefix . 'title' ) );
				if ( empty( $fw_title ) ) {
					$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['post_type'] . '-title' ) );
				}

				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}
				break;
			case 'category' :
				if ( ! in_array( 'post_tag', $this->allowed_taxonomies ) ) {
					break;
				}

				$fw_title = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], 'category', $prefix . 'title' ) );

				if ( empty( $fw_title ) ) {
					$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'category-title' ) );
				}
				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}

				break;
			case 'tag' :
				if ( ! in_array( 'post_tag', $this->allowed_taxonomies ) ) {
					break;
				}


				$fw_title = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], 'post_tag', $prefix . 'title' ) );
				if ( empty( $fw_title ) ) {
					$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . 'post_tag-title' ) );
				}

				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}

				break;
			case 'taxonomy' :
				if ( ! in_array( $location['taxonomy_type'], $this->allowed_taxonomies ) ) {
					break;
				}

				$fw_title = fw_ext_seo_parse_meta_tags( fw_get_db_term_option( $location['id'], $location['taxonomy_type'], $prefix . 'title' ) );
				if ( empty( $fw_title ) ) {
					$fw_title = fw_ext_seo_parse_meta_tags( $this->get_admin_options( $prefix . $location['taxonomy_type'] . '-title' ) );
				}

				if ( ! empty( $fw_title ) ) {
					$title = $fw_title;
				}
				break;
		}

		$title = apply_filters( 'fw_ext_seo_titles_metas_load_title', $title, $sep, $sepdirection, $location );

		return $title;
	}

	/**
	 * Defines the custom posts and taxonomies allowed to be used by this extension
	 * @internal
	 */
	public function _action_set_allowed_items() {

		$post_types     = get_post_types( array( 'public' => true ) );
		$excluded_posts = $this->get_config( 'excluded_post_types' );
		unset( $post_types['nav_menu_item'] );
		unset( $post_types['revision'] );

		foreach ( $excluded_posts as $type ) {
			if ( isset( $post_types[ $type ] ) ) {
				unset( $post_types[ $type ] );
			}
		}
		$this->allowed_post_types = $post_types;

		$taxonomies          = get_taxonomies();
		$excluded_taxonomies = $this->get_config( 'excluded_taxonomies' );

		unset( $taxonomies['nav_menu'] );
		unset( $taxonomies['link_category'] );
		unset( $taxonomies['post_format'] );

		foreach ( $excluded_taxonomies as $type ) {
			if ( isset( $taxonomies[ $type ] ) ) {
				unset( $taxonomies[ $type ] );
			}
		}
		$this->allowed_taxonomies = $taxonomies;
	}

	/**
	 * Adds the extension settings tab in Framework in SEO extension
	 *
	 * @param $seo_options , holds the general options from extension config file
	 *
	 * @return array
	 * @internal
	 */
	public function _filter_set_framework_titles_metas_tab( $seo_options ) {
		$titles_metas_options = fw_ext_seo_titles_meta_get_settings_options();

		if ( is_array( $titles_metas_options ) && ! empty( $titles_metas_options ) ) {
			return array_merge( $seo_options, $titles_metas_options );
		}

		return $seo_options;
	}

	/**
	 * Adds the extension settings metabox in custom posts editor page
	 *
	 * @param $seo_options , contains the custom post options
	 * @param $post_type , contains the custom post type
	 *
	 * @return array
	 * @internal
	 */
	public function _filter_set_custom_posts_titles_metas_metabox( $seo_options, $post_type ) {
		if ( ! in_array( $post_type, $this->allowed_post_types ) ) {
			return $seo_options;
		}

		$titles_metas_options = fw_ext_seo_titles_meta_get_post_types_options();

		if ( is_array( $titles_metas_options ) && ! empty( $titles_metas_options ) ) {
			return array_merge( $seo_options, $titles_metas_options );
		}

		return $seo_options;
	}

	/**
	 * Adds the extension settings metabox in taxonomies editor page
	 *
	 * @param $seo_options , contains the taxonomy options
	 * @param $taxonomy , contains the taxonomy type
	 *
	 * @return array
	 * @internal
	 */
	public function _filter_set_taxonomies_titles_metas_options( $seo_options, $taxonomy ) {
		if ( ! in_array( $taxonomy, $this->allowed_taxonomies ) ) {
			return $seo_options;
		}

		$titles_metas_options = fw_ext_seo_titles_meta_get_taxonomies_options();

		if ( is_array( $titles_metas_options ) && ! empty( $titles_metas_options ) ) {
			return array_merge( $seo_options, $titles_metas_options );
		}

		return $seo_options;
	}

	/**
	 * Adds the extension general option in SEO extension General Settings tab
	 *
	 * @param $options , holds the general options from extension config file
	 *
	 * @return array
	 * @internal
	 */
	public function _filter_set_framework_titles_metas_options( $options ) {
		$general_options = fw_ext_seo_titles_meta_get_general_settings_options();

		if ( is_array( $general_options ) && ! empty( $general_options ) ) {
			return array_merge( $options, $general_options );
		}

		return $options;
	}
}