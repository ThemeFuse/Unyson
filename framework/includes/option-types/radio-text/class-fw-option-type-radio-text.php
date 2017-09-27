<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Radio_Text extends FW_Option_Type
{
	private $js_uri;
	private $css_uri;
	private $custom_choice_key = 'Ku$+03';

	public function get_type()
	{
		return 'radio-text';
	}

	public function _get_data_for_js($id, $option, $data = array()) {
		return false;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => '',
			'choices' => array(
				'25' => __('25%', 'fw'),
				'50' => __('50%', 'fw'),
				'100' => __('100%', 'fw'),
			),
		);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _init()
	{
		$static_uri         = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static');
		$this->js_uri       = $static_uri . '/js';
		$this->css_uri      = $static_uri . '/css';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			$this->css_uri .'/styles.css',
			array(),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			$this->js_uri .'/scripts.js',
			array( 'jquery', 'fw-events' ),
			fw()->manifest->get_version(),
			true
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['choices'][ $this->custom_choice_key ] = '';

		return fw_render_view( dirname(__FILE__) .'/view.php', array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
			'custom_choice_key' => $this->custom_choice_key,
		) );
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_null($input_value)) {
			return $option['value'];
		}

		$option['choices'][ $this->custom_choice_key ] = '';


		/*
		 * Sometimes $input_value comes as a string because when you serialize
		 * the form with js, it gives you the value as a string
		 * and we need to treat this case accordingly.
		 */
		if (is_string($input_value)) {
			$tmp_array = array(
				'predefined' => $input_value,
				'custom' => $input_value
			);

			$input_value = $tmp_array;
		}


		$selected = fw()->backend->option_type( 'radio' )->get_value_from_input( array(
				'value' => $option['value'],
				'choices' => $option['choices']
			),
			$input_value['predefined']
		);

		if ( $selected === $this->custom_choice_key ) {
			return (string)$input_value['custom'];
		} else {
			return $selected;
		}
	}
}
