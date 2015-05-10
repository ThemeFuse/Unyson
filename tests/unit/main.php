<?php

class Tests_Unyson_Main extends WP_UnitTestCase {

	/**
	 * Activate the plugin, mock all the things
	 */
	public function setUp() {
		parent::setUp();

		/**
		 * Start the framework
		 */
		_action_init_framework();
	}

	public function test_helpers_existence() {
		$this->assertTrue(function_exists('fw_print'));
	}
}