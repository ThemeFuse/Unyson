<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );

/**
 * @since 2.4.10
 */
abstract class FW_Type_Register {
	/**
	 * Check if the type is instance of the required class (or other requirements)
	 * @param FW_Type $type
	 * @return bool|WP_Error
	 */
	abstract protected function validate_type(FW_Type $type);

	/**
	 * @var FW_Type[]
	 */
	private $types = array();

	/**
	 * Only these access keys will be able to access the registered types
	 * @var array {'key': true}
	 */
	private $access_keys = array();

	final public function __construct($access_keys) {
		{
			if (is_string($access_keys)) {
				$access_keys = array(
					$access_keys => true,
				);
			} elseif (!is_array($access_keys)) {
				trigger_error('Invalid access key', E_USER_ERROR);
			}

			$this->access_keys = $access_keys;
		}
	}

	public function register(FW_Type $type) {
		if (isset($this->task_types[$type->get_type()])) {
			throw new Exception('Type '. $type->get_type() .' already registered');
		} elseif (
			is_wp_error($validation_result = $this->validate_type($type))
			||
			!$validation_result
		) {
			throw new Exception(
				'Invalid type '. $type->get_type()
				.(is_wp_error($validation_result) ? ': '. $validation_result->get_error_message() : '')
			);
		}

		$this->types[$type->get_type()] = $type;
	}

	/**
	 * @param FW_Access_Key $access_key
	 *
	 * @return FW_Type[]
	 * @internal
	 */
	public function _get_types(FW_Access_Key $access_key) {
		if (!isset($this->access_keys[$access_key->get_key()])) {
			trigger_error('Method call denied', E_USER_ERROR);
		}

		return $this->types;
	}
}
