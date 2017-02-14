<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command_Theme_Settings extends FW_CLI_Command {

	/**
	 * Theme Settings values.
	 *
	 * ## OPTIONS
	 *
	 * [<path>]
	 * : Path to settings value
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: visual
	 * options:
	 *	- visual
	 *	- json
	 *	- serialize
	 *
	 * ## EXAMPLES
	 *
	 *	# Full theme settings structure.
	 *	$ wp unyson theme-settings get --format=json
	 *
	 *	# A part of theme settings.
	 *	$ wp unyson theme-settings get link/color
	 *
	 */
	public function get($params, $args) {
		$format = (isset($args['format'])) ? $args['format'] : 'visual';
		$path = (isset($params[0])) ? $params[0] : false;

		if ($path) {
			$data = fw_get_db_settings_option($path);
		} else {
			$data = fw_get_db_settings_option();
		}

		if ('visual' === $format) {
			print_r($data);
		}

		if ('json' === $format) {
			WP_CLI::line( json_encode($data) );
		}

		if ('serialize' === $format) {
			WP_CLI::line( serialize($data) );
		}
	}

	/**
	 * Set value in Theme Settings.
	 *
	 * ## OPTIONS
	 *
	 * <path>
	 * : Path to settings value.
	 *
	 * <value>
	 * : Value.
	 *
	 * [--array]
	 * : Decode JSON string and save as array.
	 *
	 * ## EXAMPLES
	 *
	 *	# Set new value.
	 *	$ wp unyson theme-settings set link/color/value #00000
	 *
	 *	# Output
	 *
	 *	array(
	 *		link => array(
	 *			color => array(
	 *				value => #00000
	 *			)
	 *		)
	 *	)
	 *
	 *	# Save value as array.
	 *	$ wp unyson theme-settings set logo/img '{"link": "logo.png"}' --array
	 *
	 *	# Output
	 *
	 *	array(
	 *		logo => array(
	 *			img => array(
	 *				link => logo.png
	 *			)
	 *		)
	 *	)
	 *
	 *	# Save json as string.
	 *	$ wp unyson theme-settings set user/info '{"age": "20"}'
	 *
	 *	array(
	 *		user => array(
	 *			info => '{"age": "20"}'
	 *		)
	 *	)
	 *
	 */
	public function set($params, $args) {
		$is_array = (isset($args['array'])) ? true : false;
		$path = (isset($params[0])) ? $params[0] : false;
		$value = (isset($params[1])) ? $params[1] : false;

		if( false !== $path && false !== $value ) {
			if ($is_array) {
				$data = json_decode($value, true);
				fw_set_db_settings_option($path, $data);
			} else {
				fw_set_db_settings_option($path, $value);
			}

			WP_CLI::success("Done!");
		}

		WP_CLI::error("Missed path or value.");
	}

}

WP_CLI::add_command( 'unyson theme-settings', 'FW_CLI_Command_Theme_Settings' );