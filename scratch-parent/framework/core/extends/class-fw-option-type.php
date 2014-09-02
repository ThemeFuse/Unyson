<?php if (!defined('FW')) die('Forbidden');

/**
 * Backend option
 */
abstract class FW_Option_Type
{
	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * Generate option's html from option array
	 * @param string $id
	 * @param array  $option
	 * @param array  $data
	 * @return string HTML
	 * @internal
	 */
	abstract protected function _render($id, $option, $data);

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 * @param array  $option
	 * @param array|string|null $input_value
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	abstract protected function _get_value_from_input($option, $input_value);

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with array returned from this method
	 *
	 * @return array
	 *
	 * array(
	 *     'value' => '',
	 *     ...
	 * )
	 * @internal
	 */
	abstract protected function _get_defaults();

	/**
	 * Used as prefix for attribute id="{$this->id_prefix}$id"
	 * @return string
	 */
	final public static function get_default_id_prefix()
	{
		return 'fw-option-';
	}

	/**
	 * Used as default prefix for attribute name="$prefix[$name]"
	 * Cannot contain [], it is used for $_POST[ self::get_default_name_prefix() ]
	 * @return string
	 */
	final public static function get_default_name_prefix()
	{
		return 'fw_options';
	}

	final public function __construct()
	{
		// does nothing at the moment, but maybe in the future will do something

		if (method_exists($this, '_init')) {
			$this->_init();
		}
	}

	/**
	 * Fix and prepare attributes
	 *
	 * @param string $id
	 * @param array  $option
	 * @param array  $data
	 * @return array
	 */
	private static function prepare($id, $option, $data)
	{
		if (!isset($option['attr'])) {
			$option['attr'] = array();
		}

		$option['attr']['name']  = $data['name_prefix'] .'['. $id .']';
		$option['attr']['id']    = $data['id_prefix'] . $id;
		$option['attr']['class'] = 'fw-option fw-option-type-'. $option['type'] .(
			isset($option['attr']['class'])
				? ' '. $option['attr']['class']
				: ''
			);
		$option['attr']['value'] = is_array($option['value']) ? '' : $option['value'];

		// remove some blacklisted attributes. this should be added only by render method
		unset($option['attr']['type']);
		unset($option['attr']['checked']);
		unset($option['attr']['selected']);

		return $option;
	}

	/**
	 * Generate option's html from option array
	 * @param string $id
	 * @param  array $option
	 * @param  array $data
	 * @return string HTML
	 */
	final public function render($id, $option, $data = array())
	{
		$data = array_merge(
			array(
				'id_prefix'   => self::get_default_id_prefix(),   // attribute id prefix
				'name_prefix' => self::get_default_name_prefix(), // attribute name prefix
			),
			$data
		);

		$option = array_merge(
			$this->get_defaults(),
			$option,
			array(
				'type' => $this->get_type()
			)
		);

		if (!isset($data['value'])) {
			// if no input value, use default
			$data['value'] = $option['value'];
		}

		$option = self::prepare($id, $option, $data);

		{
			wp_enqueue_style(
				'fw-option-types',
				FW_URI .'/static/css/option-types.css',
				array('fw', 'qtip'),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				'fw-option-types',
				FW_URI .'/static/js/option-types.js',
				array('fw-events', 'qtip'),
				fw()->manifest->get_version(),
				true
			);
		}

		return $this->_render($id, $option, $data);
	}

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 * @param  array $option
	 * @param  mixed|null $input_value Option's value from $_POST or elsewhere. If is null, it means it does not exists
	 * @return array|string
	 */
	final public function get_value_from_input($option, $input_value)
	{
		$option = array_merge(
			$this->get_defaults(),
			$option,
			array(
				'type' => $this->get_type()
			)
		);

		return $this->_get_value_from_input($option, $input_value);
	}

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with array returned from this method
	 *
	 * @return array
	 */
	final public function get_defaults()
	{
		$option = $this->_get_defaults();

		$option['type'] = $this->get_type();

		if (!isset($option['value'])) {
			FW_Flash_Messages::add(
				'fw-option-type-no-default-value',
				sprintf(__('Option type %s has no default value', 'fw'), $this->get_type()),
				'warning'
			);

			$option['value'] = array();
		}

		return $option;
	}

	/**
	 * Exist 3 types of options widths:
	 * - auto (float left real width of the option (minimal) )
	 * - fixed (inputs, select, textarea, and others - they have same width)
	 * - full (100% . eg. html option should expand to maximum width)
	 * Options can override this method to return another value
	 * @return bool
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'fixed';
	}

	/**
	 * Use this method to register a new option type
	 * @param string|FW_Option_Type $option_type_class
	 */
	final public static function register($option_type_class) {
		static $registration_access_key = null;

		if ($registration_access_key === null) {
			$registration_access_key = new FW_Access_Key('register_option_type');
		}

		fw()->backend->_register_option_type($registration_access_key, $option_type_class);
	}
}