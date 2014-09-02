<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Shortcode_Testimonials extends FW_Shortcode {
	/**
	 * @internal
	 */
	public function _init() {
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_static' ) );
		}
	}


	/**
	 * @internal
	 */
	public function add_static() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script(
			'carouFredSel',
			$this->get_uri() . '/static/js/jquery.carouFredSel-6.2.1-packed.js',
			array( 'jquery' ),
			fw()->theme->manifest->get_version(),
			true
		);
	}
}