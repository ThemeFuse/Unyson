<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Blog extends FW_Extension {
	private $post_type = 'post';

	/**
	 * @internal
	 */
	public function _init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, '_admin_action_rename_post_menu' ) );
			add_action( 'init', array( $this, '_admin_action_change_post_labels' ), 9999 );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_style' ) );
		} else {
			add_action( 'init', array( $this, '_theme_action_change_post_labels' ), 999 );
		}
	}

	/**
	 * Changes the labels value od the posts type: post from Post to Blog Post
	 * @internal
	 */
	public function _theme_action_change_post_labels() {
		global $wp_post_types;
		$p = $this->post_type;

		// Someone has changed this post type, always check for that!
		if ( empty ( $wp_post_types[ $p ] )
		     or ! is_object( $wp_post_types[ $p ] )
		     or empty ( $wp_post_types[ $p ]->labels )
		) {
			return;
		}

		$wp_post_types[ $p ]->has_archive = true;

		$wp_post_types[ $p ]->labels->name               = __( 'Blog', 'fw' );
		$wp_post_types[ $p ]->labels->singular_name      = __( 'Blog', 'fw' );
		$wp_post_types[ $p ]->labels->add_new            = __( 'Add blog post', 'fw' );
		$wp_post_types[ $p ]->labels->add_new_item       = __( 'Add new blog post', 'fw' );
		$wp_post_types[ $p ]->labels->all_items          = __( 'All blog posts', 'fw' );
		$wp_post_types[ $p ]->labels->edit_item          = __( 'Edit blog post', 'fw' );
		$wp_post_types[ $p ]->labels->name_admin_bar     = __( 'Blog Post', 'fw' );
		$wp_post_types[ $p ]->labels->menu_name          = __( 'Blog Post', 'fw' );
		$wp_post_types[ $p ]->labels->new_item           = __( 'New blog post', 'fw' );
		$wp_post_types[ $p ]->labels->not_found          = __( 'No blog posts found', 'fw' );
		$wp_post_types[ $p ]->labels->not_found_in_trash = __( 'No blog posts found in trash', 'fw' );
		$wp_post_types[ $p ]->labels->search_items       = __( 'Search blog posts', 'fw' );
		$wp_post_types[ $p ]->labels->view_item          = __( 'View blog post', 'fw' );
	}

	/**
	 * Changes the labels value od the posts type: post from Post to Blog Post
	 * @internal
	 */
	public function _admin_action_change_post_labels() {
		global $wp_post_types, $wp_taxonomies;
		$p = $this->post_type;

		// Someone has changed this post type, always check for that!
		if ( empty ( $wp_post_types[ $p ] )
		     or ! is_object( $wp_post_types[ $p ] )
		     or empty ( $wp_post_types[ $p ]->labels )
		) {
			return;
		}

		$wp_post_types[ $p ]->labels->name = __( 'Blog Posts', 'fw' );

		if ( empty ( $wp_taxonomies['category'] )
		     or ! is_object( $wp_taxonomies['category'] )
		     or empty ( $wp_taxonomies['category']->labels )
		) {
			return;
		}

		$wp_taxonomies['category']->labels->name = __( 'Blog Categories', 'fw' );
	}

	/**
	 * Changes the name in admin menu from Post to Blog Post
	 * @internal
	 */
	public function _admin_action_rename_post_menu() {
		global $menu;

		if ( isset( $menu[5] ) ) {
			$menu[5][0] = __( 'Blog Posts', 'fw' );
		}
	}

	function add_admin_style() {
		$screen = get_current_screen();
		if ( $screen->post_type != 'post' ) {
			return;
		}

		wp_enqueue_style(
			'fw-ext-'. $this->get_name() .'-admin-style',
			$this->get_declared_URI('/static/css/admin-style.css'),
			array(),
			fw()->manifest->get_version()
		);
	}
}