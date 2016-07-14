<?php

if (!function_exists('fw_fix_path')) {
	_action_init_framework();
}

class TestsUnysonWpEditor extends WP_Unyson_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_we_have_editor_manager() {
		$this->assertTrue(class_exists('FW_WP_Editor_Manager'));

		$id = 'my-editor';
		$option = array('type' => 'wp-editor');
		$data = array();

		fw()->backend->option_type('wp-editor')->prepare(
			$id, $option, $data
		);

		$editor_manager = new FW_WP_Editor_Manager(
			$id, $option, $data
		);

		$html =  $editor_manager->get_html();

		$this->assertRegexp('/data-fw-editor-id/', $html);
	}
}

