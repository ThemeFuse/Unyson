<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Class FW_Callback
 *
 * @since 2.6.14
 */
class FW_Callback {
	/**
	 * @var $callback string|array
	 */
	private $callback;

	/**
	 * @var array $args
	 */
	private $args;

	/**
	 * @var bool
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * FW_Callback constructor.
	 *
	 * @param string|array $callback Callback function
	 * @param array $args Callback arguments
	 * @param bool $cache Whenever you want to cache the function value after it's first call or not
	 * Recommend when the function call may require many resources or time (database requests) , or the value is small
	 * Not recommended using on very large values
	 *
	 */
	public function __construct( $callback, array $args = array(), $cache = true ) {
		$this->callback = $callback;
		$this->args     = $args;
		$this->cache    = (bool) $cache;
	}

	/**
	 * Return callback function
	 * @return array|string
	 */
	public function get_callback() {
		return $this->callback;
	}

	/**
	 * Return callback function arguments
	 * @return array
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * Execute callback function
	 * @return mixed
	 */
	public function execute() {
		if ( $this->cache ) {
			try {
				return FW_Cache::get( $this->get_id() );
			} catch ( FW_Cache_Not_Found_Exception $e ) {
				FW_Cache::set(
					$this->get_id(),
					$value = $this->get_value()
				);

				return $value;
			}
		} else {
			return $this->get_value();
		}
	}

	/**
	 * Whenever you want to clear the cached value or the function
	 */
	public function clear_cache() {
		FW_Cache::del( $this->get_id() );
	}

	/**
	 * Get raw callback value, ignoring the cache
	 * @return mixed
	 */
	protected function get_value() {
		return call_user_func_array( $this->callback, $this->args );
	}

	protected function get_id() {
		if ( ! is_string( $this->id ) ) {
			$this->id = 'fw-callback-' . md5( $this->serialize_callback() . serialize( $this->args ) );
		}

		return $this->id;
	}

	protected function serialize_callback() {
		//Closures cannot be serialized and at the moment do not have a solution
		//So the Closures will be replaced with a unique Id
		return ( $this->callback instanceof Closure )
			? uniqid( 'fw-callback-' )
			: (
			is_string( $this->callback )
				? $this->callback
				: serialize( $this->callback ) );
	}
}