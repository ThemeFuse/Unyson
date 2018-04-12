<?php if (!defined('FW')) die('Forbidden');

/**
 * Backend option
 */
abstract class FW_Option_Type
{
	/**
	 * @var FW_Access_Key
	 */
	private static $access_key;

	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * Overwrite this method to enqueue scripts and styles
	 *
	 * This method would be abstract but was added after the framework release,
	 * and to prevent fatal errors from new option types created by users we can't make it abstract.
	 *
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 * @param bool   Return true to call this method again on the next enqueue,
	 *               if you have some functionality in it that depends on option parameters.
	 *               By default this method is called only once for performance reasons.
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * Generate html
	 * @param string $id
	 * @param array $option Option array merged with _get_defaults()
	 * @param array $data {value => _get_value_from_input(), id_prefix => ..., name_prefix => ...}
	 * @return string HTML
	 * @internal
	 */
	abstract protected function _render($id, $option, $data);

	/**
	 * Extract correct value that will be stored in db or $option['value'] from raw form input value
	 * If input value is empty, will be returned $option['value']
	 * This method should be named get_db_value($form_input_value, $option)
	 * @param array $option Option array merged with _get_defaults()
	 * @param array|string|null $input_value
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	abstract protected function _get_value_from_input($option, $input_value);

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with the array returned by this method.
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
	 * Put data for to be accessed in JavaScript for each option type instance
	 */
	protected function _get_data_for_js($id, $option, $data = array()) {
		return array(
			'option' => $option
		);
	}

	/**
	 * An option type can decide which design to use by default when rendering
	 * itself.
	 *
	 * @return
	 *   null - will use whatever is passed based on the context
	 *   string - will use that particular design
	 */
	public function get_forced_render_design() {
		return null;
	}

	/**
	 * Prevent execute enqueue multiple times
	 * @var bool
	 */
	private $static_enqueued = false;

	/**
	 * Used as prefix for attribute id="{prefix}{option-id}"
	 * @return string
	 */
	final public static function get_default_id_prefix()
	{
		return fw()->backend->get_options_id_attr_prefix();
	}

	/**
	 * Used as default prefix for attribute name="prefix[name]"
	 * Cannot contain [], it is used for $_POST[ self::get_default_name_prefix() ]
	 * @return string
	 */
	final public static function get_default_name_prefix()
	{
		return fw()->backend->get_options_name_attr_prefix();
	}

	/**
	 * @param FW_Access_Key $access_key
	 * @internal
	 * This must be called right after an instance of option type has been created
	 * and was added to the registered array, so it is available through
	 * fw()->backend->option_type($this->get_type())
	 */
	final public function _call_init($access_key)
	{
		if ($access_key->get_key() !== 'fw_backend') {
			trigger_error('Method call not allowed', E_USER_ERROR);
		}

		if (method_exists($this, '_init')) {
			$this->_init();
		}
	}

	public function __construct() {

	}

	/**
	 * Fixes and prepare defaults
	 *
	 * @param string $id
	 * @param array  $option
	 * @param array  $data
	 * @return array
	 *
	 * @since 2.5.10
	 */
	public function prepare(&$id, &$option, &$data)
	{
		$data = array_merge(
			array(
				'id_prefix'   => self::get_default_id_prefix(),   // attribute id prefix
				'name_prefix' => self::get_default_name_prefix(), // attribute name prefix
			),
			$data
		);

		$defaults = $this->get_defaults();
		$merge_attr = !empty($option['attr']) && !empty($defaults['attr']);

		$option = array_merge($defaults, $option, array(
			'type' => $this->get_type()
		));

		if ($merge_attr) {
			$option['attr'] = array_merge($defaults['attr'], $option['attr']);
		}

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
	 * @param   array $data {value => $this->get_value_from_input()}
	 * @return string HTML
	 */
	final public function render($id, $option, $data = array())
	{
		$this->prepare($id, $option, $data);

		$this->enqueue_static($id, $option, $data);

		$html_attributes = array(
			'class' => 'fw-backend-option-descriptor',
			'data-fw-option-id' => $id,
			'data-fw-option-type' => $option['type']
		);

		$data_for_js = $this->_get_data_for_js($id, $option, $data);

		if ($data_for_js) {
			$html_attributes['data-fw-for-js'] = json_encode($data_for_js);
		}

		return fw_html_tag(
			'div',
			$html_attributes,
			$this->_render( $id, $this->load_callbacks( $option ), $data )
		);
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
		if ($this->static_enqueued) {
			return false;
		}

		if (
			!doing_action('admin_enqueue_scripts')
			&&
			!did_action('admin_enqueue_scripts')
		) {
			/**
			 * Do not wp_enqueue/register_...() because at this point not all handles has been registered
			 * and maybe they are used in dependencies in handles that are going to be enqueued.
			 * So as a result some handles will not be equeued because of not registered dependecies.
			 */
			return;
		}

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
					array('fw-events', 'qtip', 'fw-reactive-options'),
					fw()->manifest->get_version(),
					true
				);

				$option_types_static_enqueued = true;
			}
		}

		$this->prepare($id, $option, $data);

		$call_next_time = $this->_enqueue_static($id, $option, $data);

		$this->static_enqueued = !$call_next_time;

		return $call_next_time;
	}

	/**
	 * Extract correct value that will be stored in db or $option['value'] from raw form input value
	 * If input value is empty, will be returned $option['value']
	 * This method should be named get_db_value($form_input_value, $option)
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

		return $this->_get_value_from_input( $this->load_callbacks( $option ), $input_value);
	}

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with array returned from this method
	 *
	 * @param string Multikey. Since 2.6.9
	 * @return array
	 */
	final public function get_defaults($key = null)
	{
		$option = $this->_get_defaults();

		$option['type'] = $this->get_type();

		if (!array_key_exists('value', $option)) {
			FW_Flash_Messages::add(
				'fw-option-type-no-default-value',
				sprintf(__('Option type %s has no default value', 'fw'), $this->get_type()),
				'warning'
			);

			$option['value'] = array();
		}

		return is_string($key) ? fw_akg($key, $option) : $option;
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
	 * a general purpose 'label' => false | true from options.php
	 * @return bool | string
	 *
	 * @since 2.7.1
	 */
	public function _default_label($id, $option) {
		return fw_id_to_title($id);
	}

	/**
	 * Use this method to register a new option type
	 *
	 * @param string|FW_Option_Type $option_type_class
	 */
	final public static function register( $option_type_class, $type = null ) {
		fw()->backend->_register_option_type( self::get_access_key(), $option_type_class, $type );
	}

	/**
	 * If the option is composed of more options (added by user) which values are stored in database
	 * the option must call fw_db_option_storage_load() for each sub-option
	 * because some of them may have configured the save to be done in separate place (post meta, wp option, etc.)
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 * @return mixed
	 * @since 2.5.0
	 */
	final public function storage_load($id, array $option, $value, array $params = array()) {
		if ( // do not check !empty($option['fw-storage']) because this param can be set in option defaults
			$this->get_type() === $option['type']
			&&
			($option = array_merge($this->get_defaults(), $option))
		) {
			if (is_null($value)) {
				$value = fw()->backend->option_type($option['type'])->get_value_from_input($option, $value);
			}

			return $this->_storage_load($id, $option, $value, $params);
		} else {
			return $value;
		}
	}

	/**
	 * @see storage_load()
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 * @return mixed
	 * @since 2.5.0
	 * @internal
	 */
	protected function _storage_load($id, array $option, $value, array $params) {
		return fw_db_option_storage_load($id, $option, $value, $params);
	}

	/**
	 * If the option is composed of more options (added by user) which values are stored in database
	 * the option must call fw_db_option_storage_save() for each sub-option
	 * because some of them may have configured the save to be done in separate place (post meta, wp option, etc.)
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 * @return mixed
	 * @since 2.5.0
	 */
	final public function storage_save($id, array $option, $value, array $params = array()) {
		if ( // do not check !empty($option['fw-storage']) because this param can be set in option defaults
			$this->get_type() === $option['type']
			&&
			($option = array_merge($this->get_defaults(), $option))
		) {
			return $this->_storage_save($id, $option, $value, $params);
		} else {
			return $value;
		}
	}

	/**
	 * @see storage_save()
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 * @return mixed
	 * @since 2.5.0
	 * @internal
	 */
	protected function _storage_save($id, array $option, $value, array $params) {
		return fw_db_option_storage_save($id, $option, $value, $params);
	}

	private static function get_access_key() {
		if ( self::$access_key === null ) {
			self::$access_key = new FW_Access_Key( 'fw_option_type' );
		}

		return self::$access_key;
	}

	protected function load_callbacks( $data ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		return array_map( 'fw_call', $data );
	}
}
