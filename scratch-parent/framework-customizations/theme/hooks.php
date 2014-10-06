<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );
/**
 * Filters and Actions
 */

/**
 * Enqueue Google fonts style to admin screen for custom header display.
 * @internal
 */
function _action_theme_admin_fonts() {
	wp_enqueue_style( 'fw-theme-lato', fw_theme_font_url(), array(), fw()->theme->manifest->get_version() );
}
add_action( 'admin_print_scripts-appearance_page_custom-header', '_action_theme_admin_fonts' );

if ( ! function_exists( '_action_theme_setup' ) ) :
	/**
	 * Theme setup.
	 *
	 * Set up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support post thumbnails.
	 * @internal
	 */
	function _action_theme_setup() {

		/*
		 * Make Theme available for translation.
		 */
		load_theme_textdomain( 'unyson', get_template_directory() . '/languages' );

		// This theme styles the visual editor to resemble the theme style.
		add_editor_style( array( 'css/editor-style.css', fw_theme_font_url() ) );

		// Add RSS feed links to <head> for posts and comments.
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Thumbnails, and declare two sizes.
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 811, 372, true );
		add_image_size( 'fw-theme-full-width', 1038, 576, true );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
		) );

		/*
		 * Enable support for Post Formats.
		 * See http://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array(
			'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery',
		) );

		// Add support for featured content.
		add_theme_support( 'featured-content', array(
			'featured_content_filter' => 'fw_theme_get_featured_posts',
			'max_posts' => 6,
		) );

		// This theme uses its own gallery styles.
		add_filter( 'use_default_gallery_style', '__return_false' );
	}
endif;
add_action( 'fw_init', '_action_theme_setup' );

/**
 * Adjust content_width value for image attachment template.
 * @internal
 */
function _action_theme_content_width() {
	if ( is_attachment() && wp_attachment_is_image() ) {
		$GLOBALS['content_width'] = 810;
	}
}
add_action( 'template_redirect', '_action_theme_content_width' );

/**
 * Extend the default WordPress body classes.
 *
 * Adds body classes to denote:
 * 1. Single or multiple authors.
 * 2. Presence of header image.
 * 3. Index views.
 * 4. Full-width content layout.
 * 5. Presence of footer widgets.
 * 6. Single views.
 * 7. Featured content layout.
 *
 * @param array $classes A list of existing body class values.
 * @return array The filtered body class list.
 * @internal
 */
function _filter_theme_body_classes( $classes ) {
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	if ( get_header_image() ) {
		$classes[] = 'header-image';
	} else {
		$classes[] = 'masthead-fixed';
	}

	if ( is_archive() || is_search() || is_home() ) {
		$classes[] = 'list-view';
	}

	if ( in_array(fw_ext_sidebars_get_current_position(), array('full', 'left'))
		|| is_page_template( 'page-templates/full-width.php' )
		|| is_page_template( 'page-templates/contributors.php' )
		|| is_attachment() ) {
		$classes[] = 'full-width';
	}

	if (  is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'footer-widgets';
	}

	if ( is_singular() && ! is_front_page() ) {
		$classes[] = 'singular';
	}

	if ( is_front_page() && 'slider' == get_theme_mod( 'featured_content_layout' ) ) {
		$classes[] = 'slider';
	} elseif ( is_front_page() ) {
		$classes[] = 'grid';
	}

	return $classes;
}
add_filter( 'body_class', '_filter_theme_body_classes' );

/**
 * Extend the default WordPress post classes.
 *
 * Adds a post class to denote:
 * Non-password protected page with a post thumbnail.
 *
 * @param array $classes A list of existing post class values.
 * @return array The filtered post class list.
 * @internal
 */
function _filter_theme_post_classes( $classes ) {
	if ( ! post_password_required() && ! is_attachment() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	}

	return $classes;
}
add_filter( 'post_class', '_filter_theme_post_classes' );

/**
 * Create a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 * @internal
 */
function _filter_theme_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() ) {
		return $title;
	}

	// Add the site name.
	$title .= get_bloginfo( 'name', 'display' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 ) {
		$title = "$title $sep " . sprintf( __( 'Page %s', 'unyson' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', '_filter_theme_wp_title', 10, 2 );


/**
 * Flush out the transients used in fw_theme_categorized_blog.
 * @internal
 */
function _action_theme_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'fw_theme_category_count' );
}
add_action( 'edit_category', '_action_theme_category_transient_flusher' );
add_action( 'save_post',     '_action_theme_category_transient_flusher' );

/**
 * Theme Customizer support
 */
{
	/**
	 * Implement Theme Customizer additions and adjustments.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @internal
	 */
	function _action_theme_customize_register( $wp_customize ) {
		// Add custom description to Colors and Background sections.
		$wp_customize->get_section( 'colors' )->description           = __( 'Background may only be visible on wide screens.', 'unyson' );
		$wp_customize->get_section( 'background_image' )->description = __( 'Background may only be visible on wide screens.', 'unyson' );

		// Add postMessage support for site title and description.
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

		// Rename the label to "Site Title Color" because this only affects the site title in this theme.
		$wp_customize->get_control( 'header_textcolor' )->label = __( 'Site Title Color', 'unyson' );

		// Rename the label to "Display Site Title & Tagline" in order to make this option extra clear.
		$wp_customize->get_control( 'display_header_text' )->label = __( 'Display Site Title &amp; Tagline', 'unyson' );

		// Add the featured content section in case it's not already there.
		$wp_customize->add_section( 'featured_content', array(
			'title'       => __( 'Featured Content', 'unyson' ),
			'description' => sprintf( __( 'Use a <a href="%1$s">tag</a> to feature your posts. If no posts match the tag, <a href="%2$s">sticky posts</a> will be displayed instead.', 'unyson' ),
				esc_url( add_query_arg( 'tag', _x( 'featured', 'featured content default tag slug', 'unyson' ), admin_url( 'edit.php' ) ) ),
				admin_url( 'edit.php?show_sticky=1' )
			),
			'priority'    => 130,
		) );

		// Add the featured content layout setting and control.
		$wp_customize->add_setting( 'featured_content_layout', array(
			'default'           => 'grid',
			'sanitize_callback' => '_fw_theme_sanitize_layout',
		) );

		$wp_customize->add_control( 'featured_content_layout', array(
			'label'   => __( 'Layout', 'unyson' ),
			'section' => 'featured_content',
			'type'    => 'select',
			'choices' => array(
				'grid'   => __( 'Grid',   'unyson' ),
				'slider' => __( 'Slider', 'unyson' ),
			),
		) );
	}
	add_action( 'customize_register', '_action_theme_customize_register' );

	/**
	 * Sanitize the Featured Content layout value.
	 *
	 * @param string $layout Layout type.
	 * @return string Filtered layout type (grid|slider).
	 * @internal
	 */
	function _fw_theme_sanitize_layout( $layout ) {
		if ( ! in_array( $layout, array( 'grid', 'slider' ) ) ) {
			$layout = 'grid';
		}

		return $layout;
	}

	/**
	 * Bind JS handlers to make Theme Customizer preview reload changes asynchronously.
	 * @internal
	 */
	function _action_theme_customize_preview_js() {
		wp_enqueue_script(
			'fw-theme-customizer',
			get_template_directory_uri() . '/js/customizer.js',
			array( 'customize-preview' ),
			fw()->theme->manifest->get_version(),
			true
		);
	}
	add_action( 'customize_preview_init', '_action_theme_customize_preview_js' );
}

/**
 * Register widget areas.
 * @internal
 */
function _action_theme_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer Widget Area', 'unyson' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Appears in the footer section of the site.', 'unyson' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', '_action_theme_widgets_init' );

/**
 * Display current submitted FW_Form errors
 * @return array
 */
if (!function_exists('_action_theme_display_form_errors')):
	function _action_theme_display_form_errors() {
		$form = FW_Form::get_submitted();

		if (!$form || $form->is_valid()) {
			return;
		}

		wp_enqueue_script(
			'fw-theme-show-form-errors',
			get_template_directory_uri() . '/js/form-errors.js',
			array('jquery'),
			fw()->theme->manifest->get_version(),
			true
		);

		wp_localize_script('fw-theme-show-form-errors', '_localized_form_errors', array(
			'errors'    => $form->get_errors(),
			'form_attr' => $form->attr()
		));
	}
endif;
add_action('wp_enqueue_scripts', '_action_theme_display_form_errors');

if(!function_exists('_action_theme_add_feedback')) :
	function _action_theme_add_feedback(){
		add_post_type_support('post', 'fw-feedback');
		add_post_type_support('fw-event', 'fw-feedback');
	}
endif;
add_action('init', '_action_theme_add_feedback');

if(!function_exists('_action_theme_add_layout_builder')) :
	function _action_theme_add_layout_builder(){
		add_post_type_support('page', 'fw-layout-builder');
	}
endif;
add_action('init', '_action_theme_add_layout_builder');
