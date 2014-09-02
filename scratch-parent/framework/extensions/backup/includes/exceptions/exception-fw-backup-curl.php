<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Exception_Curl extends FW_Backup_Exception
{
	public function __construct($errno, $code, $response)
	{
		$message = sprintf(__('curl error: %s', 'fw'), json_encode(array(
			'errno' => $errno,
			'code' => $code,
			'response' => $response
		)));

		parent::__construct($message);
	}
}
