<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_SEO_Tags extends FW_Option_Type {
	private $internal_options = array();

	/**
	 * @internal
	 */
	public function _init() {
		$this->internal_options = array(
			'label' => false,
			'type'  => 'text',
			'value' => ''
		);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'fixed';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => ''
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		$css_uri    = fw()->extensions->get( 'seo' )->get_declared_URI( '/includes/option-types/' . $this->get_type() . '/static/css/style.css' );
		$js_uri     = fw()->extensions->get( 'seo' )->get_declared_URI( '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js' );
		$seo_tags   = fw()->extensions->get( 'seo' )->get_seo_tags();
		$tags_names = array();

		foreach ( $seo_tags as $tag_id => $tag ) {
			array_push( $tags_names, '%%' . $tag_id . '%%' );
		}

		$version = fw()->manifest->get_version();

		wp_enqueue_style( 'fw-option-' . $this->get_type(), $css_uri, array(), $version );
		wp_enqueue_script( 'fw-option-' . $this->get_type(), $js_uri, array('jquery', 'jquery-ui-autocomplete'), $version, true );
		wp_localize_script( 'fw-option-' . $this->get_type(), 'fw_ext_seo_tags', $tags_names );

		fw()->backend->option_type( 'text' )->enqueue_static();
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		return fw()->backend->option_type( 'text' )->render( $id, $option, $data );
	}

	public function get_type() {
		return 'seo-tags';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		return fw()->backend->option_type( 'text' )->get_value_from_input( $this->internal_options, $input_value );
	}
}

FW_Option_Type::register('FW_Option_Type_SEO_Tags');