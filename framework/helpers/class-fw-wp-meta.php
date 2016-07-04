<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Features:
 * - Works with "multi keys"
 */
class FW_WP_Meta {
	/**
	 * @param string $meta_type
	 * @param int $object_id
	 * @param string $multi_key 'abc' or 'ab/c/def'
	 * @param array|string|int|bool $set_value
	 */
	public static function set( $meta_type, $object_id, $multi_key, $set_value ) {
		if ( empty( $multi_key ) ) {
			trigger_error( 'Key not specified', E_USER_WARNING );
			return;
		}

		$multi_key = explode( '/', $multi_key );
		$key       = array_shift( $multi_key );
		$multi_key = implode( '/', $multi_key );

		if ( empty( $multi_key ) && $multi_key !== '0' ) { // Replace entire meta
			fw_update_metadata( $meta_type, $object_id, $key, $set_value );
		} else { // Change only specified key
			$value = self::get( $meta_type, $object_id, $key, true );
			fw_aks( $multi_key, $set_value, $value );
			fw_update_metadata( $meta_type, $object_id, $key, $value );
		}
	}

	/**
	 * @param string $meta_type
	 * @param int $object_id
	 * @param string $multi_key 'abc' or 'ab/c/def'
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param bool|null $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
	 *
	 * @return mixed|null
	 */
	public static function get( $meta_type, $object_id, $multi_key, $default_value = null, $get_original_value = null ) {
		if ( ! is_null($get_original_value) ) {
			_doing_it_wrong(__FUNCTION__, '$get_original_value parameter was removed', 'Unyson 2.5.8');
		}

		if ( empty( $multi_key ) ) {
			trigger_error( 'Key not specified', E_USER_WARNING );
			return null;
		}

		$multi_key = explode( '/', $multi_key );
		$key       = array_shift( $multi_key );
		$multi_key = implode( '/', $multi_key );

		$value = get_metadata( $meta_type, $object_id, $key, true );

		if ( empty( $multi_key ) && $multi_key !== '0' ) {
			return $value;
		} else {
			return fw_akg($multi_key, $value, $default_value);
		}
	}
}
