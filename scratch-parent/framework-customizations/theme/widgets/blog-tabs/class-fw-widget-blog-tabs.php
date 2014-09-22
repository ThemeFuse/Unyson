<?php if ( ! defined( 'WP_DEBUG' ) ) {
	die( 'Direct access forbidden.' );
}

class FW_Widget_Blog_Tabs extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => '' );
		parent::WP_Widget( false, __( 'Blog Tabs', 'fw' ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$number = ( (int)( $instance['number'] ) > 0 ) ? esc_attr( $instance['number'] ) : 5;

		$recent_posts = $this->fw_get_posts_with_info( array(
			'sort'  => 'post_date',
			'items' => $number,
		) );

		$popular_posts = $this->fw_get_posts_with_info( array(
			'sort'  => 'comment_count',
			'items' => $number,
		) );

		$this->_theme_action_add_static();

		$filepath = dirname( __FILE__ ) . '/views/widget.php';

		$data = array(
			'before_widget' => str_replace( 'class="', 'class="wrap-tabs ', $before_widget ),
			'after_widget'  => $after_widget,
			'recent_posts'  => $recent_posts,
			'popular_posts' => $popular_posts
		);

		echo fw_render_view( $filepath, $data );
	}

	public function fw_get_posts_with_info( $args = array() ) {
		$defaults = array(
			'sort'        => 'recent',
			'items'       => 5,
			'image_post'  => true,
			'date_post'   => true,
			'date_format' => 'F jS, Y',
			'post_type'   => 'post',
		);

		extract( wp_parse_args( $args, $defaults ) );

		$query        = new WP_Query( array(
			'post_type'      => $post_type,
			'orderby'        => $sort,
			'order '         => 'DESC',
			'posts_per_page' => $items
		) );
		$result_posts = $query->posts;

		$posts_with_info = array();

		foreach ( $result_posts as $post ) {
			$posts_with_info[ $post->ID ]['post_title'] = $post->post_title;
			$posts_with_info[ $post->ID ]['post_link']  = $post->guid;
		}

		wp_reset_query();

		return $posts_with_info;
	}

	/**
	 * @internal
	 */
	public function _theme_action_add_static() {
		wp_enqueue_script(
			'fw-theme-blog-tabs-widget',
			fw_get_template_customizations_directory_uri('/theme/widgets/blog-tabs/static/js/scripts.js'),
			array( 'jquery' ),
			fw()->theme->manifest->get_version()
		);
	}

	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'number' => '', 'title' => '' ) );
		$number   = esc_attr( $instance['number'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of Blog posts', 'fw' ); ?>
				:</label>
			<input type="text" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $number; ?>"
			       class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>"/>
		</p>
	<?php
	}
}
