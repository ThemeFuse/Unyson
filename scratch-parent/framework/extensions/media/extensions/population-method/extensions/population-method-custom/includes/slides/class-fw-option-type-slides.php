<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Slides extends FW_Option_Type
{
	private $extension_name = 'population-method-custom';

	/**
	 * @internal
	 */
	public function _init()
	{
	}

	/**
	 * @internal
	 */
	public static function _action_ajax_resize_slide()
	{
		$thumb_size = json_decode(FW_Request::POST('thumb_size'), true);
		wp_send_json(array('src' => fw_resize(FW_Request::POST('src'), $thumb_size['width'], $thumb_size['height'], true)));
	}

	/**
	 * @internal
	 */
	public static function _action_ajax_cache_slide()
	{
		$output = '';
		$attr_data = json_decode(FW_Request::POST('option'), true);

		$option = $attr_data['option'];
		$id = $attr_data['id'];
		$data = $attr_data['data'];

		parse_str(FW_Request::POST('values'), $values);

		if (isset($values)) {
			$options_values_cache = $values['fw_options']['custom-slides'];
			$options_values = array_pop($options_values_cache);
			$valid_values = fw_get_options_values_from_input(
				$option['slides_options'],
				$options_values
			);

			foreach ($values['fw_options']['custom-slides'] as $key => $value) {
				$output .= "<div class='fw-slide slide-" . $key . "'  data-order='" . $key . "'>";

				$output .= fw()->backend->render_options($option['slides_options'], $valid_values, array(
					'id_prefix' => $data['id_prefix'] . $id . '-' . $key . '-',
					'name_prefix' => $data['name_prefix'] . '[' . $id . '][' . $key . ']'
				));
				$output .= "</div>";
			}
		}

		wp_send_json($output);
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(),
			'thumb_size' => array(
				'width' => 150,
				'height' => 150
			),
			'slides_options' => array()
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		$js_path = fw()->extensions->get($this->extension_name)->get_declared_URI('/includes/slides/static/js/slides.js');
		$css_path = fw()->extensions->get($this->extension_name)->get_declared_URI('/includes/slides/static/css/slides.css');

		wp_enqueue_script(
			'fw-option-'. $this->get_type() .'-slides-js',
			$js_path,
			array('jquery-ui-sortable','qtip', 'fw'),
			fw()->manifest->get_version()
		);
		wp_enqueue_style(
			'fw-option-'. $this->get_type() .'-slides-css',
			$css_path,
			array('qtip'),
			fw()->manifest->get_version()
		);
	}

	/**
	 * Generate option's html from option array
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 * @return string HTML
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$template_path = fw()->extensions->get($this->extension_name)->get_declared_path('/includes/slides/views/templates.php');

		$values = $data['value'];
		$thumb_size = $option['thumb_size'];
		$slides_options = $option['slides_options'];
		$multimedia_type = (array) $option['multimedia_type'];

		$type = $this->get_type();
		$template = fw_render_view($template_path, compact('id', 'option', 'thumb_size', 'data', 'values', 'type', 'slides_options'));

		wp_localize_script(
			'fw-option-'. $this->get_type() .'-slides-js',
			'slides_templates',
			$template
		);

		$path = fw()->extensions->get($this->extension_name)->get_declared_path('/includes/slides/views/slides.php');

		return fw_render_view($path, compact('id', 'option', 'thumb_size', 'data', 'values', 'type', 'slides_options', 'multimedia_type'));
	}

	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	public function get_type()
	{
		return 'slides';
	}

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 * @param array $option
	 * @param array|string|null $input_value
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		// unset the last slide that is default for add
		array_pop($input_value);

		$value = array();

		$slides_options = fw_extract_only_options($option['slides_options']);

		foreach ($input_value as &$list_item_value) {
			$current_value = array();

			foreach ($slides_options as $id => $input_option) {
				$current_value[$id] = fw()->backend->option_type($input_option['type'])->get_value_from_input(
					$input_option,
					isset($list_item_value[$id]) ? $list_item_value[$id] : null
				);
				$current_value['thumb'] = isset($list_item_value['thumb']) ? $list_item_value['thumb'] : null;
			}

			$value[] = $current_value;
		}
		return $value;
	}
}

FW_Option_Type::register('FW_Option_Type_Slides');

add_action('wp_ajax_cache_slide', array('FW_Option_Type_Slides', '_action_ajax_cache_slide'));
add_action('wp_ajax_resize_slide', array('FW_Option_Type_Slides', '_action_ajax_resize_slide'));