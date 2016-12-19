<?php

if (!function_exists('fw_fix_path')) {
	_action_init_framework();
}

class Test_Option_Type extends FW_Option_Type {
	public function get_type() {
		return 'test-option-type';
	}

	protected function _enqueue_static( $id, $option, $data ) {
	}

	protected function _render( $id, $option, $data ) {
		return '';
	}

	protected function _get_value_from_input( $option, $input_value ) {
		return (string) ( is_null( $input_value ) ? $option['value'] : $input_value );
	}

	protected function _get_defaults() {
		return array(
			'random-key' => '',
			'value' => 'default-val'
		);
	}
}

class TestsUnysonOptionTypes extends WP_Unyson_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_fw_option_type_is_arround() {
		$this->assertTrue(class_exists('FW_Option_Type'));
	}

	public function test_unregistered_option_is_undefined() {
		$this->assertEquals(
			get_class(fw()->backend->option_type('test-option-type')),
			'FW_Option_Type_Undefined'
		);
	}

	public function test_registered_option_is_accessible() {
		FW_Option_Type::register('Test_Option_Type');

		$this->assertArrayHasKey(
			'value',
			fw()->backend->option_type('test-option-type')->get_defaults()
		);

		$this->assertArrayNotHasKey(
			'some-really-random-key',
			fw()->backend->option_type('test-option-type')->get_defaults()
		);
	}

	public function test_get_value_from_input_works_correctly() {
		// IMPORTANT:
		// Option type is already registered from previous test
		//
		// The real proper way to do this would be to separate tests
		// when the option type is activated and when it is not.
		// When we separate them into groups we'll be able to register
		// option_type in setUp.
		//
		// Also, there clearly should be an interface to unregister an option
		// type by demand. This one would be called in tearDown().
		//
		// FW_Option_Type::register('Test_Option_Type');

		$test_option = fw()->backend->option_type('test-option-type');

		$this->assertEquals(
			$test_option->get_value_from_input(array(), 'value-inserted-by-user'),
			'value-inserted-by-user'
		);
		$this->assertEquals(
			$test_option->get_value_from_input(array(), null),
			$test_option->get_defaults('value')
		);
	}

	public function test_option_type_give_right_default_when_provided_null() {
		$option = fw()->backend->option_type('test-option-type');

		$value = $option->get_value_from_input(
			array(), // type would be added from defaults
			null
		);

		$this->assertEquals($value, 'default-val');
	}

	public function test_option_type_gets_right_defaults() {
		$option = fw()->backend->option_type('test-option-type'); 

		$this->assertArrayHasKey(
			'random-key',
			$option->get_defaults()
		);
	}
}

