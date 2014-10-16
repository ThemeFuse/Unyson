<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Layout_Builder_Column_Item extends FW_Option_Type_Layout_Builder_Item
{
	private $restricted_types = array('column');

	public function get_type()
	{
		return 'column';
	}

	public function enqueue_static()
	{
		$column_shortcode = fw()->extensions->get('shortcodes')->get_shortcode('column');
		$static_uri = $column_shortcode->get_uri() . '/includes/fw-option-type-layout-builder-column-item/static/';
		wp_enqueue_style(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$static_uri . 'css/styles.css',
			array(),
			fw()->theme->manifest->get_version()
		);
		wp_enqueue_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$static_uri . 'js/scripts.js',
			array('fw-events', 'underscore'),
			fw()->theme->manifest->get_version(),
			true
		);

		wp_localize_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			str_replace('-', '_', $this->get_builder_type()) . '_item_type_' . $this->get_type() . '_data',
			array(
				'restrictedTypes' => $this->restricted_types,
			)
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
		$column_shortcode = fw()->extensions->get('shortcodes')->get_shortcode('column');
		$img_uri          = $column_shortcode->get_uri() . '/includes/fw-option-type-layout-builder-column-item/static/img/';
		return array(
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('1/5', 'fw'),
				'description' => __('Creates a 1/5 column' ,'fw'),
				'image'       => $img_uri . '1-5.png',
				'data'        => array(
					'subtype' => '1-5'
				)
			),
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('1/4', 'fw'),
				'description' => __('Creates a 1/4 column' ,'fw'),
				'image'       => $img_uri . '1-4.png',
				'data'        => array(
					'subtype' => '1-4'
				)
			),
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('1/3', 'fw'),
				'description' => __('Creates a 1/3 column' ,'fw'),
				'image'       => $img_uri . '1-3.png',
				'data'        => array(
					'subtype' => '1-3'
				)
			),
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('1/2', 'fw'),
				'description' => __('Creates a 1/2 column' ,'fw'),
				'image'       => $img_uri . '1-2.png',
				'data'        => array(
					'subtype' => '1-2'
				)
			),
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('2/3', 'fw'),
				'description' => __('Creates a 2/3 column' ,'fw'),
				'image'       => $img_uri . '2-3.png',
				'data'        => array(
					'subtype' => '2-3'
				)
			),
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('3/4', 'fw'),
				'description' => __('Creates a 3/4 column' ,'fw'),
				'image'       => $img_uri . '3-4.png',
				'data'        => array(
					'subtype' => '3-4'
				)
			),
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('1/1', 'fw'),
				'description' => __('Creates a 1/1 column' ,'fw'),
				'image'       => $img_uri . '1-1.png',
				'data'        => array(
					'subtype' => '1-1'
				)
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
		$attributes = 'type="' . base64_encode($atts['subtype']) . '"';
		if (isset($atts['firstInRow']) && $atts['firstInRow']) {
			$attributes .= ' first_in_row="' . base64_encode('true') . '"';
		}

		$content    = '';
		if (!empty($atts['_items'])) {
			foreach ($atts['_items'] as $item) {
				if (!in_array($item['type'], $this->restricted_types)) {
					$content .= $registered_items[$item['type']]->get_shortcode_notation($item, $registered_items);
				}
			}
		}
		return "[column {$attributes}]{$content}[/column]";
	}
}
FW_Option_Type_Builder::register_item_type('FW_Option_Type_Layout_Builder_Column_Item');
