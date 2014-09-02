<?php if (!defined('FW')) die('Forbidden');

register_post_type(fw()->extensions->get('backup')->get_post_type(), array(
	'labels' => array(
		'name'               => __('Backups', 'fw'),
		'singular_name'      => __('Backup', 'fw'),
		'add_new'            => __('Add New', 'fw'),
		'add_new_item'       => __('Add New Backup', 'fw'),
		'edit_item'          => __('Edit Backup', 'fw'),
		'new_item'           => __('New Backup', 'fw'),
		'all_items'          => __('Backup', 'fw'), // __('All Backups', 'fw'),
		'view_item'          => __('View Backup', 'fw'),
		'search_items'       => __('Search Backups', 'fw'),
		'not_found'          => __('Nothing found', 'fw'),
		'not_found_in_trash' => __('Nothing found in Trash', 'fw'),
		'parent_item_colon'  => ''
	),
	'public'                => false,
	'publicly_queryable'    => false,
	'show_ui'               => true,
	'show_in_nav_menus'     => false,
	'show_in_menu'          => 'tools.php',

	// WordPress: Disable “Add New” on Custom Post Type
	// http://stackoverflow.com/a/16675677
	'map_meta_cap' => true,
	'capability_type' => 'post',
	'capabilities' => array(
		'edit_post'         => 'edit_files',
		'read_post'         => 'edit_files',
		'delete_post'       => 'edit_files',
		'edit_posts'        => 'edit_files',
		'edit_others_posts' => 'edit_files',
		'publish_posts'     => 'edit_files',
		'read_private_posts'=> 'edit_files',

		'read'                  => 'edit_files',
		'delete_posts'          => 'edit_files',
		'delete_private_posts'  => 'edit_files',
		'delete_published_posts'=> 'edit_files',
		'delete_others_posts'   => 'edit_files',
		'edit_private_posts'    => 'edit_files',
		'edit_published_posts'  => 'edit_files',

		'create_posts'  => false,
	),
));
