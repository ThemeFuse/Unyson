<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Widget_Area extends FW_Shortcode{

	public static function get_sidebars() {

		global $wp_registered_sidebars;
		$result = array();

		foreach ( $wp_registered_sidebars as $sidebar ) {
			$result[ $sidebar['id'] ] = $sidebar['name'];
		}

		return $result;
	}

}