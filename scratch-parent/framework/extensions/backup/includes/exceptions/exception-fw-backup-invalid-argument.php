<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Exception_Invalid_Argument extends FW_Backup_Exception
{
	public function __construct($argument, $reason = null, $code = 0, Exception $previous = null)
	{
		$message = sprintf(__('Invalid argument: %s', 'fw'), $argument);
		if (!empty($reason)) {
			$message = "$message ($reason)";
		}

		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			parent::__construct($message,$code,$previous);
		}
		else {
			parent::__construct($message,$code);
		}
	}
}
