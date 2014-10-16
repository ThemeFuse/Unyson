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
	 * Overwrite this method to enqueue scripts and styles
	 *
	 * Enqueue scripts and styles in the _render() method is deprecated.
	 *
	 * This method would be abstract but was added after the framework release,
	 * and to prevent fatal errors from new option types created by users we can't make it abstract.
	 *
	 * @param string $id
	 * @param array  $option
	 * @param array  $data
	 * @param bool   Return true to call this method again on the next enqueue,
	 *               if you have some functionality in it that depends on option parameters.
	 *               By default this method is called only once for performance reasons.
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		/**
		 * @deprecated
		 *
		 * Call the _render() method to enqueue styles and scripts for option types created before this method was added
		 *
		 * For example this option type
		 * https://github.com/ThemeFuse/Unyson/blob/e650bf083dde23ae2842344e70a16d5c335bf876/scratch-parent/framework-customizations/theme/shortcodes/table/includes/fw-option-type-table-builder/class-fw-option-type-table-builder.php#L13
		 * is located in the theme and will remain there forever because the theme is not affected by the framework update
		 */
		$this->_render($id, $option, $data);
	}

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
	 * Prevent execute enqueue multiple times
	 * @var bool
	 */
	private $static_enqueued = false;

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
	 * Fixes and prepare defaults
	 *
	 * @param string $id
	 * @param array  $option
	 * @param array  $data
	 * @return array
	 */
	private function prepare(&$id, &$option, &$data)
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

		/**
		 * Remove some blacklisted attributes
		 * They should be added only by the render method
		 */
		{
			unset($option['attr']['type']);
			unset($option['attr']['checked']);
			unset($option['attr']['selected']);
		}
	}

	/**
	 * Generate option's html from option array
	 * @param  string $id
	 * @param   array $option
	 * @param   array $data
	 * @return string HTML
	 */
	final public function render($id, $option, $data = array())
	{
		$this->prepare($id, $option, $data);

		$this->enqueue_static($id, $option, $data);

		return $this->_render($id, $option, $data);
	}

	/**
	 * Enqueue option type scripts and styles
	 *
	 * All parameters are optional and will be populated with defaults
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 * @return bool
	 */
	final public function enqueue_static($id = '', $option = array(), $data = array())
	{
		{
			static $option_types_static_enqueued = false;

			if (!$option_types_static_enqueued) {
				wp_enqueue_style(
					'fw-option-types',
					fw_get_framework_directory_uri('/static/css/option-types.css'),
					array('fw', 'qtip'),
					fw()->manifest->get_version()
				);
				wp_enqueue_script(
					'fw-option-types',
					fw_get_framework_directory_uri('/static/js/option-types.js'),
					array('fw-events', 'qtip'),
					fw()->manifest->get_version(),
					true
				);

				$option_types_static_enqueued = true;
			}
		}

		if ($this->static_enqueued) {
			return false;
		}

		$this->prepare($id, $option, $data);

		$call_next_time = $this->_enqueue_static($id, $option, $data);

		$this->static_enqueued = !$call_next_time;

		return $call_next_time;
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