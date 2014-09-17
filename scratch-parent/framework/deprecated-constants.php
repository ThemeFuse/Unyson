<?php

/**
 * Deprecated constants
 * Do not use them
 * Will be removed in the next major release
 */

/**
 * Child theme constants
 *
 * Was replaced with
 * fw_get_stylesheet_customizations_directory()
 * fw_get_stylesheet_customizations_directory_uri()
 */
{
	/** @deprecated */
	define('FW_CT', is_child_theme());

	if (FW_CT) {
		/** @deprecated */
		define('FW_CT_DIR', fw_fix_path(get_stylesheet_directory()));
		/** @deprecated */
		define('FW_CT_CUSTOM_DIR', FW_CT_DIR .'/framework-customizations');
		/** @deprecated */
		define('FW_CT_THEME_DIR', FW_CT_CUSTOM_DIR .'/theme');
		/** @deprecated */
		define('FW_CT_EXTENSIONS_DIR', FW_CT_CUSTOM_DIR .'/extensions');
		/** @deprecated */
		define('FW_CT_URI', get_stylesheet_directory_uri());
		/** @deprecated */
		define('FW_CT_CUSTOM_URI', FW_CT_URI .'/framework-customizations');
		/** @deprecated */
		define('FW_CT_THEME_URI', FW_CT_CUSTOM_URI .'/theme');
		/** @deprecated */
		define('FW_CT_EXTENSIONS_URI', FW_CT_CUSTOM_URI .'/extensions');
	}
}

/**
 * Parent theme constants
 *
 * Was replaced with
 * fw_get_template_customizations_directory()
 * fw_get_template_customizations_directory_uri()
 */
{
	/** @deprecated */
	define('FW_PT_DIR', fw_fix_path(get_template_directory()));
	/** @deprecated */
	define('FW_PT_CUSTOM_DIR', FW_PT_DIR .'/framework-customizations');
	/** @deprecated */
	define('FW_PT_THEME_DIR', FW_PT_CUSTOM_DIR .'/theme');
	/** @deprecated */
	define('FW_PT_EXTENSIONS_DIR', FW_PT_CUSTOM_DIR .'/extensions');
	/** @deprecated */
	define('FW_PT_URI', get_template_directory_uri());
	/** @deprecated */
	define('FW_PT_CUSTOM_URI', FW_PT_URI .'/framework-customizations');
	/** @deprecated */
	define('FW_PT_THEME_URI', FW_PT_CUSTOM_URI .'/theme');
	/** @deprecated */
	define('FW_PT_EXTENSIONS_URI', FW_PT_CUSTOM_URI .'/extensions');
}

/**
 * Framework constants
 *
 * Was replaced with
 * fw_get_framework_directory()
 * fw_get_framework_directory_uri()
 */
{
	/** @deprecated */
	define('FW_DIR', FW_PT_DIR .'/framework');
	/** @deprecated */
	define('FW_EXTENSIONS_DIR', FW_DIR .'/extensions');
	/** @deprecated */
	define('FW_URI', FW_PT_URI .'/framework');
	/** @deprecated */
	define('FW_EXTENSIONS_URI', FW_URI .'/extensions');
}

/** Cache */
{
	/** @deprecated */
	define('FW_CACHE_DIR', fw_fix_path(WP_CONTENT_DIR) .'/cache/framework');
	/** @deprecated */
	define('FW_CACHE_URI', WP_CONTENT_URL .'/cache/framework');
}
