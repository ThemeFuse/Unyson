<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Reflection_Backup_Archive
{
	public function inspect_file($file)
	{
		$zip = new ZipArchive();

		if ($zip->open($file) !== true) {
			return array(
				'db' => false,
				'fs' => false,
				'auto-install' => false,
			);
		}

		$content = $this->inspect_zip($zip);

		$zip->unchangeAll();
		$zip->close();

		return $content;
	}

	public function inspect_zip(ZipArchive $zip)
	{
		$content = array(
			'db' => false,
			'fs' => false,
			'auto-install' => false,
		);

		if (count(array_filter(array('index.php', 'wp-config.php'), array($zip, 'statName'))) > 0) {
			$content['fs'] = true;
		}

		if ($zip->statName('database.sql') !== false) {
			$content['db'] = true;
		}

		if ($content['db'] || $content['fs']) {
			return $content;
		}

		$s =  '/auto-install/database.sql';
		for ($index = 0; $index < $zip->numFiles; ++$index) {
			$name = $zip->getNameIndex($index);
			if (substr($name, -strlen($s)) === $s) {
				$content['auto-install'] = true;
				break;
			}
		}

		return $content;
	}
}
