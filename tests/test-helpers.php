<?php

class Tests_Unyson_Main extends WP_Unyson_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_helpers_existence() {
		$this->assertTrue(function_exists('fw_print'));
	}

	public function test_fw_collect_options() {
		$options = array(
			'b1' => array(
				'type' => 'box',
				'options' => array(
					'b1o1' => array('type' => 'text'),
					'b1o2' => array('type' => 'textarea'),
				),
			),
			't1' => array(
				'type' => 'tab',
				'options' => array(
					't1o1' => array('type' => 'select'),
					't1o2' => array('type' => 'radio'),
				),
			),
			array(
				't2' => array(
					'type' => 'tab',
					'options' => array(
						't2o1' => array('type' => 'text'),
					),
				),
			),
			array(
				array(
					't3' => array(
						'type' => 'tab',
						'options' => array(
							't3o1' => array('type' => 'text'),
						),
					),
				),
			),
			'g1' => array(
				'type' => 'group',
				'options' => array(
					'g1o1' => array('type' => 'checkbox'),
					'g1o2' => array('type' => 'checkboxes'),
				),
			),
			'g2' => array(
				'type' => 'group',
				'options' => array(
					'g2o1' => array('type' => 'checkbox'),
					array(
						'g2o2' => array('type' => 'checkboxes'),
					),
					array(
						array(
							'g2o2' => array('type' => 'checkboxes'),
						)
					),
				),
			),
			'g3' => array(
				'type' => 'group',
				'options' => array(
					array(
						'g2o2' => array('type' => 'checkboxes'),
					),
					array(
						array(
							'g2o2' => array('type' => 'checkboxes'),
						)
					),
					'g2o1' => array('type' => 'checkbox'),
					'g2b1' => array(
						'type' => 'box',
						'options' => array(
							'g3b1o1' => array('type' => 'text'),
							array(
								'g3b1o2' => array('type' => 'text'),
							),
						),
					),
					array(
						'g2b2' => array(
							'type' => 'box',
							'options' => array(
								array(
									array(
										array(
											'g3b2o1' => array('type' => 'text'),
										),
									),
								),
							),
						),
					),
				),
			),
			'o1' => array('type' => 'text'),
			array(
				'o2' => array('type' => 'textarea'),
			),
			array(
				array(
					'o3' => array('type' => 'text'),
				),
			),
		);

		{
			$result = array();

			fw_collect_options($result, $options);

			$this->assertEquals(
				array(
					'o1' => array('type' => 'text'),
					'o2' => array('type' => 'textarea'),
					'o3' => array('type' => 'text'),
					'b1o1' => array('type' => 'text'),
					'b1o2' => array('type' => 'textarea'),
					't1o1' => array('type' => 'select'),
					't1o2' => array('type' => 'radio'),
					't2o1' => array('type' => 'text'),
					't3o1' => array('type' => 'text'),
					'g1o1' => array('type' => 'checkbox'),
					'g1o2' => array('type' => 'checkboxes'),
					'g2o1' => array('type' => 'checkbox'),
					'g2o2' => array('type' => 'checkboxes'),
					'g2o2' => array('type' => 'checkboxes'),
					'g2o2' => array('type' => 'checkboxes'),
					'g2o2' => array('type' => 'checkboxes'),
					'g2o1' => array('type' => 'checkbox'),
					'g3b1o1' => array('type' => 'text'),
					'g3b1o2' => array('type' => 'text'),
					'g3b2o1' => array('type' => 'text'),
				),
				$result
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit' => 1,
			));

			$this->assertEquals(1, count($result));

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit' => 3,
			));

			$this->assertEquals(3, count($result));

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_option_types' => array('text', 'textarea'),
			));

			$this->assertEquals(
				array(
					'o1' => array('type' => 'text'),
					'o2' => array('type' => 'textarea'),
					'o3' => array('type' => 'text'),
					'b1o1' => array('type' => 'text'),
					'b1o2' => array('type' => 'textarea'),
					't2o1' => array('type' => 'text'),
					't3o1' => array('type' => 'text'),
					'g3b1o1' => array('type' => 'text'),
					'g3b1o2' => array('type' => 'text'),
					'g3b2o1' => array('type' => 'text'),
				),
				$result
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_level' => 1,
			));

			$this->assertEquals(
				array(
					'o1' => array('type' => 'text'),
					'o2' => array('type' => 'textarea'),
					'o3' => array('type' => 'text'),
				),
				$result
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_level' => 1,
				'limit_option_types' => array('text'),
			));

			$this->assertEquals(
				array(
					'o1' => array('type' => 'text'),
					'o3' => array('type' => 'text'),
				),
				$result
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_level' => 1,
				'limit_option_types' => array(),
			));

			$this->assertEquals(
				array(),
				$result
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_option_types' => array(),
				'limit_container_types' => false,
			));

			$this->assertEquals(
				array(
					'b1',
					't1',
					't2',
					't3',
					'g1',
					'g2',
					'g3',
					'g2b1',
					'g2b2',
				),
				array_keys($result)
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_option_types' => array(),
				'limit_container_types' => array('box', 'tab'),
			));

			$this->assertEquals(
				array(
					'b1',
					't1',
					't2',
					't3',
					'g2b1',
					'g2b2',
				),
				array_keys($result)
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_option_types' => array(),
				'limit_container_types' => false,
				'limit_level' => 1,
			));

			$this->assertEquals(
				array(
					'b1',
					't1',
					't2',
					't3',
					'g1',
					'g2',
					'g3',
				),
				array_keys($result)
			);

			unset($result);
		}

		{
			$result = array();

			fw_collect_options($result, $options, array(
				'limit_option_types' => array(),
				'limit_container_types' => array('box'),
				'limit_level' => 1,
			));

			$this->assertEquals(
				array(
					'b1',
				),
				array_keys($result)
			);

			unset($result);
		}
	}
}