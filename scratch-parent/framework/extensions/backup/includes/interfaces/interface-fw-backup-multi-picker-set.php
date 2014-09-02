<?php if (!defined('FW')) die('Forbidden');

interface FW_Backup_Interface_Multi_Picker_Set
{
	/**
	 * @return array
	 */
	public function get_multi_picker_set();
	public function set_option_values($values);
}
