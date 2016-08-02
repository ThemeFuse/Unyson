<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );

/**
 * Give users the possibility to register a type safely
 * Instead of doing apply_filters('my_types', $types) where someone can mess your data
 * with this class you do do_action('register_my_types', $types)
 * and users will be able only to $types->register(new Allowed_Type_Class())
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
	protected $types = array();

	/**
	 * Only these access keys will be able to access the registered types
	 * @var array {'key': true}
	 */
	protected $access_keys = array();

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

	/**
	 * @param FW_Access_Key $access_key
	 * @param $type
	 *
	 * @return FW_Type|null
	 * @internal
	 * @since 2.5.12
	 */
	public function _get_type(FW_Access_Key $access_key, $type) {
		if (!isset($this->access_keys[$access_key->get_key()])) {
			trigger_error('Method call denied', E_USER_ERROR);
		}

		return isset($this->types[$type]) ? $this->types[$type] : null;
	}
}
