<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Work with $_SESSION
 *
 * Advantages: Do not session_start() on every refresh, but only when it is accessed
 */
class FW_Session {
	private static function start_session() {
		if ( apply_filters( 'fw_use_sessions', true ) && ! session_id() ) {
			session_start();
		}
	}

	public static function get( $key, $default_value = null ) {
		if ( ! apply_filters( 'fw_use_sessions', true ) ) {
			return array();
		}

		self::start_session();

		return fw_akg( $key, $_SESSION, $default_value );
	}

	public static function set( $key, $value ) {
		self::start_session();

		fw_aks( $key, $value, $_SESSION );
	}

	public static function del( $key ) {
		self::start_session();

		fw_aku( $key, $_SESSION );
	}
}
