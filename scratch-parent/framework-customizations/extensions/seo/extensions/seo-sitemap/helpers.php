<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Titles&Meta extension helper file
 */

/**
 * Returns all search engines names that extension pings to.
 *
 * @param bool $array , if isset to true, returns the names as an array;
 *                     if isset to false, returns the names as a string, divided by $divider parameter
 * @param string $divider , defines the symbol that will divide the engines names
 *
 * @return array | string
 */
function fw_ext_seo_sitemap_get_search_engines_names( $array = true, $divider = ',' ) {
	$search_engines    = fw()->extensions->get( 'seo-sitemap' )->get_search_engines();
	$available_engines = fw()->extensions->get( 'seo-sitemap' )->get_config( 'search_engines' );

	if ( empty( $search_engines ) ) {
		if ( $array ) {
			array();
		} else {
			'';
		}
	}

	$names = array();

	foreach ( $search_engines as $id => $search_engine ) {
		if ( in_array( $id, $available_engines ) ) {
			array_push( $names, $search_engine['name'] );
		}
	}

	if ( $array ) {
		return $names;
	}

	$names = implode( $divider . ' ', $names );

	return $names;
}

/**
 * Return the home path
 * @return string
 */
function fw_ext_seo_sitemap_get_home_path() {
	if ( function_exists( "get_home_path" ) ) {
		$res = get_home_path();
	} else {
		$home = home_url();
		if ( $home != '' && $home != get_option( 'url' ) ) {
			$home_path = parse_url( $home );
			if ( isset( $home_path['path'] ) ) {
				$home_path = $home_path['path'];
				$root      = str_replace( $_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"] );
				$home_path = trailingslashit( $root . $home_path );
			} else {
				$home_path = ABSPATH;
			}

		} else {
			$home_path = ABSPATH;
		}

		$res = $home_path;
	}

	return $res;
}

/**
 * Returns sitemap URI address
 * @return string
 */
function fw_ext_seo_sitemap_get_stiemap_link() {
	return fw()->extensions->get( 'seo-sitemap' )->get_sitemap_uri();
}

/**
 * Updates sitemap
 */
function fw_ext_seo_sitemap_update() {
	fw()->extensions->get( 'seo-sitemap' )->update_sitemap();
}

/**
 * Checks if the file is writable and if is not tries to make it writable
 *
 * @param string $filename , the name of the file with the entire path
 *
 * @return bool
 */
function fw_ext_seo_sitemap_try_make_file_writable( $filename ) {
	if ( ! is_writable( $filename ) ) {
		if ( ! @chmod( $filename, 0666 ) ) {
			$pathtofilename = dirname( $filename );
			if ( ! is_writable( $pathtofilename ) ) {
				if ( ! @chmod( $pathtofilename, 0666 ) ) {
					return false;
				}
			}
		}
	}

	return true;
}