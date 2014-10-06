<?php

function _action_fw_builder_fullscreen_set_storage_item()
{
	$path = 'builder_fullscreen';
	$data = array_unique(array_merge(fw_builder_fullscreen_get_storage_items(), (array)FW_Request::POST('post_id')));
	fw_set_db_settings_option($path, $data);
}

function _action_fw_builder_fullscreen_unset_storage_item()
{
	$path = 'builder_fullscreen';
	$post_id = FW_Request::post('post_id');
	$storage_items = fw_builder_fullscreen_get_storage_items();
	$key = array_search($post_id, $storage_items);
	unset($storage_items[$key]);
	fw_set_db_settings_option($path, $storage_items);
}

function fw_builder_fullscreen_get_storage_items()
{
	return (array)fw_get_db_settings_option('builder_fullscreen');
}

function fw_builder_is_fullscreen_on()
{
	$post_id = get_the_ID();
	$storage_items = fw_builder_fullscreen_get_storage_items();
	return (false !== $post_id and in_array($post_id, $storage_items));
}

function _filter_fw_builder_fullscreen_add_classes($str)
{
	return fw_builder_is_fullscreen_on() ? ($str . ' builder-fullscreen') : $str;
}

function _action_fw_builder_fullscreen_add_backdrop()
{
	$hidden_class = fw_builder_is_fullscreen_on() ? '' : 'fw-hidden';
	echo '<div id="builder-backdrop" class="' . $hidden_class . '">
	<div class="buttons-wrapper">
		<span class="spinner"></span>
		<a class="preview button">'.__('Preview Changes', 'fw').'</a>
		<a class="button button-primary">'.__('Update', 'fw').'</a>
		</div>
	</div>';
}

add_action('wp_ajax_fw_builder_fullscreen_set_storage_item', '_action_fw_builder_fullscreen_set_storage_item');
add_action('wp_ajax_fw_builder_fullscreen_unset_storage_item', '_action_fw_builder_fullscreen_unset_storage_item');
add_filter('fw_builder_fullscreen_add_classes', '_filter_fw_builder_fullscreen_add_classes');
add_action('fw_builder_fullscreen_add_backdrop', '_action_fw_builder_fullscreen_add_backdrop');