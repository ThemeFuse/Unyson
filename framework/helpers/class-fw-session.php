<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Work with $_SESSION
 *
 * Advantages: Do not session_start() on every refresh, but only when it is accessed
 */
class FW_Session {
	static $fw_session = array();

	private static function start_session() {
		if ( self::is_session() && ! session_id() ) {
			session_start();
		}
	}

	public static function get( $key, $default_value = null ) {
		self::start_session();

		return fw_akg( $key, self::get_session_var(), $default_value );
	}

	public static function set( $key, $value ) {
		self::start_session();

		$session = self::get_session_var();

		fw_aks( $key, $value, $session );
	}

	public static function del( $key ) {
		self::start_session();
		
		$sesseion = self::get_session_var();
		fw_aku( $key, $sesseion );
	}

	public static function is_session() {

		if ( ! function_exists( 'session_status' ) || PHP_SESSION_DISABLED == session_status() || ! isset( $_SESSION ) ) {
			return false;
		}

		return true;
	}

	public static function get_session_var() {

		if ( self::is_session() ) {
			return $_SESSION;
		}

		return self::$fw_session;
	}
}
