<?php if (!defined('FW')) die('Forbidden');

/**
 * WordPress automatically adds slashes to:
 * $_REQUEST
 * $_POST
 * $_GET
 * $_COOKIE
 *
 * For e.g.
 *
 * If value is simple, get value directly:
 * $foo = isset($_GET['bar']) && $_GET['bar'] == 'yes';
 *
 * If value can contain some user input and can have quotes or json from some option, then use this helper:
 * $foo = json_decode(FW_Request::POST('bar')); // json_decode($_POST('bar')) will not work if json will contain quotes
 *
 * You can test that problem.
 * Add somewhere this code:
    fw_print(array(
        $_GET['test'],
        json_decode($_GET['test']),
        FW_Request::GET('test'),
        json_decode(FW_Request::GET('test'))
    ));
 * and access: http://your-site.com/?test={'a':1}
 */
class FW_Request
{

	protected static $local_get = null;
	protected static $local_post = null;
	protected static $local_request = null;
	protected static $local_cookie = null;

	protected static function prepare_key($key)
	{
		return (get_magic_quotes_gpc() && is_string($key) ? addslashes($key) : $key);
	}

	protected static function get_set_key($multikey = null, $set_value = null, &$value)
	{
		$multikey = self::prepare_key($multikey);

		if ($set_value === null) { // get
			return fw_stripslashes_deep_keys($multikey === null ? $value : fw_akg($multikey, $value));
		} else { // set
			fw_aks($multikey, fw_addslashes_deep_keys($set_value), $value);
		}

		return '';
	}

	public static function GET($multikey = null, $default_value = null)
	{
		$work_with = (self::$local_get === null)? $_GET : self::$local_get;
		return fw_stripslashes_deep_keys(
			$multikey === null
				? $work_with
				: fw_akg($multikey, $work_with, $default_value)
		);
	}

	public static function mock_get($default_value = array())
	{
		self::$local_get = $default_value;
	}

	public static function POST($multikey = null, $default_value = null)
	{
		$work_with = (self::$local_post === null)? $_POST : self::$local_post;
		return fw_stripslashes_deep_keys(
			$multikey === null
				? $work_with
				: fw_akg($multikey, $work_with, $default_value)
		);
	}

	public static function mock_post($default_value = array())
	{
		self::$local_post = $default_value;
	}

	public static function COOKIE($multikey = null, $set_value = null, $expire = 0, $path = null)
	{
		if ($set_value !== null) {

			// transforms a string ( key1/key2/key3 => key1][key2][key3] )
			$multikey = str_replace('/', '][', $multikey) . ']';

			// removes the first closed square bracket ( key1][key2][key3] => key1[key2][key3] )
			$multikey = preg_replace('/\]/', '', $multikey, 1);

			return setcookie($multikey, $set_value, $expire, $path);
		} else {
			$work_with = (self::$local_cookie === null)? $_COOKIE : self::$local_cookie;
			return self::get_set_key($multikey, $set_value, $work_with);
		}
	}

	public static function mock_cookie($default_value = array())
	{
		self::$local_cookie = $default_value;
	}

	public static function REQUEST($multikey = null, $default_value = null)
	{
		$work_with = (self::$local_request === null)? $_REQUEST : self::$local_request;
		return fw_stripslashes_deep_keys(
			$multikey === null
				? $work_with
				: fw_akg($multikey, $work_with, $default_value)
		);
	}

	public static function mock_request($default_value = array())
	{
		self::$local_request = $default_value;
	}

	public static function restore_globals()
	{
		self::$local_get = null;
		self::$local_post = null;
		self::$local_request = null;
		self::$local_cookie = null;
	}
}