<?php if (!defined('FW')) die('Forbidden');

$options = array(
	'demo_text_2' => array(
		'label' => __('Text', 'fw'),
		'type'  => 'text',
		'value' => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_short_text_2' => array(
		'label' => __('Short Text', 'fw'),
		'type'  => 'short-text',
		'value' => '7',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_password_2' => array(
		'label' => __('Password', 'fw'),
		'type'  => 'password',
		'value' => 'Dotted text',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_hidden_2' => array(
		'label' => false,
		'type'  => 'hidden',
		'value' => '{some: "json"}',
		'desc'  => false,
	),
	'demo_textarea_2' => array(
		'label' => __('Textarea', 'fw'),
		'type'  => 'textarea',
		'value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => array(
			'icon' => 'video',
			'html' => '<iframe width="420" height="236" src="https://player.vimeo.com/video/101070863" frameborder="0" allowfullscreen></iframe>'
		),
	),
	'demo_wp_editor_2' => array(
		'label' => __('Rich Text Editor', 'fw'),
		'type' => 'wp-editor',
		'value' => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_html_2' => array(
		'label' => __('HTML', 'fw'),
		'type'  => 'html',
		'value' => '{some: "json"}',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'html'  => '<em>Lorem</em> <b>ipsum</b> <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAANbY1E9YMgAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADWSURBVDjLlZNNCsIwEEZzKW/jyoVbD+Aip/AGgmvRldCKNxDBv4LSfSG7kBZix37BQGiapA48ZpjMvIZAGRExwDmnESw7MMvsHnMFTdOQUsqjrmtXsggKEEVReCDseZc/HbOgoCxLDytwUEFBVVUe/fjNDguEEFGSAiml4Xq+DdZJAV78sM1oOpnT/fI0oEYPZ0lBtjuaBWSttcHtRQWvx9sMrlcb7+HQwxlmojfI9ycziGyj34sK3AV8zd7KFSYFCCwO1aMFsQgK8DO1bRsFM0HBP9i9L2ONMKHNZV7xAAAAAElFTkSuQmCC">',
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_checkbox_2' => array(
		'label' => __('Checkbox', 'fw'),
		'type'  => 'checkbox',
		'value' => true,
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'text'  => __('Custom text', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_checkboxes_2' => array(
		'label' => __('Checkboxes', 'fw'),
		'type'  => 'checkboxes',
		'value' => array(
			'c1' => false,
			'c2' => true,
		),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'choices' => array(
			'c1'  => __('Checkbox 1 Custom Text', 'fw'),
			'c2'  => __('Checkbox 2 Custom Text', 'fw'),
			'c3'  => __('Checkbox 3 Custom Text', 'fw'),
		),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_switch_2' => array(
		'label' => __('Switch', 'fw'),
		'type'  => 'switch',
		'right-choice' => array(
			'value' => 'yes',
			'label' => __('Yes', 'fw')
		),
		'left-choice' => array(
			'value' => 'no',
			'label' => __('No', 'fw')
		),
		'value' => 'yes',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_select_2' => array(
		'label' => __('Select', 'fw'),
		'type'  => 'select',
		'value' => 'c',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'choices' => array(
			''  => '---',
			'a' => __('Lorem ipsum', 'fw'),
			'b' => array(
				'text' => __('Consectetur', 'fw'),
				'attr' => array(
					'label' => 'Label overrides text',
					'data-whatever' => 'some data',
				),
			),
			array(
				'attr' => array(
					'label' => __('Optgroup Label', 'fw'),
					'data-whatever' => 'some data',
				),
				'choices' => array(
					'c' => __('Sed ut perspiciatis', 'fw'),
					'd' => __('Excepteur sint occaecat', 'fw'),
				),
			),
			1 => __('One', 'fw'),
			2 => __('Two', 'fw'),
			3 => __('Three', 'fw'),
		),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_short_select_2' => array(
		'label' => __('Short Select', 'fw'),
		'type'  => 'short-select',
		'value' => '7',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'choices' => array(
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'7' => '7',
		),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_select_multiple_2' => array(
		'label' => __('Select Multiple', 'fw'),
		'type'  => 'select-multiple',
		'value' => array('c', '2'),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'choices' => array(
			''  => '---',
			'a' => __('Lorem ipsum', 'fw'),
			'b' => array(
				'text' => __('Consectetur', 'fw'),
				'attr' => array(
					'label' => 'Label overrides text',
					'data-whatever' => 'some data',
				),
			),
			array(
				'attr' => array(
					'label' => __('Optgroup Label', 'fw'),
					'data-whatever' => 'some data',
				),
				'choices' => array(
					'c' => __('Sed ut perspiciatis', 'fw'),
					'd' => __('Excepteur sint occaecat', 'fw'),
				),
			),
			1 => __('One', 'fw'),
			2 => __('Two', 'fw'),
			3 => __('Three', 'fw'),
		),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),

	'demo_group_multi_select_2' => array(
		'type' => 'group',
		'options' => array(
			'demo_multi_select_posts_2' => array(
				'type' => 'multi-select',
				'label' => __('Multi-Select: Posts', 'fw'),
				'population' => 'posts',
				'source' => 'page',
				'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_multi_select_taxonomies_2' => array(
				'type' => 'multi-select',
				'label' => __('Multi-Select: Taxonomies', 'fw'),
				'population' => 'taxonomy',
				'source' => 'category',
				'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_multi_select_users_2' => array(
				'type' => 'multi-select',
				'label' => __('Multi-Select: Users', 'fw'),
				'population' => 'users',
				'source' => 'administrator',
				'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_multi_select_array_2' => array(
				'type' => 'multi-select',
				'label' => __('Multi-Select: Custom Array', 'fw'),
				'population' => 'array',
				'choices' => array(
					'hello' => __('Hello', 'fw'),
					'world' => __('World', 'fw'),
				),
				'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
		),
	),
	'demo_radio_2' => array(
		'label' => __('Radio', 'fw'),
		'type'  => 'radio',
		'value' => 'c2',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'choices' => array(
			'c1'  => __('Radio 1 Custom Text', 'fw'),
			'c2'  => __('Radio 2 Custom Text', 'fw'),
			'c3'  => __('Radio 3 Custom Text', 'fw'),
		),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_radio_text_2' => array(
		'label' => __('Radio Text', 'fw'),
		'type'  => 'radio-text',
		'value' => '75',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'choices' => array(
			'25'  => __('25%', 'fw'),
			'50'  => __('50%', 'fw'),
			'100' => __('100%', 'fw'),
		),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_image_picker_2' => array(
		'label' => __('Image Picker', 'fw'),
		'type'  => 'image-picker',
		'value' => '',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'choices' => array(
			'choice-1' => array(
				'small' => array(
					'height' => 70,
					'src' => get_template_directory_uri() .'/images/image-picker-demo/thumb1.jpg'
				),
				'large' => array(
					'height' => 214,
					'src' => get_template_directory_uri() .'/images/image-picker-demo/tooltip1.jpg'
				),
			),
			'choice-2' => array(
				'small' => array(
					'height' => 70,
					'src' => get_template_directory_uri() .'/images/image-picker-demo/thumb2.jpg'
				),
				'large' => array(
					'height' => 214,
					'src' => get_template_directory_uri() .'/images/image-picker-demo/tooltip2.jpg'
				),
			),
		),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_icon_2' => array(
		'label' => __('Icon', 'fw'),
		'type'  => 'icon',
		'value' => 'fa fa-linux',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_upload_2' => array(
		'label'       => __('Single Upload', 'fw'),
		'desc'        => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'type'        => 'upload',
		'images_only' => false,
		'help'        => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_upload_images_2' => array(
		'label' => __('Single Upload (Images Only)', 'fw'),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'type'  => 'upload',
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_multi_upload_2' => array(
		'label'       => __('Multi Upload', 'fw'),
		'desc'        => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'type'        => 'multi-upload',
		'images_only' => false,
		'help'        => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_multi_upload_images_2' => array(
		'label' => __('Multi Upload (Images Only)', 'fw'),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'type'  => 'multi-upload',
		'help'        => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_color_picker_2' => array(
		'label' => __('Color Picker', 'fw'),
		'type'  => 'color-picker',
		'value' => '',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_gradient_2' => array(
		'label' => __('Gradient', 'fw'),
		'type'  => 'gradient',
		'value' => array(
			'primary'       => '#ffffff',
			'secondary'     => '#ffffff'
		),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_background_image_2' => array(
		'label' => __('Background Image', 'fw'),
		'type'  => 'background-image',
		'value'   => 'none',
		'choices' => array(
			'none' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/no_pattern.jpg',
				'css'  => array(
					'background-image' => 'none'
				)
			),
			'bg-1' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/diagonal_bottom_to_top_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/diagonal_bottom_to_top_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
			'bg-2' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/diagonal_top_to_bottom_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/diagonal_top_to_bottom_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
			'bg-3' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/dots_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/dots_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
			'bg-4' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/romb_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/romb_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
			'bg-5' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/square_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/square_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
			'bg-6' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/noise_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/noise_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
			'bg-7' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/vertical_lines_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/vertical_lines_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
			'bg-8' => array(
				'icon' => get_template_directory_uri() . '/images/patterns/waves_pattern_preview.jpg',
				'css'  => array(
					'background-image'  => 'url("' . get_template_directory_uri() . '/images/patterns/waves_pattern.png' . '")',
					'background-repeat' => 'repeat',
				)
			),
		),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_typography_2' => array(
		'label' => __('Typography', 'fw'),
		'type'  => 'typography',
		'value' => array(
			'size'      => 17,
			'family'    => 'Verdana',
			'style'     => '300italic',
			'color'     => '#0000ff'
		),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
	),
	'demo_datetime_range_2' => array(
		'type'  => 'datetime-range',
		'attr'  => array( 'class' => 'custom-class', 'data-foo' => 'bar' ),
		'label' => __('Demo date range', 'fw'),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
		'datetime-pickers' => array(
			'from' => array(
				'timepicker' => false,
				'datepicker' => true,
			),
			'to' => array(
				'timepicker' => false,
				'datepicker' => true,
			)
		),
		'value' => array(
			'from' => '',
			'to'   => ''
		)
	),
	'demo_datetime_picker_2' => array(
		'type'  => 'datetime-picker',
		'value' => '',
		'attr'  => array( 'class' => 'custom-class', 'data-foo' => 'bar' ),
		'label' => __('Date & Time picker', 'fw'),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
		'datetime-picker' => array(
			'format'        => 'd-m-Y H:i',
			'extra-formats' => array(),
			'moment-format' => 'DD-MM-YYYY HH:mm',
			'scrollInput'   => false,
			'maxDate'       => false,
			'minDate'       => false,
			'timepicker'    => true,
			'datepicker'    => true,
			'defaultTime'   => '12:00'
		)
	),
	'demo_addable_popup_2' => array(
		'label' => __('Addable Popup', 'fw'),
		'type' => 'addable-popup',
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'template' => '{{=demo_text}}',
		'popup-options' => array(
			'demo_text' => array(
				'label' => __('Text', 'fw'),
				'type' => 'text',
				'value' => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium',
				'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_image_picker' => array(
				'label' => __('Image Picker', 'fw'),
				'type'  => 'image-picker',
				'value' => '',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'attr'  => array(
					'data-height' => 70
				),
				'choices' => array(
					'choice-1' => array(
						'small' => array(
							'height' => 70,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/thumb1.jpg'
						),
						'large' => array(
							'height' => 214,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/tooltip1.jpg'
						),
					),
					'choice-2' => array(
						'small' => array(
							'height' => 70,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/thumb2.jpg'
						),
						'large' => array(
							'height' => 214,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/tooltip2.jpg'
						),
					),
				),
				'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_upload_images' => array(
				'label' => __('Single Upload (Images Only)', 'fw'),
				'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'type' => 'upload',
				'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_addable_popup_inner' => array(
				'label' => __('Addable Popup', 'fw'),
				'type' => 'addable-popup',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'template' => 'Title color-picker value : {{=demo_color_picker}}',
				'popup-options' => array(
					'demo_multi_upload_images' => array(
						'label' => __('Multi Upload (images only)', 'fw'),
						'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
						'type' => 'multi-upload',
						'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
							__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
							__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
						),
					),
					'demo_color_picker' => array(
						'label' => __('Color Picker', 'fw'),
						'type' => 'color-picker',
						'value' => '',
						'desc' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
						'help' => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
							__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
							__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
						),
					)
				)
			),
		),
	),
	'demo_addable_option_2' => array(
		'label' => __('Addable Option', 'fw'),
		'type'  => 'addable-option',
		'option' => array(
			'type' => 'text',
		),
		'value' => array('Option 1', 'Option 2', 'Option 3'),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		)
	),
	'demo_addable_box_2' => array(
		'label' => __('Addable Box', 'fw'),
		'type'  => 'addable-box',
		'value' => array(),
		'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
		'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
			__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
			__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
		),
		'box-controls' => array(
			//'custom' => '<small class="dashicons dashicons-smiley" title="Custom"></small>',
		),
		'box-options'   => array(
			'demo_text' => array(
				'label' => __('Text', 'fw'),
				'type'  => 'text',
				'value' => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_textarea' => array(
				'label' => __('Textarea', 'fw'),
				'type'  => 'textarea',
				'value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => array(
					'icon' => 'video',
					'html' => '<iframe width="420" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>'
				),
			),
		),
	),
	'demo_group_2' => array(
		'type' => 'group',
		'options' => array(
			'demo_text_in_group_2' => array(
				'label' => __('Text in Group', 'fw'),
				'type'  => 'text',
				'value' => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_password_in_group_2' => array(
				'label' => __('Password in Group', 'fw'),
				'type'  => 'password',
				'value' => 'Dotted text',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
		),
	),
	'demo_multi_2' => array(
		'label' => false,
		'type'  => 'multi',
		'value' => array(),
		'desc'  => false,
		'inner-options'  => array(
			'demo_text' => array(
				'label' => __('Text in Multi', 'fw'),
				'type'  => 'text',
				'value' => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
			'demo_textarea' => array(
				'label' => __('Textarea in Multi', 'fw'),
				'type'  => 'textarea',
				'value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			),
		),
	),
	'demo_multi_picker_select_2' => array(
		'type'  => 'multi-picker',
		'label' => false,
		'desc'  => false,
		'picker' => array(
			'gadget' => array(
				'label'   => __('Multi Picker: Select', 'fw'),
				'type'    => 'select',
				'choices' => array(
					'phone'  => __('Phone', 'fw'),
					'laptop' => __('Laptop', 'fw')
				),
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				)
			)
		),
		'choices' => array(
			'phone' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'memory' => array(
					'type'  => 'select',
					'label' => __('Memory', 'fw'),
					'choices' => array(
						'16' => __('16Gb', 'fw'),
						'32' => __('32Gb', 'fw'),
						'64' => __('64Gb', 'fw'),
					)
				)
			),
			'laptop' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'webcam' => array(
					'type'  => 'switch',
					'label' => __('Webcam', 'fw'),
				)
			),
		),
		'show_borders' => false,
	),
	'demo_multi_picker_radio_2' => array(
		'type'  => 'multi-picker',
		'label' => false,
		'desc'  => false,
		'value' => array(
			'gadget' => 'laptop',
		),
		'picker' => array(
			'gadget' => array(
				'label'   => __('Multi Picker: Radio', 'fw'),
				'type'    => 'radio',
				'choices' => array(
					'phone'  => __('Phone', 'fw'),
					'laptop' => __('Laptop', 'fw')
				),
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				)
			)
		),
		'choices' => array(
			'phone' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'memory' => array(
					'type'  => 'select',
					'label' => __('Memory', 'fw'),
					'choices' => array(
						'16' => __('16Gb', 'fw'),
						'32' => __('32Gb', 'fw'),
						'64' => __('64Gb', 'fw'),
					)
				)
			),
			'laptop' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'webcam' => array(
					'type'  => 'switch',
					'label' => __('Webcam', 'fw'),
				)
			),
		),
		'show_borders' => false,
	),
	'demo_multi_picker_image_picker_2' => array(
		'type'  => 'multi-picker',
		'label' => false,
		'desc'  => false,
		'picker' => array(
			'gadget' => array(
				'label' => __('Multi Picker: Image Picker', 'fw'),
				'type'  => 'image-picker',
				'choices' => array(
					'phone' => array(
						'label' => __('Phone', 'fw'),
						'small' => array(
							'height' => 70,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/thumb1.jpg'
						),
						'large' => array(
							'height' => 214,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/tooltip1.jpg'
						),
					),
					'laptop' => array(
						'label' => __('Laptop', 'fw'),
						'small' => array(
							'height' => 70,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/thumb2.jpg'
						),
						'large' => array(
							'height' => 214,
							'src' => get_template_directory_uri() .'/images/image-picker-demo/tooltip2.jpg'
						),
					)
				),
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				)
			)
		),
		'choices' => array(
			'phone' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'memory' => array(
					'type'  => 'select',
					'label' => __('Memory', 'fw'),
					'choices' => array(
						'16' => __('16Gb', 'fw'),
						'32' => __('32Gb', 'fw'),
						'64' => __('64Gb', 'fw'),
					)
				)
			),
			'laptop' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'webcam' => array(
					'type'  => 'switch',
					'label' => __('Webcam', 'fw'),
				)
			),
		),
		'show_borders' => false,
	),
	'demo_multi_picker_switch_2' => array(
		'type'  => 'multi-picker',
		'label' => false,
		'desc'  => false,
		'picker' => array(
			'gadget' => array(
				'label' => __('Switch', 'fw'),
				'type'  => 'switch',
				'right-choice' => array(
					'value' => 'laptop',
					'label' => __('Laptop', 'fw')
				),
				'left-choice' => array(
					'value' => 'phone',
					'label' => __('Phone', 'fw')
				),
				'value' => 'yes',
				'desc'  => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
				'help'  => sprintf("%s \n\n'\"<br/><br/>\n\n <b>%s</b>",
					__('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'fw'),
					__('Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium', 'fw')
				),
			)
		),
		'choices' => array(
			'phone' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'memory' => array(
					'type'  => 'select',
					'label' => __('Memory', 'fw'),
					'choices' => array(
						'16' => __('16Gb', 'fw'),
						'32' => __('32Gb', 'fw'),
						'64' => __('64Gb', 'fw'),
					)
				)
			),
			'laptop' => array(
				'price' => array(
					'type'  => 'text',
					'label' => __('Price', 'fw'),
				),
				'webcam' => array(
					'type'  => 'switch',
					'label' => __('Webcam', 'fw'),
				)
			),
		),
		'show_borders' => false,
	),
);
