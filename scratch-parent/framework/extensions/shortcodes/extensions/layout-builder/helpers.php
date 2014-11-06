<?php if (!defined('FW')) die('Forbidden');

function fw_ext_layout_builder_is_builder_post($post_id = '')
{
	return fw()->extensions->get('layout-builder')->is_builder_post($post_id);
}
