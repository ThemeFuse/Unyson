<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Map
 */
class FW_Option_Type_Map extends FW_Option_Type {
	private $language = '';

	/**
	 * @internal
	 */
	public function _init() {
		$this->language = substr( get_locale(), 0, 2 );
	}

	public function get_type() {
		return 'map';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			$this->get_type() . '-styles',
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/style.css')
		);

		wp_enqueue_script(
			'google-maps-api-v3',
			'https://maps.googleapis.com/maps/api/js?v=3.15&sensor=false&libraries=places&language=' . $this->language,
			array(),
			'3.15',
			true
		);
		wp_enqueue_script(
			$this->get_type() . '-styles',
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
			array( 'jquery', 'jquery-ui-widget', 'fw-events', 'underscore', 'jquery-ui-autocomplete' ),
			'1.0',
			true
		);
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$data['value']['coordinates'] = json_encode($data['value']['coordinates']);

		$path = fw_get_framework_directory('/includes/option-types/' . $this->get_type() . '/views/view.php');

		return fw_render_view( $path, array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data
		) );
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		$coordinates = array(
			'lat' => - 34,
			'lng' => 150
		);
		if ( is_array( $input_value ) ) {
			if ( $input_value['coordinates'] ) {
				$coordinates = json_decode( $input_value['coordinates'], true );
			}
		} else {
			$input_value = $option['value'];
		}

		$location = array(
			'location'    => ( isset ( $input_value['location'] ) ) ? $input_value['location'] : '',
			'venue'       => ( isset ( $input_value['venue'] ) ) ? $input_value['venue'] : '',
			'address'     => ( isset ( $input_value['address'] ) ) ? $input_value['address'] : '',
			'city'        => ( isset ( $input_value['city'] ) ) ? $input_value['city'] : '',
			'state'       => ( isset ( $input_value['state'] ) ) ? $input_value['state'] : '',
			'country'     => ( isset ( $input_value['country'] ) ) ? $input_value['country'] : '',
			'zip'         => ( isset ( $input_value['zip'] ) ) ? $input_value['zip'] : '',
			'coordinates' => $coordinates
		);

		return $location;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => array(
				'coordinates' => array(
					'lat'   => -34,
					'lng'   => 150,
				)
			)
		);
	}
}

FW_Option_Type::register( 'FW_Option_Type_Map' );