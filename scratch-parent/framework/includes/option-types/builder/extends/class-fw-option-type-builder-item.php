<?php if (!defined('FW')) die('Forbidden');

abstract class FW_Option_Type_Builder_Item
{
	/**
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * @return string The type of the builder to which this item is connected
	 */
	abstract public function get_builder_type();

	/**
	 * @return array(
	 *  array(
	 *      'html' => '<p class="dashicons dashicons-smiley"></p><p>Item Title</p>',
	 *  ),
	 *  array(
	 *      'tab' => __('Tab Title', 'fw'),
	 *      'html' => '<p class="dashicons dashicons-smartphone"></p><p>Item Title</p>',
	 *  ),
	 *  ...
	 * )
	 */
	abstract public function get_thumbnails();

	/**
	 * Called when builder is rendered
	 */
	abstract public function enqueue_static();

	final public function __construct()
	{
		// Maybe in the future this method will have some functionality

		if (method_exists($this, '_init')) {
			$this->_init();
		}
	}

	/**
	 * Overwrite this method if you want to change/fix attributes that comes from js
	 * @param $attributes array Backbone Item (Model) attributes
	 * @return mixed
	 */
	public function get_value_from_attributes($attributes)
	{
		return $attributes;
	}
}
