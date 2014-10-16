<?php

function load_builder_templates()
{
	$data = (array)fw_get_db_settings_option('builder_template/' . FW_Request::POST('builder_type'));
	wp_send_json_success($data);
}

function save_builder_template()
{
	$uniqid = uniqid();
	$post_title = FW_Request::POST('template_name');
	$post_title = empty($post_title) ? __('No Title', 'fw') : $post_title;
	$data = array('title' => $post_title, 'json' => FW_Request::POST('builder_json'));
	fw_set_db_settings_option('builder_template/' . $_POST['builder_type'] . '/' . $uniqid, $data);
	wp_send_json_success(array_merge(array('id' => $uniqid), $data));
}

function delete_builder_template()
{
	$path = 'builder_template/' . $_POST['builder_type'];
	$db_options = fw_get_db_settings_option($path);

	fw_aku(FW_Request::POST('uniqid'), $db_options);
	fw_set_db_settings_option($path, $db_options);
}

add_action('wp_ajax_load_builder_templates', 'load_builder_templates');
add_action('wp_ajax_save_builder_template', 'save_builder_template');
add_action('wp_ajax_delete_builder_template', 'delete_builder_template');