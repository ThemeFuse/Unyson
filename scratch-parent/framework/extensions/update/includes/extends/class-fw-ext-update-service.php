<?php if (!defined('FW')) die('Forbidden');

/**
 * Extend this class if you want to create a new update service
 */
abstract class FW_Ext_Update_Service extends FW_Extension
{
	/**
	 * Return latest version of the framework if this service supports framework update
	 *
	 * @param bool $force_check Check now, do not use cache
	 * @return string|false|WP_Error
	 *      false    Does not know how to work with extension.
	 *      WP_Error Knows how to work with it, but there is an error
	 *      string   Everything is ok, here is latest version
	 *
	 * @internal
	 */
	public function _get_framework_latest_version($force_check)
	{
		return false;
	}

	/**
	 * Download (and extract) framework files
	 *
	 * ! Work with global $wp_filesystem; Do not use base php filesystem functions
	 *
	 * @param $version Version to download
	 * @param string $wp_filesystem_download_directory Empty directory offered for download files in it
	 * @return string|false|WP_Error Path to WP Filesystem directory with downloaded (and extracted) files
	 *
	 * @internal
	 */
	public function _download_framework($version, $wp_filesystem_download_directory)
	{
		return false;
	}

	/**
	 * Return latest version of the theme if this service supports theme update
	 *
	 * @param bool $force_check Check now, do not use cache
	 * @return string|false|WP_Error
	 *      false    Does not know how to work with extension.
	 *      WP_Error Knows how to work with it, but there is an error
	 *      string   Everything is ok, here is latest version
	 *
	 * @internal
	 */
	public function _get_theme_latest_version($force_check)
	{
		return false;
	}

	/**
	 * Download (and extract) theme files
	 *
	 * ! Work with global $wp_filesystem; Do not use base php filesystem functions
	 *
	 * @param $version Version to download
	 * @param string $wp_filesystem_download_directory Empty directory offered for download files in it
	 * @return string|false|WP_Error Path to WP Filesystem directory with downloaded (and extracted) files
	 *
	 * @internal
	 */
	public function _download_theme($version, $wp_filesystem_download_directory)
	{
		return false;
	}

	/**
	 * Return latest version of the extension if this service supports extension update
	 *
	 * @param FW_Extension $extension
	 * @param bool $force_check Check now, do not use cache
	 * @return string|false|WP_Error
	 *      false    Does not know how to work with extension.
	 *      WP_Error Knows how to work with it, but there is an error
	 *      string   Everything is ok, here is latest version
	 *
	 * @internal
	 */
	public function _get_extension_latest_version(FW_Extension $extension, $force_check)
	{
		return false;
	}

	/**
	 * Download (and extract) extension
	 *
	 * ! Work with global $wp_filesystem; Do not use base php filesystem functions
	 *
	 * @param FW_Extension $extension
	 * @param $version Version to download
	 * @param string $wp_filesystem_download_directory Empty directory offered for download files in it
	 * @return string|false|WP_Error Path to WP Filesystem directory with downloaded (and extracted) files
	 *
	 * @internal
	 */
	public function _download_extension(FW_Extension $extension, $version, $wp_filesystem_download_directory)
	{
		return false;
	}
}
