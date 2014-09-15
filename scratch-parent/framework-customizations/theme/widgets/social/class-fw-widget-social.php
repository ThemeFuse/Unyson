<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Widget_Social extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'description' => __( 'Social links', 'fw' ) );

		parent::WP_Widget( false, __( 'Social', 'fw' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$params = array();

		foreach ( $instance as $key => $value ) {
			$params[ $key ] = $value;
		}

		$title = $before_title . $params['widget-title'] . $after_title;
		unset($params['widget-title']);

		$filepath = dirname( __FILE__ ) . '/views/widget.php';

		$data = array(
			'instance' => $params,
			'title' => $title,
			'before_widget' => str_replace( 'class="', 'class="widget_social_links ', $before_widget ),
			'after_widget'  => $after_widget,
		);

		echo fw_render_view( $filepath, $data );
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( (array) $new_instance, $old_instance );

		return $instance;
	}

	function form( $instance ) {

		$titles = array(
			'widget-title' => __( 'Social Title:', 'fw' ),
			'google'       => __( 'Google URL:', 'fw' ),
			'facebook'     => __( 'Facebook URL:', 'fw' ),
			'twitter'      => __( 'Twitter URL:', 'fw' ),
			'dribbble'     => __( 'Dribbble URL:', 'fw' ),
			'vimeo-square' => __( 'Vimeo-square URL:', 'fw' ),
			'linkedin'     => __( 'Linkedin URL:', 'fw' ),
			'instagram'    => __( 'Instagram URL:', 'fw' )
		);

		$instance = wp_parse_args( (array) $instance, $titles );

		foreach ( $instance as $key => $value ) {
			?>
			<p>
				<label><?php echo $titles[ $key ] ?></label>
				<input class="widefat widget_social_link widget_link_field"
				       name="<?php echo $this->get_field_name( $key ) ?>" type="text"
				       value="<?php echo ( $instance[ $key ] === $titles[ $key ] ) ? '' : $instance[ $key ]; ?>"/>
			</p>
		<?php
		}
	}
}
