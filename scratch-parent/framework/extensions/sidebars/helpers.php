<?php if (!defined('FW')) die('Forbidden');

/**
 * Returns the sidebar HTML or warning message
 *
 * @param $color look in _FW_Extension_Sidebars_Config::$allowed_colors
 */
function fw_ext_sidebars_show($color)
{
	return fw()->extensions->get('sidebars')->render_sidebar($color);
}

/**
 * Returns string (position-id) if DB has preset for current page else return false
 *
 */
function fw_ext_sidebars_get_current_position()
{
	return fw()->extensions->get('sidebars')->get_current_positon();
}

/**
 * Returns array if DB has preset for current page else return null
 */
function fw_ext_sidebars_get_current_preset()
{
	return fw()->extensions->get('sidebars')->get_current_preset();
}