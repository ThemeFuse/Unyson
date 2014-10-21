<?php if (!defined('FW')) die('Forbidden');

abstract class FW_Option_Type_Builder extends FW_Option_Type
{
	/**
	 * Store item types for registration of all builder types, until they will be required
	 * @var array|false
	 *      array Can have some pending item types in it
	 *      false Item types already requested and was registered, so do not use pending anymore
	 */
	private static $item_types_pending_registration = array();

	/**
	 * Registered item types of the current builder type
	 * @var null|array {item-type => item-instance}
	 */
	private $item_types = array();

	/**
	 * @param string|FW_Option_Type_Builder_Item $item_type_class
	 */
	final public static function register_item_type($item_type_class)
	{
		if (is_array(self::$item_types_pending_registration)) {
			// Item types never requested. Continue adding to pending
			self::$item_types_pending_registration[] = $item_type_class;
		} else {
			self::$item_types_pending_registration = array($item_type_class);

			self::register_pending_item_types();
		}
	}

	private static function register_pending_item_types()
	{
		if (!is_array(self::$item_types_pending_registration)) {
			// all pending item types already registered
			return;
		}

		foreach (self::$item_types_pending_registration as $item_type_class) {
			if (is_string($item_type_class)) {
				$item_type_instance = new $item_type_class;
			} else {
				$item_type_instance = $item_type_class;
			}

			unset($item_type_class);

			if (!is_subclass_of($item_type_instance, 'FW_Option_Type_Builder_Item')) {
				trigger_error('Invalid builder item type class '. get_class($item_type_instance), E_USER_WARNING);
				continue;
			}

			$builder_type = $item_type_instance->get_builder_type();

			/**
			 * @var FW_Option_Type_Builder $builder_type_instance
			 */
			$builder_type_instance = fw()->backend->option_type($builder_type);

			if (!$builder_type_instance->item_type_is_valid($item_type_instance)) {
				trigger_error('Invalid builder item. (type: '. $item_type_instance->get_type() .')', E_USER_WARNING);
				continue;
			}

			$builder_type_instance->_register_item_type($item_type_instance);
		}

		self::$item_types_pending_registration = false;
	}

	/**
	 * @param FW_Option_Type_Builder_Item $item_type_instance
	 */
	private function _register_item_type($item_type_instance)
	{
		if (isset($this->item_types[$item_type_instance->get_type()])) {
			trigger_error('Builder item already registered (type: '. $item_type_instance->get_type() .')', E_USER_ERROR);
			return;
		}

		$this->item_types[$item_type_instance->get_type()] = $item_type_instance;
	}

	/**
	 * @return FW_Option_Type_Builder_Item[]
	 */
	final protected function get_item_types()
	{
		if (is_array(self::$item_types_pending_registration)) {
			/**
			 * Item types requested first time.
			 * Register pending item types.
			 */
			self::register_pending_item_types();
		}

		return $this->item_types;
	}

	/**
	 * Overwrite this method to force your builder type items to extend custom class or to have custom requirements
	 * @param FW_Option_Type_Builder_Item $item_type_instance
	 * @return bool
	 */
	protected function item_type_is_valid($item_type_instance)
	{
		return is_subclass_of($item_type_instance, 'FW_Option_Type_Builder_Item');
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(
				'json' => '[]'
			)
		);
	}

	private function get_static_uri($append = '')
	{
		return fw()->extensions->get('builder')->get_declared_URI('/includes/option-types/builder/static'. $append);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		{
			wp_enqueue_style(
				'fw-option-builder',
				$this->get_static_uri('/css/builder.css'),
				array('fw'),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-builder',
				$this->get_static_uri('/js/builder.js'),
				array(
					'jquery-ui-draggable',
					'jquery-ui-sortable',
					'fw',
					'fw-events',
					'backbone',
					'backbone-relational'
				),
				fw()->manifest->get_version(),
				true
			);
		}

		wp_enqueue_media();

		{
			wp_enqueue_style(
				'fw-option-builder-helpers',
				$this->get_static_uri('/css/helpers.css'),
				array('fw-option-builder'),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-builder-helpers',
				$this->get_static_uri('/js/helpers.js'),
				array('fw-option-builder',),
				fw()->manifest->get_version(),
				true
			);

			wp_localize_script(
				'fw-option-builder-helpers',
				'_fw_option_type_builder_helpers',
				array(
					'l10n' => array(
						'save' => __('Save', 'fw'),
					),
					'item_widths' => fw_ext_builder_get_item_widths_for_js($this->get_type())
				)
			);
		}

		if (!(isset($option['fullscreen']) and $option['fullscreen'] === false)) {
			wp_enqueue_style(
				'fw-option-builder-fullscreen',
				$this->get_static_uri('/css/fullscreen.css'),
				array('fw-option-builder'),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-builder-fullscreen',
				$this->get_static_uri('/js/fullscreen.js'),
				array('fw-option-builder',),
				fw()->manifest->get_version(),
				true
			);
		}

		if (!(isset($option['template_saving']) and $option['template_saving'] === false)) {
			wp_enqueue_style(
				'fw-option-builder-template-saving',
				$this->get_static_uri('/css/template-saving.css'),
				array('fw-option-builder'),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-builder-template-saving',
				$this->get_static_uri('/js/template-saving.js'),
				array('fw-option-builder'),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_enqueue_style(
				'fw-option-builder-history',
				$this->get_static_uri('/css/history.css'),
				array('fw-option-builder'),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-builder-history',
				$this->get_static_uri('/js/history.js'),
				array('fw-option-builder',),
				fw()->manifest->get_version(),
				true
			);
		}
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _render($id, $option, $data)
	{
		/**
		 * array(
		 *  'Tab title' => array(
		 *      '<thumbnail html>',
		 *      '<thumbnail html>',
		 *      '<thumbnail html>',
		 *  ),
		 *  'Tab title' => array(
		 *      '<thumbnail html>',
		 *  )
		 * )
		 */
		$thumbnails = array();

		foreach ($this->get_item_types() as $item) {
			/** @var FW_Option_Type_Builder_Item $item */

			$item->enqueue_static();

			foreach ($item->get_thumbnails() as $thumbnail) {
				if (!isset($thumbnail['tab'])) {
					$tab_title = '~';
				} else {
					$tab_title = $thumbnail['tab'];
				}

				if (!isset($thumbnails[$tab_title])) {
					$thumbnails[$tab_title] = array();
				}

				$thumbnails[$tab_title][] =
					'<div class="builder-item-type" data-builder-item-type="'. esc_attr($item->get_type()) .'">'.
						$thumbnail['html'].
					'</div>';
			}
		}

		if (method_exists($this, 'sort_thumbnails')) {
			$this->sort_thumbnails($thumbnails);
		}

		return fw_render_view(dirname(__FILE__) .'/../view.php', array(
			'id'         => $id,
			'option'     => $option,
			'data'       => $data,
			'thumbnails' => $thumbnails,
			'option_type'=> $this->get_type()
		));
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (empty($input_value)) {
			$input_value = $option['value']['json'];
		}

		$items = json_decode($input_value, true);

		if (!$items) {
			$items = array();
		}

		return array(
			'json' => json_encode(
				$this->get_value_from_items($items)
			),
		);
	}

	/**
	 * Get correct value from items
	 * @param array $items
	 * @return array
	 */
	public function get_value_from_items($items)
	{
		/**
		 * @var FW_Option_Type_Builder_Item[] $item_types
		 */
		$item_types = $this->get_item_types();

		$fixed_items = array();

		foreach ($items as $item_attributes) {
			if (!isset($item_attributes['type']) || !isset($item_types[ $item_attributes['type'] ])) {
				// invalid item type
				continue;
			}

			$fixed_item_attributes = $item_types[ $item_attributes['type'] ]->get_value_from_attributes($item_attributes);

			if (isset($fixed_item_attributes['_items'])) {
				$fixed_item_attributes['_items'] = $this->get_value_from_items($fixed_item_attributes['_items']);
			}

			$fixed_items[] = $fixed_item_attributes;
		}

		return $fixed_items;
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'full';
	}
}
