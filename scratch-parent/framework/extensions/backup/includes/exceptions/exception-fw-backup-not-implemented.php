<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Exception_Not_Implemented extends FW_Backup_Exception
{
	public function __construct($message = 'Not Implemented', $code = 0, Exception $previous = null)
	{
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			parent::__construct($message,$code,$previous);
		}
		else {
			parent::__construct($message,$code);
		}
	}
}
