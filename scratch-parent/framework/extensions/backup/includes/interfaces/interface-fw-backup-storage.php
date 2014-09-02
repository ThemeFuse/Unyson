<?php if (!defined('FW')) die('Forbidden');

interface FW_Backup_Interface_Storage
{
	/**
	 * @param string $context
	 * @return string
	 */
	public function get_title($context = null);

	/**
	 * @throw FW_Backup_Exception
	 */
	public function ping();

	/**
	 * @param $file
	 * @return FW_Backup_Interface_File
	 * @throws FW_Backup_Exception
	 */
	public function move($file);

	/**
	 * @param FW_Backup_Interface_File $backup_file
	 * @return string path to temporary file
	 * @throws FW_Backup_Exception
	 */
	public function fetch(FW_Backup_Interface_File $backup_file);

	/**
	 * @param FW_Backup_Interface_File $backup_file
	 * @throws FW_Backup_Exception
	 */
	public function remove(FW_Backup_Interface_File $backup_file);
}
