<?php if (!defined('FW')) die('Forbidden');

interface FW_Backup_Interface_Storage_Factory
{
	/**
	 * @return FW_Backup_Interface_Storage
	 */
	public function create_storage();
}
