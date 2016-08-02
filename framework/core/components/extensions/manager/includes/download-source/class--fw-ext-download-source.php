<?php if (! defined('FW')) { die('Forbidden'); }

/**
 * User to specify multiple download sources for an extension.
 * @since 2.5.12
 */
abstract class FW_Ext_Download_Source extends FW_Type
{
	/**
	 * Perform the actual download.
	 * It should download, by convention, a zip file which absolute path
	 * is $path.
	 *
	 * @param array $opts
	 * @param string $path Absolute file of the future ZIP file
	 */
	abstract public function download(array $opts, $zip_path);
}

