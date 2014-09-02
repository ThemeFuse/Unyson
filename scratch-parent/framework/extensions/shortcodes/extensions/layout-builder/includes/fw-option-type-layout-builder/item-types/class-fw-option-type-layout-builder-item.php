<?php if (!defined('FW')) die('Forbidden');

abstract class FW_Option_Type_Layout_Builder_Item extends FW_Option_Type_Builder_Item
{
	private $builder_type = 'layout-builder';

	final public function get_builder_type()
	{
		return $this->builder_type;
	}

	// TODO: see if needs to be final
	final public function get_thumbnails()
	{
		$data = $this->get_thumbnails_data();
		$thumbs = array();
		foreach ($data as $item) {
			$item = array_merge(
				array(
					'tab'           => '~',
					'title'         => '',
					'description'   => '',
				),
				$item
			);
			$data_str = '';
			if (!empty($item['data'])) {
				foreach ($item['data'] as $key => $value) {
					$data_str .= "data-$key='$value' ";
				}
				$data_str = substr($data_str, 0, -1);
			}

			$hover_tooltip = $item['description'] ? "data-hover-tip='{$item['description']}'" : '';
			$inner_classes = 'no-image';
			$image_html    = '';
			if (isset($item['image'])) {
				$inner_classes = '';
				$image_html    = "<img src='{$item['image']}' />";
			}
			$thumbs[] = array(
				'tab'  => $item['tab'],
				'html' => "<div class='inner {$inner_classes}' {$hover_tooltip}>" .
								$image_html .
								"<p><span>{$item['title']}</span></p>" .
								"<span class='item-data' {$data_str}></span>" .
							'</div>'
			);
		}
		return $thumbs;
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
	abstract protected function get_thumbnails_data();

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
	abstract public function get_shortcode_notation($atts, $registered_items);
}
