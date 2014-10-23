<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Portfolio extends FW_Extension {

	private $post_type = 'fw-portfolio';
	private $slug = 'project';
	private $taxonomy_slug = 'portfolio';
	private $taxonomy_name = 'fw-portfolio-category';

	/**
	 * @internal
	 */
	public function _init() {
		$this->define_slugs();

		add_action( 'init', array( $this, '_action_register_post_type' ) );
		add_action( 'init', array( $this, '_action_register_taxonomy' ) );

		if ( is_admin() ) {
			$this->add_admin_actions();
			$this->add_admin_filters();
		} else {
			$this->add_theme_filters();
		}
	}

	private function define_slugs() {
		$this->slug          = apply_filters( 'fw_ext_portfolio_post_slug', $this->slug );
		$this->taxonomy_slug = apply_filters( 'fw_ext_portfolio_taxonomy_slug', $this->taxonomy_slug );
	}

	public function add_admin_actions() {
		add_action( 'admin_menu', array( $this, '_action_admin_rename_porjects' ) );
		add_action( 'restrict_manage_posts', array( $this, '_action_admin_add_portfolio_edit_page_filter' ) );
		// listing screen
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array(
			$this,
			'_action_admin_manage_custom_column'
		), 10, 2 );

		// add / edit screen
		add_action( 'fw_post_options', array( $this, '_action_admin_add_post_options' ), 10, 2 );
		add_action( 'do_meta_boxes', array( $this, '_action_admin_featured_image_label' ) );

		add_action( 'admin_enqueue_scripts', array( $this, '_action_admin_add_static' ) );

		add_action( 'admin_head', array( $this, '_action_admin_initial_nav_menu_meta_boxes' ), 999 );
	}

	public function add_admin_filters() {
		add_filter( 'parse_query', array( $this, '_filter_admin_filter_portfolios_by_portfolio_category' ), 10, 2 );
		add_filter( 'months_dropdown_results', array( $this, '_filter_admin_remove_select_by_date_filter' ) );
		add_filter( 'manage_edit-' . $this->post_type . '_columns', array(
			$this,
			'_filter_adminmanage_edit_columns'
		), 10, 1 );
	}

	public function add_theme_filters() {
		add_filter( 'template_include', array( $this, '_filter_theme_template_include' ) );
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_static() {
		$projects_listing_screen  = array(
			'only' => array(
				array(
					'post_type' => $this->post_type,
					'base'      => array( 'edit' )
				)
			)
		);
		$projects_add_edit_screen = array(
			'only' => array(
				array(
					'post_type' => $this->post_type,
					'base'      => 'post'
				)
			)
		);

		if ( fw_current_screen_match( $projects_listing_screen ) ) {
			wp_enqueue_style(
				'fw-extension-' . $this->get_name() . '-listing',
				$this->get_declared_URI( '/static/css/admin-listing.css' ),
				array(),
				fw()->manifest->get_version()
			);
		}

		if ( fw_current_screen_match( $projects_add_edit_screen ) ) {
			wp_enqueue_style(
				'fw-extension-' . $this->get_name() . '-add-edit',
				$this->get_declared_URI( '/static/css/admin-add-edit.css' ),
				array(),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				'fw-extension-' . $this->get_name() . '-add-edit',
				$this->get_declared_URI( '/static/js/admin-add-edit.js' ),
				array( 'jquery' ),
				fw()->manifest->get_version(),
				true
			);
		}
	}

	/**
	 * @internal
	 */
	public function _action_register_post_type() {

		$post_names = apply_filters( 'fw_ext_projects_post_type_name', array(
			'singular' => __( 'Project', 'fw' ),
			'plural'   => __( 'Projects', 'fw' )
		) );

		register_post_type( $this->post_type, array(
			'labels'             => array(
				'name'               => $post_names['plural'], //__( 'Portfolio', 'fw' ),
				'singular_name'      => $post_names['singular'], //__( 'Portfolio project', 'fw' ),
				'add_new'            => __( 'Add New', 'fw' ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'fw' ), $post_names['singular'] ),
				'edit'               => __( 'Edit', 'fw' ),
				'edit_item'          => sprintf( __( 'Edit %s', 'fw' ), $post_names['singular'] ),
				'new_item'           => sprintf( __( 'New %s', 'fw' ), $post_names['singular'] ),
				'all_items'          => sprintf( __( 'All %s', 'fw' ), $post_names['plural'] ),
				'view'               => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
				'view_item'          => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
				'search_items'       => sprintf( __( 'Search %s', 'fw' ), $post_names['plural'] ),
				'not_found'          => sprintf( __( 'No %s Found', 'fw' ), $post_names['plural'] ),
				'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'fw' ), $post_names['plural'] ),
				'parent_item_colon'  => '' /* text for parent types */
			),
			'description'        => __( 'Create a portfolio item', 'fw' ),
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'publicly_queryable' => true,
			/* queries can be performed on the front end */
			'has_archive'        => true,
			'rewrite'            => array(
				'slug' => $this->slug
			),
			'menu_position'      => 4,
			'show_in_nav_menus'  => false,
			'menu_icon'          => 'dashicons-portfolio',
			'hierarchical'       => false,
			'query_var'          => true,
			/* Sets the query_var key for this post type. Default: true - set to $post_type */
			'supports'           => array(
				'title', /* Text input field to create a post title. */
				'editor',
				'thumbnail', /* Displays a box for featured image. */
			),
			'capabilities'       => array(
				'edit_post'              => 'edit_pages',
				'read_post'              => 'edit_pages',
				'delete_post'            => 'edit_pages',
				'edit_posts'             => 'edit_pages',
				'edit_others_posts'      => 'edit_pages',
				'publish_posts'          => 'edit_pages',
				'read_private_posts'     => 'edit_pages',
				'read'                   => 'edit_pages',
				'delete_posts'           => 'edit_pages',
				'delete_private_posts'   => 'edit_pages',
				'delete_published_posts' => 'edit_pages',
				'delete_others_posts'    => 'edit_pages',
				'edit_private_posts'     => 'edit_pages',
				'edit_published_posts'   => 'edit_pages',
			),
		) );

	}

	/**
	 * @internal
	 */
	public function _action_register_taxonomy() {

		$category_names = apply_filters( 'fw_ext_portfolio_category_name', array(
			'singular' => __( 'Category', 'fw' ),
			'plural'   => __( 'Categories', 'fw' )
		) );

		$labels = array(
			'name'              => sprintf( _x( 'Portfolio %s', 'taxonomy general name', 'fw' ), $category_names['plural'] ),
			'singular_name'     => sprintf( _x( 'Portfolio %s', 'taxonomy singular name', 'fw' ), $category_names['singular'] ),
			'search_items'      => sprintf( __( 'Search %s', 'fw' ), $category_names['plural'] ),
			'all_items'         => sprintf( __( 'All %s', 'fw' ), $category_names['plural'] ),
			'parent_item'       => sprintf( __( 'Parent %s', 'fw' ), $category_names['singular'] ),
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'fw' ), $category_names['singular'] ),
			'edit_item'         => sprintf( __( 'Edit %s', 'fw' ), $category_names['singular'] ),
			'update_item'       => sprintf( __( 'Update %s', 'fw' ), $category_names['singular'] ),
			'add_new_item'      => sprintf( __( 'Add New %s', 'fw' ), $category_names['singular'] ),
			'new_item_name'     => sprintf( __( 'New %s Name', 'fw' ), $category_names['singular'] ),
			'menu_name'         => sprintf( __( '%s', 'fw' ), $category_names['plural'] )
		);
		$args   = array(
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug' => $this->taxonomy_slug
			),
		);

		register_taxonomy( $this->taxonomy_name, esc_attr( $this->post_type ), $args );
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_post_options( $options, $post_type ) {
		if ( $post_type === $this->post_type ) {
			$options[] = array(
				'general' => array(
					'context' => 'side',
					'title'   => __( 'Project', 'fw' ) . ' ' . __( 'Gallery', 'fw' ),
					'type'    => 'box',
					'options' => array(
						'project-gallery' => array(
							'label' => false,
							'type'  => 'multi-upload',
							'desc'  => false,
							'texts' => array(
								'button_add'  => __( 'Set project gallery', 'fw' ),
								'button_edit' => __( 'Edit project gallery', 'fw' )
							)
						)
					)
				)
			);
		}

		return $options;
	}

	/**
	 * internal
	 */
	public function _action_admin_rename_porjects() {
		global $menu;

		foreach ( $menu as $key => $menu_item ) {
			if ( $menu_item[2] == 'edit.php?post_type=' . $this->post_type ) {
				$menu[ $key ][0] = __( 'Portfolio', 'fw' );
			}
		}
	}

	/**
	 * Change the title of Featured Image Meta box
	 * @internal
	 */
	public function _action_admin_featured_image_label() {
		remove_meta_box( 'postimagediv', $this->post_type, 'side' );
		add_meta_box( 'postimagediv', __( 'Project Cover Image', 'fw' ), 'post_thumbnail_meta_box', $this->post_type, 'side' );
	}

	/**
	 * @internal
	 */
	public function _action_admin_manage_custom_column( $column_name, $id ) {
		switch ( $column_name ) {
			case 'image':
				if ( get_the_post_thumbnail( intval( $id ) ) ) {
					$value = '<a href="' . get_edit_post_link( $id, true ) . '" title="' . esc_attr( __( 'Edit this item', 'fw' ) ) . '">' .
					         '<img src="' . fw_resize( get_post_thumbnail_id( intval( $id ) ), 150, 100, true ) . '" width="150" height="100" >' .
					         '</a>';
				} else {
					$value = '<img src="' . $this->get_declared_URI( '/static/images/no-image.png' ) . '"/>';
				}
				echo $value;
				break;

			default:
				break;
		}
	}

	/**
	 * @internal
	 */
	public function _filter_adminmanage_edit_columns( $columns ) {
		$new_columns          = array();
		$new_columns['cb']    = $columns['cb']; // checkboxes for all projects page
		$new_columns['image'] = __( 'Cover Image', 'fw' );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * @internal
	 *
	 * @param WP_Query $query
	 *
	 * @return WP_Query
	 */
	public function _filter_admin_filter_portfolios_by_portfolio_category( $query ) {
		$screen = fw_current_screen_match( array(
			'only' => array(
				'base'      => 'edit',
				'id'        => 'edit-' . $this->post_type,
				'post_type' => $this->post_type,
			)
		) );

		if ( ! $screen || ! $query->is_main_query() ) {
			return $query;
		}

		$filter_value = FW_Request::GET( $this->get_name() . '-filter-by-portfolio-category' );

		if ( empty( $filter_value ) ) {
			return $query;
		}

		$filter_value = (int) $filter_value;

		$query->set( 'tax_query', array(
			array(
				'taxonomy' => $this->taxonomy_name,
				'field'    => 'id',
				'terms'    => $filter_value,
			)
		) );

		return $query;
	}

	/**
	 * @internal
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public function _filter_admin_remove_select_by_date_filter( $filters ) {
		$screen = array(
			'only' => array(
				'base' => 'edit',
				'id'   => 'edit-' . $this->post_type,
			)
		);

		if ( ! fw_current_screen_match( $screen ) ) {
			return $filters;
		}

		return array();
	}

	/**
	 * @internal
	 */
	public function _action_admin_add_portfolio_edit_page_filter() {
		$screen = fw_current_screen_match( array(
			'only' => array(
				'base'      => 'edit',
				'id'        => 'edit-' . $this->post_type,
				'post_type' => $this->post_type,
			)
		) );

		if ( ! $screen ) {
			return;
		}

		$terms = get_terms( $this->taxonomy_name );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			echo '<select name="' . $this->get_name() . '-filter-by-portfolio-category"><option value="0">' . __( 'View all categories', 'fw' ) . '</option></select>';

			return;
		}

		$get = FW_Request::GET( $this->get_name() . '-filter-by-portfolio-category' );
		$id  = ( ! empty( $get ) ) ? (int) $get : 0;

		$dropdown_options = array(
			'selected'        => $id,
			'name'            => $this->get_name() . '-filter-by-portfolio-category">',
			'taxonomy'        => $this->taxonomy_name,
			'show_option_all' => __( 'View all categories', 'fw' ),
			'hide_empty'      => true,
			'hierarchical'    => 1,
			'show_count'      => 0,
			'orderby'         => 'name',
		);

		wp_dropdown_categories( $dropdown_options );
	}

	/**
	 * @internal
	 */
	public function _filter_theme_template_include( $template ) {
		if ( is_singular( $this->post_type ) ) {
			return $this->locate_path( '/views/single.php' );
		} else if ( is_tax( $this->taxonomy_name ) ) {
			return $this->locate_path( '/views/taxonomy.php' );
		} else if ( is_post_type_archive( $this->post_type ) ) {
			return $this->locate_path( '/views/archive.php' );
		}

		return $template;
	}

	public function get_settings() {

		$response = array(
			'post_type'     => $this->post_type,
			'slug'          => $this->slug,
			'taxonomy_slug' => $this->taxonomy_slug,
			'taxonomy_name' => $this->taxonomy_name
		);

		return $response;
	}

	public function get_image_sizes() {
		return $this->get_config( 'image_sizes' );
	}

	public function get_post_type_name() {
		return $this->post_type;
	}

	public function get_taxonomy_name() {
		return $this->taxonomy_name;
	}

	public function _action_admin_initial_nav_menu_meta_boxes() {
		$screen = array(
			'only' => array(
				'base' => 'nav-menus'
			)
		);
		if ( ! fw_current_screen_match( $screen ) ) {
			return;
		}

		if ( get_user_option( 'fw-metaboxhidden_nav-menus' ) !== false ) {
			return;
		}

		$user              = wp_get_current_user();
		$hidden_meta_boxes = get_user_meta( $user->ID, 'metaboxhidden_nav-menus' );

		if ( $key = array_search( 'add-' . $this->taxonomy_name, $hidden_meta_boxes[0] ) ) {
			unset( $hidden_meta_boxes[0][ $key ] );
		}

		update_user_option( $user->ID, 'metaboxhidden_nav-menus', $hidden_meta_boxes[0], true );
		update_user_option( $user->ID, 'fw-metaboxhidden_nav-menus', 'updated', true );
	}
}
