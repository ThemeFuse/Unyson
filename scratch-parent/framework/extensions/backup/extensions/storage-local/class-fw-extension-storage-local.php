<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Storage_Local extends FW_Extension implements FW_Backup_Interface_Storage_Factory
{
	// FW_Backup_Interface_Storage_Factory

	public function create_storage()
	{
		/**
		 * @var FW_Extension_Backup $backup
		 */
		$backup = $this->get_parent();

		return new FW_Backup_Storage_Local($backup, $backup->service('shared.feedback'));
	}

	// FW_Extension

	/**
	 * @internal
	 */
	public function _init()
	{
	}
}
