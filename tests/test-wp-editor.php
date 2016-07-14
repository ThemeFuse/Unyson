<?php

if (!function_exists('fw_fix_path')) {
	_action_init_framework();
}

class TestsUnysonWpEditor extends WP_Unyson_UnitTestCase {
	private $test_field = 'first';

	public function setUp() {
		parent::setUp();

		$id = 'my-editor';
		$option = array('type' => 'wp-editor', 'tinymce' => true);
		$data = array();

		fw()->backend->option_type('wp-editor')->prepare(
			$id, $option, $data
		);

		$this->editor_manager = new FW_WP_Editor_Manager(
			$id, $option, $data
		);

		// wp-includes/class-wp-editor.php
		// self::$this_tinymce = ( $set['tinymce'] && user_can_richedit() );
		add_filter(
			'user_can_richedit',
			array($this, 'user_should_be_able_to_rich_edit_in_tests'),
			999999999999999
		);
	}

	function user_should_be_able_to_rich_edit_in_tests() {
		return true;
	}

	public function test_we_have_editor_manager() {
		$this->assertTrue(class_exists('FW_WP_Editor_Manager'));
	}

	public function test_we_get_html_correctly() {
		$html =  $this->editor_manager->get_html();
		$this->assertRegexp('/data-fw-editor-id/', $html);
	}

	public function test_actions_are_called_in_correct_order() {
		add_filter(
			'quicktags_settings',
			array($this, 'quicktags_settings_callback_in_middle'),
			10
		);

		add_filter(
			'quicktags_settings',
			array($this, 'quicktags_settings_callback_in_last'),
			999999999999999
		);

		$this->editor_manager->get_html();

		$this->assertEquals(
			$this->test_field,
			'last'
		);
	}

	public function quicktags_settings_callback_in_middle($qtInit) {
		$this->test_field = 'middle';
		return $qtInit;
	}

	public function quicktags_settings_callback_in_last($qtInit) {
		$this->test_field = 'last';
	}
}

