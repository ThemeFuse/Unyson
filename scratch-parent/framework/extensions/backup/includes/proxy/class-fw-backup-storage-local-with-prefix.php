<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Storage_Local_With_Prefix implements FW_Backup_Interface_Storage
{
	private $storage_local;
	private $name_prefix;

	public function __construct(FW_Extension_Backup_Storage_Local $storage_local, $name_prefix = 'backup')
	{
		$this->storage_local = $storage_local;
		$this->name_prefix = $name_prefix;
	}

	public function get_name()
	{
		return $this->storage_local->get_name();
	}

	public function get_title($context = null)
	{
		return $this->storage_local->get_title($context);
	}

	public function get_storage_options()
	{
		return $this->storage_local->get_storage_options();
	}

	public function set_storage_options($values)
	{
		$this->storage_local->set_storage_options($values);
	}

	public function before_save_storage_options($values)
	{
		$this->storage_local->before_save_storage_options($values);
	}

	public function ping(FW_Backup_Interface_Feedback $feedback)
	{
		$this->storage_local->ping($feedback);
	}

	public function move($file, FW_Backup_Interface_Feedback $feedback)
	{
		return $this->storage_local->move($file, $feedback, $this->name_prefix);
	}

	public function fetch($storage_file, FW_Backup_Interface_Feedback $feedback)
	{
		return $this->storage_local->fetch($storage_file, $feedback);
	}

	public function remove($storage_file, FW_Backup_Interface_Feedback $feedback)
	{
		$this->storage_local->remove($storage_file, $feedback);
	}

	public function download($storage_file)
	{
		$this->storage_local->download($storage_file);
	}
}
