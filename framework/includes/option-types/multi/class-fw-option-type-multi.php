<?php if (!defined('FW')) die('Forbidden');

/**
 * Rows with options
 */
class FW_Option_Type_Multi extends FW_Option_Type
{
	public function get_type()
	{
		return 'multi';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		static $enqueue = true;

		if ($enqueue) {
			wp_enqueue_style(
				'fw-option-'. $this->get_type(),
				fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/styles.css'),
				array(),
				fw()->manifest->get_version()
			);

			$enqueue = false;
		}

		fw()->backend->enqueue_options_static($option['inner-options']);

		return true;
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		if (empty($data['value'])) {
			$data['value'] = array();
		}

		$div_attr = $option['attr'];
		unset($div_attr['name'], $div_attr['value']);

		return '<div '. fw_attr_to_html($div_attr) .'>'.
			fw()->backend->render_options($option['inner-options'], $data['value'], array(
				'id_prefix'   => $data['id_prefix'] . $id .'-',
				'name_prefix' => $data['name_prefix'] .'['. $id .']',
			)).
		'</div>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if ( is_array($input_value) || empty($option['value']) ) {
			$value = array();
		} else {
			$value = $option['value'];
		}

		foreach (fw_extract_only_options($option['inner-options']) as $inner_id => $inner_option) {
			$value[$inner_id] = fw()->backend->option_type($inner_option['type'])->get_value_from_input(
				isset($value[$inner_id])
					? array_merge($inner_option, array('value' => $value[$inner_id]))
					: $inner_option,
				isset($input_value[$inner_id]) ? $input_value[$inner_id] : null
			);
		}

		return $value;
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'full';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'inner-options' => array(),
			'value' => array(),
		);
	}
}