<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );


class FW_Option_Type_Datetime_Picker extends FW_Option_Type {

	public function get_type() {
		return 'datetime-picker';
	}

	public function _get_backend_width_type() {
		return 'fixed';
	}

	protected function _get_data_for_js($id, $option, $data = array()) {
		return false;
	}

	/**
	 * Detetime-picker options on http://xdsoft.net/jqplugins/datetimepicker/ excepts: [value]
	 * Additional options:
	 *      moment-format - (string) the format must be compatible with moment.js
	 *      extra-formats - (array) additional formats, which make possible to validate on save
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'attr' => array(),
			'value'  => '',
			'datetime-picker' => array(
				'format'        => 'Y/m/d H:i',
				'extra-formats' => array(),
				// it is used in event option type.
				'moment-format' => 'YYYY/MM/DD HH:mm',
				'maxDate'       => false,
				'minDate'       => false,
				'timepicker'    => true,
				'datepicker'    => true,
				'defaultTime'   => '12:00'
			),
		);
	}

	protected function _render( $id, $option, $data ) {
		$wrapper_attr = $option['attr'];

		$defaults                  = $this->_get_defaults();
		$default_datetime_picker   = $defaults['datetime-picker'];
		$option['datetime-picker'] = array_merge( $default_datetime_picker, $option['datetime-picker'] );

		$moment_format = $option['datetime-picker']['moment-format'];

		$wrapper_attr['data-min-date'] = fw_akg('datetime-picker/minDate', $option, false );
		$wrapper_attr['data-max-date'] = fw_akg('datetime-picker/maxDate', $option, false );
		$wrapper_attr['data-extra-formats'] = isset($option['datetime-picker']['extra-formats']) ? json_encode($option['datetime-picker']['extra-formats']) : '';
		$wrapper_attr['data-datetime-attr'] = json_encode($option['datetime-picker']);

		unset($option['datetime-picker']['moment-format'], $option['datetime-picker']['extra-formats'], $option['attr']['class'], $wrapper_attr['name'], $wrapper_attr['id'], $wrapper_attr['value'] );

		if (isset($option['datetime-picker']['value'])) {
			unset($option['datetime-picker']['value']);
		}

		$option['datetime-picker']['scrollInput'] = false;
		$option['datetime-picker']['lang'] = substr(get_locale(), 0, 2);
		$option['attr']['data-moment-format'] = $moment_format;

		$html = '<div ' . fw_attr_to_html($wrapper_attr) .' >';
		$html .= fw()->backend->option_type( 'text' )->render( $id, $option, $data );
		$html .= '</div>';

		return $html;
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {
		//plugin styles & js
		{
			$css_lib_uri        = fw_get_framework_directory_uri('/includes/option-types/datetime-picker/static/css/jquery.datetimepicker.css');
			$js_lib_uri         = fw_get_framework_directory_uri('/includes/option-types/datetime-picker/static/js/jquery.datetimepicker.js');
		}

		//framework styles & js
		{
			$css_main_uri    = fw_get_framework_directory_uri('/includes/option-types/datetime-picker/static/css/style.css');
			$js_main_uri     = fw_get_framework_directory_uri('/includes/option-types/datetime-picker/static/js/script.js');
		}

		wp_enqueue_style( 'fw-option-datetime-picker-lib-css', $css_lib_uri );
		wp_enqueue_style( 'fw-option-datetime-picker-main-css', $css_main_uri );
		wp_enqueue_script( 'fw-moment' );
		wp_enqueue_script( 'fw-option-datetime-picker-lib-js', $js_lib_uri, array('jquery', 'fw-moment'), false, true );
		wp_enqueue_script( 'fw-option-datetime-picker-main-js', $js_main_uri, array('jquery', 'fw-option-datetime-picker-lib-js', 'fw-events' ), false, true );

		fw()->backend->option_type( 'text' )->enqueue_static();
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_null($input_value)) {
			return $option['value'];
		}

		$extra_formats = (isset($option['datetime-picker']['extra-formats']) and is_array($option['datetime-picker']['extra-formats'])) ? $option['datetime-picker']['extra-formats'] : array();

		//check format
		if ( isset($option['datetime-picker']['format']) && !empty($option['datetime-picker']['format']) ){
			if ($this->_fw_validate_date_format($input_value, $option['datetime-picker']['format'], $extra_formats ) === false) {
				return $option['value'];
			}
		}

		//check minDate/maxDate range
		try {
			$input_timestamp_value = strtotime($input_value);

			if (isset($option['datetime-picker']['minDate']) and !empty($option['datetime-picker']['minDate']) ) {
				$min_timestamp_value = strtotime($option['datetime-picker']['minDate']);

				if ($min_timestamp_value > $input_timestamp_value) {
					return $option['value'];
				}
			}

			if (isset($option['datetime-picker']['maxDate']) and !empty($option['datetime-picker']['maxDate']) ) {
				$max_timestamp_value = strtotime($option['datetime-picker']['maxDate']);

				if ($max_timestamp_value < $input_timestamp_value) {
					return $option['value'];
				}
			}

		} catch (Exception $e) {
			return $option['value'];
		}

		return (string)$input_value;
	}


	private function _fw_validate_date_format($date, $format = 'Y/m/d H:i', $extra_formats = array())
	{
		$extra_formats = array_merge($extra_formats, array($format));

		foreach($extra_formats as $format) {
			$d = DateTime::createFromFormat($format, $date);
			if ($d && $d->format($format) == $date) {
				return true;
			}
		}

		return false;
	}

}
