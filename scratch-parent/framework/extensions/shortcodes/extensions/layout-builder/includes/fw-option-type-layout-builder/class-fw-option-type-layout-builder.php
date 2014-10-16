<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Layout_Builder extends FW_Option_Type_Builder
{
	private $editor_integration_enabled = false;

	public function get_type()
	{
		return 'layout-builder';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'editor_integration' => false,
			'value'              => array(
				'json' => '[]'
			)
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _render($id, $option, $data)
	{
		$static_uri = fw()->extensions->get('layout-builder')->locate_URI('/includes/fw-option-type-layout-builder/static/');
		$version = fw()->extensions->get('layout-builder')->manifest->get_version();

		wp_enqueue_style(
			'fw-option-type-' . $this->get_type(),
			$static_uri . 'css/styles.css',
			array(),
			$version
		);

		/*
		 * there should not be (and it does not make sens to be)
		 * more than one layout builder per page that is integrated
		 * with the default post content editor
		 * integration in the sens of inserting the button to activate/deactivate
		 * the builder, to replace the post content with the shortcode notation
		 */
		if ($this->editor_integration_enabled && $option['editor_integration'] === true) {
			trigger_error(
				__('There must not be more than one Layout Editor integrated with the wp post editor per page', 'fw'),
				E_USER_ERROR
			);
		} elseif ($option['editor_integration'] === true) {
			$this->editor_integration_enabled = true;
			wp_enqueue_style(
				'fw-option-type-' . $this->get_type() . '-editor-integration',
				$static_uri . 'css/editor_integration.css',
				array(),
				$version
			);
			wp_enqueue_script(
				'fw-option-type-' . $this->get_type() . '-editor-integration',
				$static_uri . 'js/editor_integration.js',
				array('jquery', 'fw-events'),
				$version,
				true
			);
			wp_localize_script(
				'fw-option-type-' . $this->get_type() . '-editor-integration',
				'fw_option_type_' . str_replace('-', '_', $this->get_type()) . '_editor_integration_data',
				array(
					'l10n'                => array(
						'showButton' => __('Visual Layout Editor', 'fw'),
						'hideButton' => __('Default Editor', 'fw'),
					),
					'optionId'            => $option['attr']['id'],
					'renderInBuilderMode' => isset($data['value']['builder_active']) ? $data['value']['builder_active'] : false
				)
			);
		}

		return parent::_render($id, $option, $data);
	}

	protected function item_type_is_valid($item_type_instance)
	{
		return is_subclass_of($item_type_instance, 'FW_Option_Type_Layout_Builder_Item');
	}

	/*
	 * Sorts the tabs so that the layout tab comes first
	 */
	protected function sort_thumbnails(&$thumbnails)
	{
		uksort($thumbnails, array($this, 'sort_thumbnails_helper'));
	}

	private function sort_thumbnails_helper($tab1, $tab2)
	{
		$layout_tab = __('Layout Elements', 'fw');
		if ($tab1 === $layout_tab) {
			return -1;
		} elseif ($tab2 === $layout_tab) {
			return 1;
		}

		return strcasecmp($tab1, $tab2);
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		$value = parent::_get_value_from_input($option, $input_value);
		$value['shortcode_notation'] = $this->get_shortcode_notation(json_decode($value['json'], true));
		if($option['editor_integration'] === true) {
			$value['builder_active'] = isset($_POST['layout-builder-active']) && $_POST['layout-builder-active'] === 'true';
		}

		return $value;
	}

	private function get_shortcode_notation($items)
	{
		/**
		 * @var Option_Type_Layout_Builder_Item[] $registered_items
		 */
		$registered_items = $this->get_item_types();
		$shortcode_notation = '';
		foreach ($items as $item_attributes) {
			if ( isset( $registered_items[ $item_attributes['type'] ] ) ) {
				$shortcode_notation .= $registered_items[ $item_attributes['type'] ]->get_shortcode_notation($item_attributes, $registered_items);
			}
		}
		return $shortcode_notation;
	}
}
FW_Option_Type::register('FW_Option_Type_Layout_Builder');

{
	$path = dirname(__FILE__);
	require $path . '/item-types/class-fw-option-type-layout-builder-item.php';
	require $path . '/item-types/simple/class-fw-option-type-layout-builder-simple-item.php';
}
