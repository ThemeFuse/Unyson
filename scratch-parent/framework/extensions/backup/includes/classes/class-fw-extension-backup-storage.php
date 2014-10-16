<?php if (!defined('FW')) die('Forbidden');

abstract class FW_Extension_Backup_Storage extends FW_Extension implements FW_Backup_Interface_Storage
{
    public function get_storage_options()
    {
        return array();
    }

	public function set_storage_options($values)
	{
	}

	public function before_save_storage_options($values)
	{
		return $values;
	}

	/**
     * @internal
     */
    public function _init()
    {
    }

	/**
	 * @return FW_Extension_Backup
	 */
	protected function backup()
	{
		return $this->get_parent();
	}
}
