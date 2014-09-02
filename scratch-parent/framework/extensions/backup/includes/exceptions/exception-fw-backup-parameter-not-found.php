<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Exception_Parameter_Not_Found extends FW_Backup_Exception
{
	public function __construct($service_id, $code = 0, Exception $previous = null)
	{
		$message = sprintf(__('Parameter not found: %s', 'fw'), $service_id);

		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			parent::__construct($message,$code,$previous);
		}
		else {
			parent::__construct($message,$code);
		}
	}
}
