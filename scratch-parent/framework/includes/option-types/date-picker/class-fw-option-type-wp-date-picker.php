<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Date_Picker extends FW_Option_Type {
	private $internal_options = array();

	public function get_type() {
		return 'date-picker';
	}

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
			'value' => '',
			'monday-first' => true, // The week will begin with Monday; for Sunday, set to false
			'min-date' => date('d-m-Y'), // Minimum date will be current day, set a date in format d-m-Y as a start date, or set null for no minimum date
			'max-date' => null, // There will not be set the maximum date by default, set a date in format d-m-Y as a start date
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		$css_uri    = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/datepicker.css');
		$js_uri     = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js');
		$date_picker_js_uri = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/bootstrap-datepicker.js');

		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			$css_uri,
			array(),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			$js_uri,
			array('jquery', 'fw-events'),
			fw()->manifest->get_version(),
			true
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type() . '-date-picker',
			$date_picker_js_uri,
			array('jquery', 'fw-events'),
			fw()->manifest->get_version(),
			true
		);

		$language = substr(get_locale(), 0, 2);

		if ( $language != 'en' ) {
			$locale_uri = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/locales/bootstrap-datepicker.' . $language . '.js');
			wp_enqueue_script(
				'fw-option-' . $this->get_type() . '-date-picker-locale',
				$locale_uri,
				array('fw-option-' . $this->get_type() . '-date-picker'),
				fw()->manifest->get_version(),
				true
			);
		}

		fw()->backend->option_type( 'text' )->enqueue_static();
	}

	/**
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 *
	 * @return string
	 */
	protected function _render( $id, $option, $data ) {
		$language = substr(get_locale(), 0, 2);

		$properties = array(
			'language' => $language,
			'weekStart'  => ( $option['monday-first'] == true ) ? 1 : 0,
			'minDate'  => ( $option['min-date'] !== null ) ? $option['min-date'] : null,
			'maxDate'  => ( $option['max-date'] !== null ) ? $option['max-date'] : null,
		);

		$option['attr']['readonly'] = 'readonly';
		$option['attr']['data-fw-option-date-picker-opts'] = json_encode( $properties );

		return fw()->backend->option_type( 'text' )->render( $id, $option, $data );
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		if (is_null($input_value)) {
			$input_value = $option['value'];
		}

		return (string)$input_value;
	}
}

FW_Option_Type::register('FW_Option_Type_Date_Picker');