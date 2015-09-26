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
	 * @param array $values Options values (in db format, returned from get_value_from_input())
	 * @param array $data
	 * @param bool   Return true to call this method again on the next enqueue,
	 *               if you have some functionality in it that depends on option parameters.
	 *               By default this method is called only once for performance reasons.
	 */
	abstract protected function _enqueue_static($id, $option, $values, $data);

	/**
	 * Generate html
	 * @param array $containers array('option_id' => array(), ...)
	 *              - Options arrays are merged with _get_defaults()
	 *              - All options are 100% only current container type, no need to check if ($option['type'] === $this->get_type())
	 *              - Are sent multiple options instead of one, because tabs (and maybe other feature containers)
	 *                can't be rendered separately (only as a collection).
	 *                Instead of having render_option() for those that can be rendered separately,
	 *                and render_options() for those like tabs, was decided to make a compromise,
	 *                only one method that always will receive an array of options,
	 *                instead of two methods when things may become confuse and complicated.
	 * @param array $values Options values (in db format, returned from get_value_from_input())
	 * @param array $data {id_prefix => '...', name_prefix => '...'}
	 * @return string HTML
	 * @internal
	 */
	abstract protected function _render($containers, $values, $data);

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
	 *     'type' => '...',
	 *     ...
	 *     'options' => array(...),
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
	 * If options enqueue is called before the `admin_enqueue_scripts` action, that is wrong,
	 * we must not execute wp_enqueue_...() before that action, because it is not recommended to do that
	 * also some styles/scripts can have in dependencies some styles/script that are not yet registered
	 * so they will not be enqueued
	 *
	 * @var array
	 */
	private static $too_early_enqueue = array();

	final public static function _init_static($access_key) {
		if ( $access_key->get_key() !== 'fw_backend' ) {
			trigger_error( 'Method call not allowed', E_USER_ERROR );
		}

		add_action('admin_enqueue_scripts', array(__CLASS__, '_action_enqueue_too_early_static'), 11);
	}

	final public static function _action_enqueue_too_early_static() {
		if (empty(self::$too_early_enqueue)) {
			return;
		}

		$options = self::$too_early_enqueue;

		self::$too_early_enqueue = array();

		foreach ($options as $id => $opt) {
			fw()->backend->container_type($opt['type'])->enqueue_static($id, $opt['option'], $opt['values'], $opt['data']);
		}
	}

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
	private function prepare($id, &$option, &$data)
	{
		$data = array_merge(
			array(
				'id_prefix'   => FW_Option_Type::get_default_id_prefix(),   // attribute id prefix
				'name_prefix' => FW_Option_Type::get_default_name_prefix(), // attribute name prefix
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

		if (!isset($option['title'])) {
			$option['title'] = fw_id_to_title($id);
		}

		$option['attr']['class'] = 'fw-container fw-container-type-'. $option['type'] .(
			isset($option['attr']['class'])
				? ' '. $option['attr']['class']
				: ''
			);
	}

	/**
	 * Generate html
	 * @param   array $options array('container_id' => array(...container option...))
	 * @param   array $values Options values (in db format, returned from get_value_from_input())
	 * @param   array $data {'id_prefix' => '...', 'name_prefix' => '...'}
	 * @return string HTML
	 */
	final public function render($options, $values = array(), $data = array())
	{
		$containers = array();

		foreach ($options as $id => &$option) {
			if (
				!isset($option['options'])
				||
				!isset($option['type'])
				||
				$option['type'] !== $this->get_type()
			) {
				continue;
			}

			$this->prepare($id, $option, $data);

			$this->enqueue_static($id, $option, $data);

			$containers[$id] = &$option;
		}

		return $this->_render($containers, $values, $data);
	}

	/**
	 * Enqueue container type scripts and styles
	 *
	 * All parameters are optional and will be populated with defaults
	 *
	 * @param string $id
	 * @param array $option
	 * @param array $values Options values (in db format, returned from get_value_from_input())
	 * @param array $data
	 * @return bool
	 */
	final public function enqueue_static($id = '', $option = array(), $values = array(), $data = array())
	{
		if (!doing_action('admin_enqueue_scripts') && !did_action('admin_enqueue_scripts')) {
			self::$too_early_enqueue[$id] = array(
				'option' => $option,
				'values' => $values,
				'data' => $data,
			);
			// fixme: maybe add warning?
			return;
		}

		if ($this->static_enqueued) {
			return false;
		}

		$this->prepare($id, $option, $data);

		$call_next_time = $this->_enqueue_static($id, $option, $values, $data);

		$this->static_enqueued = !$call_next_time;

		return $call_next_time;
	}

	/**
	 * Default option array
	 *
	 * @return array
	 *         'type'  => '...'
	 *         'title' => '...'
	 *         'attr'  => array(...)
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
			$registration_access_key = new FW_Access_Key('fw_container_type');
		}

		fw()->backend->_register_container_type($registration_access_key, $container_type_class);
	}
}