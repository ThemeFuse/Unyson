<?php if (!defined('FW')) die('Forbidden');

/**
 * @deprecated since 2.5.0
 */
interface FW_Option_Handler
{
	function get_option_value($option_id, $option, $data = array());

	function save_option_value($option_id, $option, $value, $data = array());
}

