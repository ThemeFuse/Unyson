<?php if (!defined('FW')) die('Forbidden');

/**
 * Backend option container
 */
abstract class FW_Container_Type
{
	/**
	 * Container's unique type, used in option array in 'type' key
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * Overwrite this method to enqueue scripts and styles
	 *
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 * @param bool   Return true to call this method again on the next enqueue,
	 *               if you have some functionality in it that depends on option parameters.
	 *               By default this method is called only once for performance reasons.
	 */
	abstract protected function _enqueue_static($id, $option, $data);

	/**
	 * Generate html
	 * @param string $id
	 * @param array $option Option array merged with _get_defaults()
	 * @param array $data {id_prefix => '...', name_prefix => '...'}
	 * @return string HTML
	 * @internal
	 */
	abstract protected function _render($id, $option, $data);

	/**
	 * Default option array
	 *
	 * This makes possible a container option array to have required only two parameters:
	 * array('type' => '...', 'options' => array(...))
	 * Other parameters are merged with the array returned by this method.
	 *
	 * @return array
	 *
	 * array(
	 *     'title' => 'Container default title', // optional, some containers may not have a title
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

	final public function __construct()
	{
		// does nothing at the moment, but maybe in the future will do something
	}

	/**
	 * @param FW_Access_Key $access_key
	 * @internal
	 * This must be called right after an instance of container type has been created
	 * and was added to the registered array
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
				'type' => $this->get_type(),
			)
		);

		if (!isset($option['attr'])) {
			$option['attr'] = array();
		}

		$option['attr']['class'] = 'fw-container fw-container-type-'. $option['type'] .(
			isset($option['attr']['class'])
				? ' '. $option['attr']['class']
				: ''
			);
	}

	/**
	 * Generate html
	 * @param  string $id
	 * @param   array $option
	 * @param   array $data {'id_prefix' => '...', 'name_prefix' => '...'} // fixme: 'values' - options values ?
	 * @return string HTML
	 */
	final public function render($id, $option, $data = array())
	{
		$this->prepare($id, $option, $data);

		$this->enqueue_static($id, $option, $data);

		return $this->_render($id, $option, $data);
	}

	/**
	 * Enqueue container type scripts and styles
	 *
	 * All parameters are optional and will be populated with defaults
	 *
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

		$this->prepare($id, $option, $data);

		$call_next_time = $this->_enqueue_static($id, $option, $data);

		$this->static_enqueued = !$call_next_time;

		return $call_next_time;
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

		return $option;
	}

	/**
	 * Use this method to register a new container type
	 * @param string|FW_Container_Type $container_type_class
	 */
	final public static function register($container_type_class) {
		static $registration_access_key = null;

		if ($registration_access_key === null) {
			$registration_access_key = new FW_Access_Key('register_container_type');
		}

		fw()->backend->_register_container_type($registration_access_key, $container_type_class);
	}
}