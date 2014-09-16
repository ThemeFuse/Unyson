<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Shortcode_Accordion extends FW_Shortcode {

	protected function handle_shortcode( $atts, $content, $tag ) {
		wp_enqueue_script(
			'shortcode-accordion',
			$this->get_uri() . '/static/js/scripts.js',
			array( 'jquery-ui-accordion' ),
			fw()->theme->manifest->get_version(),
			true
		);

		return fw_render_view( $this->get_path() . '/views/view.php', array(
			'atts'    => $atts,
			'content' => $content,
			'tag'     => $tag
		) );
	}
}