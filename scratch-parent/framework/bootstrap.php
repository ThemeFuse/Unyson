<?php if (!defined('WP_DEBUG')) die('Forbidden');
/**
 * Loads the framework
 * Include this file in theme/functions.php
 */

/**
 * Tells that the framework is loaded.
 * You can check if this constant is defined to be sure the file is not accessed directly from browser.
 */
define('FW', true);

/** Convert to Unix style directory separators */
function fw_fix_path($path) {
	return str_replace(array('//', '\\'), array('/', '/'), $path);
}

/** Define useful constants */
{
	/** Child theme */
	{
		define('FW_CT', is_child_theme());

		if (FW_CT) {
			/** Full path to directory */
			define('FW_CT_DIR', fw_fix_path(get_stylesheet_directory()));

			/** Full path to directory with settings */
			define('FW_CT_CUSTOM_DIR', FW_CT_DIR .'/framework-customizations');

			/** Full path to directory with theme settings */
			define('FW_CT_THEME_DIR', FW_CT_CUSTOM_DIR .'/theme');

			/** Full path to directory with extensions */
			define('FW_CT_EXTENSIONS_DIR', FW_CT_CUSTOM_DIR .'/extensions');

			/** URI to directory */
			define('FW_CT_URI', get_stylesheet_directory_uri());

			/** URI to directory with settings */
			define('FW_CT_CUSTOM_URI', FW_CT_URI .'/framework-customizations');

			/** URI to directory with theme settings */
			define('FW_CT_THEME_URI', FW_CT_CUSTOM_URI .'/theme');

			/** URI to directory with extensions */
			define('FW_CT_EXTENSIONS_URI', FW_CT_CUSTOM_URI .'/extensions');
		}
	}

	/** Parent theme */
	{
		/** Full path to directory */
		define('FW_PT_DIR', fw_fix_path(get_template_directory()));

		/** Full path to directory with settings */
		define('FW_PT_CUSTOM_DIR', FW_PT_DIR .'/framework-customizations');

		/** Full path to directory with theme settings */
		define('FW_PT_THEME_DIR', FW_PT_CUSTOM_DIR .'/theme');

		/** Full path to directory with extensions */
		define('FW_PT_EXTENSIONS_DIR', FW_PT_CUSTOM_DIR .'/extensions');

		/** URI to directory */
		define('FW_PT_URI', get_template_directory_uri());

		/** URI to directory with settings */
		define('FW_PT_CUSTOM_URI', FW_PT_URI .'/framework-customizations');

		/** URI to directory with theme settings */
		define('FW_PT_THEME_URI', FW_PT_CUSTOM_URI .'/theme');

		/** URI to directory with extensions */
		define('FW_PT_EXTENSIONS_URI', FW_PT_CUSTOM_URI .'/extensions');
	}

	/** Framework */
	{
		/** Full path to directory */
		define('FW_DIR', FW_PT_DIR .'/framework');

		/** Full path to directory with extensions */
		define('FW_EXTENSIONS_DIR', FW_DIR .'/extensions');

		/** URI to directory */
		define('FW_URI', FW_PT_URI .'/framework');

		/** URI to directory with extensions */
		define('FW_EXTENSIONS_URI', FW_URI .'/extensions');
	}

	/** Cache */
	{
		define('FW_CACHE_DIR', fw_fix_path(WP_CONTENT_DIR) .'/cache/framework');

		define('FW_CACHE_URI', WP_CONTENT_URL .'/cache/framework');
	}
}

/**
 * Load core
 */
{
	require FW_DIR .'/core/Fw.php';

	/**
	 * @return _FW Framework instance
	 */
	function fw() {
		static $FW = null; // cache

		if ($FW === null) {
			$FW = new _Fw();
		}

		return $FW;
	}

	fw();
}

/**
 * Load helpers
 */
foreach (
	array(
		'post-meta',
		'class-fw-access-key',
		'class-fw-dumper',
		'general',
		'class-fw-wp-filesystem',
		'class-fw-cache',
		'class-fw-form',
		'class-fw-request',
		'class-fw-session',
		'class-fw-wp-option',
		'class-fw-wp-post-meta',
		'database',
		'class-fw-flash-messages',
		'class-fw-resize',
	)
	as $file
) {
	require FW_DIR .'/helpers/'. $file .'.php';
}

/**
 * Load (includes) other functionality
 */
foreach (
	array(
		'hooks',
		'option-types',
	)
	as $file
) {
	require FW_DIR .'/includes/'. $file .'.php';
}

/**
 * Init components
 */
foreach (fw() as $component_name => $component) {
	if ($component_name === 'manifest')
		continue;

	/** @var FW_Component $component */
	$component->_call_init();
}

/**
 * For Flash Message Helper:
 * just start session before headers sent
 * to prevent: Warning: session_start(): Cannot send session cookie - headers already sent if flash added to late
 */
FW_Session::get(-1);

do_action('fw_init');
