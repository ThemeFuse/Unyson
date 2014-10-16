<?php if (!defined('FW')) die('Forbidden');

/**
 * Collect paths of backups which are stored locally.
 */
class FW_Backup_Walker_Local_Backup_File
{
	private $result = array();

	public function get_result()
	{
		return $this->result;
	}

	public function walk(WP_Post $post)
	{
		if ($a = $this->backup()->get_backup_info($post->ID)) {
			$r = $a->get_storage_file();
			if ($r instanceof FW_Backup_Storage_File_Local) {
				$this->result[] = $r->get_path();
			}
		}
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
