<?php if (!defined('FW')) die('Forbidden');

/**
 * Class FW_Backup_Exception_Service_Invalid_Interface
 *
 * Throws when service was found but is not implement specified interface
 */
class FW_Backup_Exception_Service_Invalid_Interface extends FW_Backup_Exception_Service
{
	public function __construct($service_id, $instanceof, $code = 0, Exception $previous = null)
	{
		$message = sprintf(__('Invalid Service Interface: %s, should be instance of %s', 'fw'), $service_id, $instanceof);

		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			parent::__construct($message,$code,$previous);
		}
		else {
			parent::__construct($message,$code);
		}
	}
}
