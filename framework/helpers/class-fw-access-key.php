<?php if (!defined('FW')) die('Forbidden');

/**
 * Used in public callbacks to allow call only from known caller
 * 
 * For e.g. Inside some class is created an instance of FW_Access_Key with unique key 'whatever',
 * so nobody else can create another instance with same key, only that class owns that unique key.
 * Some public callback (function or method) that wants to allow to be called only by that class,
 * sets an requirement that some parameter should be an instance on FW_Access_Key and its key should be 'whatever'
 * 
 * function my_function(FW_Access_Key $key, $another_parameter) {
 *  if ($key->get_key() !== 'whatever') {
 *      trigger_error('Call denied', E_USER_ERROR);
 *  }
 * 
 *  //...
 * }
 */
final class FW_Access_Key
{
	private static $created_keys = array();

	private $key;
	
	final public function get_key()
	{
		return $this->key;
	}

	/**
	 * @param string $unique_key unique
	 */
	final public function __construct($unique_key)
	{
		if (isset(self::$created_keys[$unique_key])) {
			trigger_error('Key "'. $unique_key .'" already defined', E_USER_ERROR);
		}
		
		self::$created_keys[$unique_key] = true;
		
		$this->key = $unique_key;
	}
}
