<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_SEO extends FW_Extension {
	/**
	 * Holds the SEO tags that can be used by sub-extensions
	 * This member cannot be accessed directly, only by "get_seo_tags" method
	 * @var array
	 */
	private $seo_tags = array();

	private $name = 'SEO';

	private $form = null;

	/**
	 * Holds the current location in front-end
	 * This member cannot be accessed directly, only by "get_location" method
	 * @var array
	 */
	private $current_location = array();

	/**
	 * @internal
	 */
	public function _init() {
		if ( is_admin() ) {

			$this->name = __( 'SEO', 'fw' );

			$this->form = new FW_Form( 'fw_ext_' . $this->get_name(), array(
				'render'   => array( $this, '_form_render' ),
				'validate' => array( $this, '_form_validate' ),
				'save'     => array( $this, '_form_save' ),
			) );

			$this->add_admin_actions();
			$this->add_admin_filters();
		} else {
			$this->add_theme_actions();
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_add_settings_menu() {
		add_submenu_page( 'options-general.php',
			$this->name, __( 'Search Engines', 'fw' ),
			'manage_options',
			$this->get_name() . '-settings',
			array( $this, '_display_settings_page' )
		);
	}

	public function _admin_action_add_static() {
		$screen = array(
			'only'  => array(
				array(
					'base'  => 'post'
				)
			)
		);
		if ( fw_current_screen_match($screen) ) {
			wp_enqueue_style( $this->get_name() . '-style', $this->get_declared_URI('/static/css/style.css') );
		}
	}

	private function add_admin_actions() {
		add_action( 'admin_menu', array( $this, '_admin_action_add_settings_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_action_add_static' ) );
	}

	/**
	 * Init the admin area filters
	 */
	private function add_admin_filters() {
		add_filter( 'fw_post_options', array( $this, '_admin_filter_set_custom_posts_seo_options' ), 10, 2 );
		add_filter( 'fw_taxonomy_options', array( $this, '_admin_filter_set_taxonomy_seo_options' ), 10, 2 );
	}

	/**
	 * Init the frontend area actions
	 */
	private function add_theme_actions() {
		add_action( 'wp', array( $this, '_action_set_location' ) );
		add_action( 'fw_ext_seo_init_location', array( $this, '_theme_action_update_seo_tags' ) );
	}

	/**
	 * Returns General SEO options in the framework settings.
	 * @return array
	 */
	private function get_seo_framework_options() {
		return array(
			'general-tab' => array(
				'title'   => __( 'General', 'fw' ),
				'type'    => 'tab',
				'options' => array(
					'general-settings' => array(
						'title'   => __( 'General Settings', 'fw' ),
						'type'    => 'box',
						'options' => array()
					)
				)
			),
		);
	}

	/**
	 * @param $tag , SEO tag name
	 *
	 * @return string
	 */
	private function parse_seo_tag_helper( $tag ) {
		$tag_str = trim( str_replace( '%%', '', $tag[0] ) );
		$seo_tag = $this->get_seo_tags( $tag_str );

		return isset( $seo_tag['value'] ) ? $seo_tag['value'] : '';
	}

	/**
	 * Return SEO options array;
	 *
	 * @return array
	 * @internal
	 */
	private function get_seo_options() {
		$seo_options = $this->get_seo_framework_options();

		$general_settings = $seo_options['general-tab']['options']['general-settings']['options'];
		foreach ( apply_filters( 'fw_ext_seo_general_setting_admin_options', $general_settings ) as $opt_id => $options ) {
			if ( isset( $general_settings[ $opt_id ] ) ) {
				FW_Flash_Messages::add( 'fw-ext-seo-add-tabs', sprintf( __( 'Unable to set the %s option, as there is already present an option with such id', 'fw' ), $opt_id ), 'warning' );
				continue;
			}
			$general_settings[ $opt_id ] = $options;
		}
		$seo_options['general-tab']['options']['general-settings']['options'] = $general_settings;

		$general_tab = $seo_options['general-tab']['options'];

		foreach ( apply_filters( 'fw_ext_seo_general_tab_admin_options', $general_tab ) as $tab_id => $options ) {
			$general_tab[ $tab_id ] = $options;
		}

		$seo_options['general-tab']['options'] = $general_tab;

		foreach ( apply_filters( 'fw_ext_seo_admin_options', array() ) as $tab_id => $options ) {
			if ( isset( $seo_options[ $tab_id ] ) ) {
				FW_Flash_Messages::add( 'fw-ext-seo-add-tabs', sprintf( __( 'Unable to set the %s tab, as it exists already', 'fw' ), $tab_id ), 'warning' );
				continue;
			}
			$seo_options[ $tab_id ] = $options;
		}

		return $seo_options;
	}

	/**
	 * Init seo tags
	 * There will be defined only the name and description of the tags, and the value of few simple tags.
	 * The value of the tags is updated by "_action_init_update_tags" method on "wp" action
	 */
	public function init_seo_tags() {
		if ( ! empty( $this->seo_tags ) ) {
			return;
		}

		$this->seo_tags['sitename'] = array(
			'name'  => '%%sitename%%',
			'desc'  => __( 'Site name', 'fw' ),
			'value' => get_bloginfo( 'name' ),
		);

		$this->seo_tags['sitedesc'] = array(
			'name'  => '%%sitedesc%%',
			'desc'  => __( 'Site description', 'fw' ),
			'value' => get_bloginfo( 'description' ),
		);

		$this->seo_tags['currenttime'] = array(
			'name'  => '%%currenttime%%',
			'desc'  => __( 'Current time', 'fw' ),
			'value' => date( 'H:i' ),
		);

		$this->seo_tags['currentdate'] = array(
			'name'  => '%%currentdate%%',
			'desc'  => __( 'Current date', 'fw' ),
			'value' => date( 'M jS Y' ),
		);

		$this->seo_tags['currentmonth'] = array(
			'name'  => '%%currentmonth%%',
			'desc'  => __( 'Current month', 'fw' ),
			'value' => date( 'Y' ),
		);

		$this->seo_tags['currentyear'] = array(
			'name'  => '%%currentyear%%',
			'desc'  => __( 'Current year', 'fw' ),
			'value' => date( 'Y' ),
		);

		$this->seo_tags['date'] = array(
			'name'  => '%%date%%',
			'desc'  => __( 'Date of the post/page', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['title'] = array(
			'name'  => '%%title%%',
			'desc'  => __( 'Title of the post/page/term', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['excerpt'] = array(
			'name'  => '%%excerpt%%',
			'desc'  => __( 'Excerpt of the current post, of auto-generate if it is not set', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['excerpt_only'] = array(
			'name'  => '%%excerpt_only%%',
			'desc'  => __( 'Excerpt of the current post, without auto-generation', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['post_tags'] = array(
			'name'  => '%%post_tags%%',
			'desc'  => __( 'Post tags, separated by coma', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['post_categories'] = array(
			'name'  => '%%post_categories%%',
			'desc'  => __( 'Post categories, separated by coma', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['description'] = array(
			'name'  => '%%description%%',
			'desc'  => __( 'Category/tag/term description', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['term_title'] = array(
			'name'  => '%%term_title%%',
			'desc'  => __( 'Term title', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['modified'] = array(
			'name'  => '%%modified%%',
			'desc'  => __( 'Post modified time', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['id'] = array(
			'name'  => '%%id%%',
			'desc'  => __( 'Post/page id', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['author_name'] = array(
			'name'  => '%%author_name%%',
			'desc'  => __( 'Post/page author "nicename"', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['author_id'] = array(
			'name'  => '%%author_id%%',
			'desc'  => __( 'Post/page author id', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['searchphrase'] = array(
			'name'  => '%%searchphrase%%',
			'desc'  => __( 'Search phrase in search page', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['pagenumber'] = array(
			'name'  => '%%pagenumber%%',
			'desc'  => __( 'Page number', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['max_page'] = array(
			'name'  => '%%max_page%%',
			'desc'  => __( 'Page number', 'fw' ),
			'value' => '',
		);

		$this->seo_tags['caption'] = array(
			'name'  => '%%caption%%',
			'desc'  => __( 'Attachment caption', 'fw' ),
			'value' => '',
		);

		foreach ( apply_filters( 'fw_ext_seo_init_tags', array() ) as $tag_id => $tag ) {
			if ( isset( $this->seo_tags[ $tag_id ] ) ) {
				continue;
			}
			$this->seo_tags[ $tag_id ] = $tag;
		}
	}

	/**
	 * @internal
	 */
	public function _display_settings_page() {
		echo '<h2>'. __( 'Search Engines', 'fw' ) .'</h2><p></p>';
		echo '<div class="wrap">';
		$this->form->render();
		echo '</div>';
	}

	/**
	 * @internal
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function _form_render( $data ) {
		$options = $this->get_seo_options();

		if ( empty( $options ) ) {
			return $data;
		}

		$values = FW_Request::POST( FW_Option_Type::get_default_name_prefix(), fw_get_db_extension_data( $this->get_name(), 'options' ) );

		echo fw()->backend->render_options( $options, $values );

		$data['submit']['html'] = '<button class="button-primary button-large">' . __( 'Save', 'fw' ) . '</button>';

		unset( $options );

		return $data;
	}

	/**
	 * @internal
	 * @param $errors
	 * @return array
	 */
	public function _form_validate( $errors ) {
		if (!current_user_can('manage_options')) {
			$errors[] = __('You have no permission to change SEO options', 'fw');
		}

		return $errors;
	}

	/**
	 * @internal
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function _form_save( $data ) {
		fw_set_db_extension_data( $this->get_name(), 'options', fw_get_options_values_from_input( $this->get_seo_options() ) );

		do_action( 'fw_' . $this->get_name() . '_form_save' );

		$data['redirect'] = fw_current_url();

		return $data;
	}

	/**
	 * Return the current page type in front-end
	 * @return array
	 */
	public function get_location() {
		return $this->current_location;
	}

	/**
	 * Parses string values and replaces the seo tags with their values
	 * This method should be called after wordpress "wp" action
	 *
	 * @param $value , option value that should be parsed
	 *
	 * @return string
	 */
	public function parse_seo_tags( $value ) {
		$value = strip_tags( $value );

		return preg_replace_callback( '/%%[a-z|0-9|_|-]*%%/', array( $this, 'parse_seo_tag_helper' ), $value );
	}

	/**
	 * Determine the current frontend page location
	 * @internal
	 */
	public function _action_set_location() {
		global $wp_query;
		$return = array();

		if ( is_404() ) {
			$return['type'] = '404';
		} elseif ( is_search() ) {
			$return['type'] = 'search';
		} elseif ( is_front_page() ) {
			$return['type'] = 'front_page';

			if ( ! is_home() ) {
				$return['id'] = get_option( 'page_on_front' );
			}
		} elseif ( is_home() ) {
			$return['type'] = 'blog_page';
			$return['id']   = get_option( 'page_for_posts' );
		} elseif ( is_singular() ) {
			global $post;
			$return['type']      = 'singular';
			$return['id']        = $post->ID;
			$return['post_type'] = $post->post_type;
		} elseif ( is_category() ) {
			$return['type']          = 'category';
			$return['taxonomy_type'] = get_query_var( 'taxonomy' );
			$return['id']            = get_query_var( 'cat' );
		} elseif ( is_tag() ) {
			$return['type']          = 'tag';
			$return['taxonomy_type'] = get_query_var( 'taxonomy' );
			$return['id']            = get_query_var( 'tag_id' );
		} elseif ( is_tax() ) {
			$return['type']          = 'taxonomy';
			$return['taxonomy_type'] = get_query_var( 'taxonomy' );
			$return['id']            = get_queried_object()->term_id;
		} elseif ( is_author() ) {
			$return['type'] = 'author_archive';
		} elseif ( is_date() ) {
			$return['type'] = 'date_archive';
		} elseif ( is_archive() ) {
			$return['type'] = 'archive';
		}

		/*
		 * Check if the location has pagination and add the page
		 */
		$paged                  = get_query_var( 'paged' );
		$return['paged']        = ( $paged == 0 ) ? 1 : $paged;
		$return['max_pages']    = $wp_query->max_num_pages;
		$return                 = apply_filters( 'fw_ext_seo_get_location', $return );
		$this->current_location = $return;

		do_action( 'fw_ext_seo_init_location', $this->current_location );
	}

	/**
	 * Returns the SEO tags: %%title%%, %%excerpt%%, ...
	 * This function should be used after wordpress "wp" action
	 * If the method is called without parameters it returns the array of all tags and their values
	 * If the method is called with parameter, it returns the tag
	 * If the method is called with parameter and it is wrong, it returns an empty string
	 *
	 * @param $tag , name of the specific tag
	 *
	 * @return array
	 */
	public function get_seo_tags( $tag = null ) {
		$this->init_seo_tags();

		if ( is_null( $tag ) ) {
			return $this->seo_tags;
		}

		if ( isset( $this->seo_tags[ $tag ] ) ) {
			return $this->seo_tags[ $tag ];
		}

		return array();
	}

	/**
	 * Update the SEO key tags values on wordpress "wp" action,
	 * such as %%title%%, %%excerpt%%, values can only be initialised after "wp" action
	 * @internal
	 */
	public function _theme_action_update_seo_tags( $location ) {
		if ( empty( $this->seo_tags ) ) {
			$this->get_seo_tags();
		}

		switch ( $location['type'] ) {
			case 'search' :
				$this->seo_tags['searchphrase']['value'] = get_search_query();
				$this->seo_tags['pagenumber']            = $location['paged'];
				$this->seo_tags['max_page']              = $location['max_pages'];
				break;
			case 'author_archive' :
				$this->seo_tags['author_id']['value']   = get_query_var( 'author' );
				$this->seo_tags['author_name']['value'] = get_the_author_meta( 'nickname', get_query_var( 'author' ) );
				$this->seo_tags['pagenumber']           = $location['paged'];
				$this->seo_tags['max_page']             = $location['max_pages'];
				break;
			case 'date_archive' :
				$this->seo_tags['pagenumber'] = $location['paged'];
				$this->seo_tags['max_page']   = $location['max_pages'];
				break;
			case 'front_page' :
				$this->seo_tags['pagenumber'] = $location['paged'];
				$this->seo_tags['max_page']   = $location['max_pages'];
				break;
			case 'blog_page' :
				$this->seo_tags['pagenumber'] = $location['paged'];
				$this->seo_tags['max_page']   = $location['max_pages'];
				break;
			case 'singular' :
				global $post;
				$this->seo_tags['date']['value']         = get_the_date();
				$this->seo_tags['title']['value']        = get_the_title();
				$this->seo_tags['excerpt']['value']      = ( has_excerpt() ) ? get_the_excerpt() : wp_trim_excerpt();
				$this->seo_tags['excerpt_only']['value'] = ( has_excerpt() ) ? get_the_excerpt() : '';
				$this->seo_tags['modified']['value']     = $post->post_modified;
				$this->seo_tags['id']['value']           = $post->ID;
				$this->seo_tags['author_id']['value']    = $post->post_author;
				$this->seo_tags['author_name']['value']  = get_the_author_meta( 'nickname', $post->post_author );
				if ( $location['post_type'] == 'attachment' ) {
					$this->seo_tags['caption']['value'] = ( has_excerpt() ) ? get_the_excerpt() : '';
				}

				$categories = wp_get_post_categories( $post->ID );
				foreach ( $categories as $cat_id ) {
					$category = get_category( $cat_id );
					$this->seo_tags['post_categories']['value'] .= $category->name . ', ';
				}
				$this->seo_tags['post_categories']['value'] = rtrim( $this->seo_tags['post_categories']['value'], ', ' );

				$tags = wp_get_post_tags( $post->ID );
				foreach ( $tags as $tag_id ) {
					$tag = get_tag( $tag_id );
					$this->seo_tags['post_tags']['value'] .= $tag->name . ', ';
				}
				$this->seo_tags['post_tags']['value'] = rtrim( $this->seo_tags['post_tags']['value'], ', ' );
				break;
			case 'tag' :
				$this->seo_tags['title']['value']       = single_term_title( '', false );
				$this->seo_tags['description']['value'] = term_description( $location['id'], $location['taxonomy_type'] );
				$this->seo_tags['pagenumber']           = $location['paged'];
				$this->seo_tags['max_page']             = $location['max_pages'];
				break;
			case 'category' :
				$this->seo_tags['title']['value']       = single_term_title( '', false );
				$this->seo_tags['description']['value'] = term_description( $location['id'], $location['taxonomy_type'] );
				$this->seo_tags['pagenumber']           = $location['paged'];
				$this->seo_tags['max_page']             = $location['max_pages'];
				break;
			case 'taxonomy' :
				$this->seo_tags['title']['value']       = single_term_title( '', false );
				$this->seo_tags['description']['value'] = term_description( $location['id'], $location['taxonomy_type'] );
				$this->seo_tags['pagenumber']           = $location['paged'];
				$this->seo_tags['max_page']             = $location['max_pages'];
				break;
		}

		$this->seo_tags = apply_filters( 'fw_ext_seo_update_tags', $this->seo_tags, $location );
	}

	/**
	 * Inserts the SEO metabox tab in custom posts editor, where sub-extensions will attach their options
	 *
	 * @param $post_options , array of the current custom post options
	 * @param $post_type , custom post type
	 *
	 * @return array
	 * @internal
	 */
	public function _admin_filter_set_custom_posts_seo_options( $post_options, $post_type ) {
		$seo_options = array(
			'title'   => __( 'Search Engines', 'fw' ),
			'type'    => 'tab',
			'options' => array()
		);

		foreach ( apply_filters( 'fw_ext_seo_post_type_options', array(), $post_type ) as $tab_id => $options ) {
			if ( isset( $seo_options['options'][ $tab_id ] ) ) {
				continue;
			}
			$seo_options['options'][ $tab_id ] = $options;
		}

		if ( ( count( $seo_options['options'] ) == 1 ) ) {
			$first_value = reset( $seo_options['options'] );
			if ( isset( $first_value['type'] ) && ( $first_value['type'] == 'tab' ) ) {
				$seo_options['options'] = $first_value['options'];
			}
		}

		if ( is_array( $seo_options['options'] ) && ! empty( $seo_options['options'] ) ) {

			if ( isset( $post_options['main'] ) && $post_options['main']['type'] == 'box' ) {
				$seo_options['type'] = 'tab';
				$post_options['main']['options'][ $this->get_name() ] = $seo_options;
			} else {
				$seo_options = array(
					'title' => false,
					'type'    => 'box',
					'options' => array(
						$this->get_name() => $seo_options
					)
				);
				$post_options[ $this->get_name() ] = $seo_options;
			}
		}

		return $post_options;
	}

	/**
	 * Inserts the SEO options section in taxonomy editor, where sub-extensions will attach their options
	 *
	 * @param $tax_options , array of the current taxonomy options
	 * @param $taxonomy , taxonomy type
	 *
	 * @return array
	 * @internal
	 */
	public function _admin_filter_set_taxonomy_seo_options( $tax_options, $taxonomy ) {
		$seo_options = array();

		foreach ( apply_filters( 'fw_ext_seo_taxonomy_options', array(), $taxonomy ) as $group_id => $options ) {
			if ( isset( $seo_options[ $group_id ] ) ) {
				continue;
			}
			$seo_options[ $group_id ] = $options;
		}

		if ( is_array( $seo_options ) && ! empty( $seo_options ) ) {
			return array_merge( $tax_options, $seo_options );
		}

		return $tax_options;
	}
}