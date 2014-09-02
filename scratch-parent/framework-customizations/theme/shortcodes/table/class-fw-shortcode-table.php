<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Shortcode_Table extends FW_Shortcode {
	/**
	 * @internal
	 */
	public function _init() {
		if ( is_admin() ) {
			$this->load_option_type();
		}
	}

	private function load_option_type() {
		require $this->get_path() . '/includes/fw-option-type-table-builder/class-fw-option-type-table-builder.php';
	}

	protected function handle_shortcode( $atts, $content, $tag ) {

		$view_file = $this->get_path() . '/views/' . $atts['table_purpose'] . '.php';

		if ( ! file_exists( $view_file ) ) {
			trigger_error(
				sprintf( __( 'No default view (views/view.php) found for shortcode: %s', 'fw' ), $tag ),
				E_USER_ERROR
			);
		}

		return fw_render_view( $view_file, array(
			'atts'    => $atts,
			'content' => $content,
			'tag'     => $tag
		) );
	}


}