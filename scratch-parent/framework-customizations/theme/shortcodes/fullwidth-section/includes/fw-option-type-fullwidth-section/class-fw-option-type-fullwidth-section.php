<?php

class FW_Option_Type_Fullwidth_Section extends FW_Option_Type_Layout_Builder_Item
{
	private $shortcode_instance;

	public function get_type()
	{
		return 'fullwidth-section';
	}

	/**
	 * Called when builder is rendered
	 */
	public function enqueue_static()
	{
		$this->shortcode_instance = fw()->extensions->get('shortcodes')->get_shortcode('fullwidth_section');
		$static_uri = $this->shortcode_instance->get_uri() . '/includes/fw-option-type-fullwidth-section/static/';
		wp_enqueue_style(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$static_uri . 'css/fullwidth-section.css',
			array(),
			fw()->theme->manifest->get_version()
		);
		wp_enqueue_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$static_uri . 'js/fullwidth-section.js',
			array('fw-events', 'underscore'),
			fw()->theme->manifest->get_version(),
			true
		);

		wp_localize_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			str_replace('-', '_', $this->get_builder_type() . '_item_type_' . $this->get_type() . '_data'),
			$this->get_options()
		);
	}

	private function get_options()
	{
		$options = $this->shortcode_instance->get_options();
		$collector = array();
		if ($options) {
			$collector['options'] = $this->transform_options($options);
			fw()->backend->enqueue_options_static($options);
		}

		return $collector;
	}

	/*
	 * Puts each option into a separate array
	 * to keep it's order inside the modal dialog
	 */
	private function transform_options($options)
	{
		$new_options = array();
		foreach ($options as $id => $option) {
			$new_options[] = array($id => $option);
		}
		return $new_options;
	}

	/**
	 * @return array(
	 *  array(
	 *      'tab'   => __('Tab 1', 'fw'),
	 *      'title' => __('thumb title 1', 'fw'),
	 *      'data'  => array( // optional
	 *          'key1'  => 'value1',
	 *          'key2'  => 'value2'
	 *      )
	 *  ),
	 *  array(
	 *      'tab'   => __('Tab 2', 'fw'),
	 *      'title' => __('thumb title 2', 'fw'),
	 *      'data'  => array( // optional
	 *          'key1'  => 'value1',
	 *      )
	 *  ),
	 *  ...
	 * )
	 */
	protected function get_thumbnails_data()
	{
		// TODO: Implement get_thumbnails_data() method.
		return array(
			array(
				'tab' => __('Layout Elements', 'fw'),
				'title' => __('Custom Section', 'fw'),
				'description' => __('Creates a custom section', 'fw'),
			)
		);
	}


	/**
	 * Transforms the array representation of the shortcode to shortcode notation
	 *
	 * @param $atts array The array representation of the shortcode ex:
	 * array(
	 *      'type'      => 'simple',
	 *      'subtype'   => 'button',
	 *      'optionValues'  => array(
	 *          'size' => 'large',
	 *          'type' => 'primary'
	 *      )
	 * )
	 * @param $registered_items Option_Type_Layout_Builder_Item[] The layout builder items, useful when
	 * the shortcode accepts nested shortcodes
	 * @return string The shortcode notation of the shortcode ex: [button size="large" type="primary"]
	 */
	public function get_shortcode_notation($atts, $registered_items)
	{
		$attributes = '';

		if (!empty($atts['optionValues'])) {
			$attributes .= 'option_values="' . base64_encode(json_encode($atts['optionValues'])) . '"';
			$attributes .= ' _json_keys="' . base64_encode(json_encode(array('option_values'))) . '"';
		}

		$content = '';
		if (!empty($atts['_items'])) {
			foreach ($atts['_items'] as $item) {
				if (!in_array($item['type'], (array)$this->get_type())) {
					$content .= $registered_items[$item['type']]->get_shortcode_notation($item, $registered_items);
				}
			}
		}

		return '[fullwidth_section ' . $attributes . ']' . $content . '[/fullwidth_section]';
	}
}

FW_Option_Type_Builder::register_item_type('FW_Option_Type_Fullwidth_Section');
