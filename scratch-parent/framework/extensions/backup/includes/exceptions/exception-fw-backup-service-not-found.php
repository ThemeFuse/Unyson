<?php if (!defined('FW')) die('Forbidden');

/**
 * Class FW_Backup_Exception_Service_Not_Found
 *
 * Throws when service was not found
 */
class FW_Backup_Exception_Service_Not_Found extends FW_Backup_Exception_Service
{
	public function __construct($service_id, $code = 0, Exception $previous = null)
	{
		$message = sprintf(__('Service not found: %s', 'fw'), $service_id);

		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			parent::__construct($message,$code,$previous);
		}
		else {
			parent::__construct($message,$code);
		}
	}
}
