<?php if (!defined('FW')) die('Forbidden');

class Shortcode_Icon extends FW_Shortcode
{

	/**
	 * @internal
	 */
	public function _init()
	{
		if (is_admin()) {
			add_filter($this->builder_type . '-shortcode-' . 'icon' . '-shortcode-notation', array($this, '_admin_filter_shortcode_notation'), 10, 2);
		}
	}


	public function _admin_filter_shortcode_notation($notation, $atts){
		
		$attributes = $atts['optionValues'];
		
		$attributes['color'] = (!empty($attributes['color'])) ? $attributes['color'] : '#000';
		$attributes['size']  =  (!empty($attributes['size'])) ? $attributes['size'] : '40';

		return '[icon '. fw_attr_to_html($attributes).']';
	}

}	
