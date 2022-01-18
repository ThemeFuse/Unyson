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
	protected static function prepare_key($key)
	{
		return $key;
	}

	protected static function get_set_key($multikey = null, $set_value = null, &$value = '')
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
		return fw_stripslashes_deep_keys(
			$multikey === null
				? $_GET
				: fw_akg($multikey, $_GET, $default_value)
		);
	}

	public static function POST($multikey = null, $default_value = null)
	{
		return fw_stripslashes_deep_keys(
			$multikey === null
				? $_POST
				: fw_akg($multikey, $_POST, $default_value)
		);
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
			return self::get_set_key($multikey, $set_value, $_COOKIE);
		}
	}

	public static function REQUEST($multikey = null, $default_value = null)
	{
		return fw_stripslashes_deep_keys(
			$multikey === null
				? $_REQUEST
				: fw_akg($multikey, $_REQUEST, $default_value)
		);
	}
}
