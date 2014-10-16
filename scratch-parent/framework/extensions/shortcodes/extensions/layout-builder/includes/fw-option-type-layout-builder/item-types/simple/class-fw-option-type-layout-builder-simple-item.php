<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Layout_Builder_Simple_Item extends FW_Option_Type_Layout_Builder_Item
{
	private $type = 'simple';
	private $builder_data;

	public function get_type()
	{
		return $this->type;
	}

	public function enqueue_static()
	{
		$static_uri = fw()->extensions->get('layout-builder')->locate_URI('/includes/fw-option-type-layout-builder/item-types/simple/static/');
		$version = fw()->extensions->get('layout-builder')->manifest->get_version();

		wp_enqueue_style(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$static_uri . 'css/styles.css',
			array('fw'),
			$version
		);
		wp_enqueue_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$static_uri . 'js/scripts.js',
			array('fw', 'fw-events', 'underscore'),
			$version,
			true
		);
		wp_localize_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			str_replace('-', '_', $this->get_builder_type()) . '_item_type_' . $this->get_type() . '_data',
			$this->get_builder_data()
		);
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
		$data = $this->get_builder_data();
		$thumb_data = array();
		foreach ($data as $id => $item) {
			$thumb_data[$id] = array(
				'tab'         => $item['tab'],
				'title'       => $item['title'],
				'description' => $item['description'],
				'data'        => array(
					'subtype' => $id
				)
			);

			if (isset($item['image'])) {
				$thumb_data[$id]['image'] = $item['image'];
			}
		}

		$this->sort_thumbnails($thumb_data);
		return $thumb_data;
	}

	private function get_builder_data()
	{
		if (!$this->builder_data) {
			$shortcodes = fw()->extensions->get('shortcodes')->get_shortcodes();
			foreach ($shortcodes as $tag => $shortcode) {
				$config = $shortcode->get_config('layout_builder');
				if ($config) {

					// check if the shortcode type is valid
					$config = array_merge(array('type' => $this->type), $config);
					if ($config['type'] !== $this->get_type()) {
						continue;
					}

					if (!isset($config['tab'])) {
						trigger_error(
							sprintf(__("No Layout Editor tab specified for shortcode: %s", 'fw'), $tag),
							E_USER_WARNING
						);
					}

					$item_data = array_merge(
						array(
							'tab'         => '~',
							'title'       => $tag,
							'description' => '',
						),
						$config
					);

					// search for the thumb image (icon)
					$image_path = $shortcode->get_path() . '/static/img/layout_builder.png';
					if (file_exists($image_path)) {
						$item_data['image'] = $shortcode->get_uri() . '/static/img/layout_builder.png';
					}

					// if the shortcode has options we store them and then they are passed to the modal
					$options = $shortcode->get_options();
					if ($options) {
						$item_data['options'] = $this->transform_options($options);
						fw()->backend->enqueue_options_static($options);
					}

					$this->builder_data[$tag] = $item_data;
				}
			}
		}

		return $this->builder_data;
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

	/*
	 * Sorts the thumbnails by their titles
	 */
	private function sort_thumbnails(&$thumbnails)
	{
		usort($thumbnails, array($this, 'sort_thumbnails_helper'));
		return $thumbnails;
	}

	private function sort_thumbnails_helper($thumbnail1, $thumbnail2)
	{
		return strcasecmp($thumbnail1['title'], $thumbnail2['title']);
	}

	public function get_value_from_attributes($attributes)
	{
		// simple items do not contain other items
		unset($attributes['_items']);

		/*
		 * when saving the modal, the values go into the
		 * 'optionValues' key, if it is not present it could be
		 * because of two things:
		 * either the shortcode does not have options
		 * or the user did not open or save the modal (which will be more likely the case)
		 */
		if (!isset($attributes['optionValues'])) {
			$builder_data = $this->get_builder_data();
			$item         = $builder_data[ $attributes['subtype'] ];
			if (isset($item['options'])) {
				$attributes['optionValues'] = fw_get_options_values_from_input($item['options'], array());
			}
		}

		return $attributes;
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
	 *          'type' => 'primary',
	 *      )
	 * )
	 * @param $registered_items Option_Type_Layout_Builder_Item[] The layout builder items, useful when
	 * the shortcode accepts nested shortcodes
	 * @return string The shortcode notation of the shortcode ex: [button size="large" type="primary"]
	 */
	public function get_shortcode_notation($atts, $registered_items)
	{
		$shortcode = fw()->extensions->get('shortcodes')->get_shortcode($atts['subtype']);
		if ($shortcode) {
			$values = isset($atts['optionValues']) ? $atts['optionValues'] : array();
			$shortcode_notation = $shortcode->generate_shortcode_notation($values);
			return $shortcode_notation;
		} else {
			return '';
		}
	}
}
FW_Option_Type_Builder::register_item_type('FW_Option_Type_Layout_Builder_Simple_Item');
