<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Column extends FW_Shortcode
{
	/**
	 * @internal
	 */
	public function _init()
	{
		if (is_admin()) {
			$this->load_item_type();
		}
	}

	/**
	 * Loads a layout builder custom item type
	 */
	private function load_item_type()
	{
		require $this->get_path() .
			'/includes/fw-option-type-layout-builder-column-item/class-fw-option-type-layout-builder-column-item.php';
	}
}
