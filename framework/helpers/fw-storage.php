<?php if (!defined('FW')) die('Forbidden');

// Process the `fw-storage` option parameter

/**
 * @param string $id
 * @param array $option
 * @param mixed $value
 * @param array $params
 *
 * @return mixed
 *
 * @since 2.5.0
 */
function fw_db_option_storage_save($id, array $option, $value, array $params = array()) {
	if (
		!empty($option['fw-storage'])
		&&
		($storage = is_array($option['fw-storage'])
			? $option['fw-storage']
			: array('type' => $option['fw-storage'])
		)
		&&
		!empty($storage['type'])
		&&
		($storage_type = fw_db_option_storage_type($storage['type']))
	) {
		$option['fw-storage'] = $storage;
	} else {
		return $value;
	}

	/** @var FW_Option_Storage_Type $storage_type */

	return $storage_type->save($id, $option, $value, $params);
}

/**
 * @param string $id
 * @param array $option
 * @param mixed $value
 * @param array $params
 *
 * @return mixed
 *
 * @since 2.5.0
 */
function fw_db_option_storage_load($id, array $option, $value, array $params = array()) {
	if (
		!empty($option['fw-storage'])
		&&
		($storage = is_array($option['fw-storage'])
			? $option['fw-storage']
			: array('type' => $option['fw-storage'])
		)
		&&
		!empty($storage['type'])
		&&
		($storage_type = fw_db_option_storage_type($storage['type']))
	) {
		// Fixes https://github.com/ThemeFuse/Unyson/issues/2265
		if (isset($params['customizer']) && is_customize_preview()) {
			/** @var WP_Customize_Manager $wp_customize */
			global $wp_customize;

			if (
				($setting = $wp_customize->get_setting($setting_id = 'fw_options[' . $id . ']'))
				&&
				!is_null($wp_customize->post_value($setting))
			) {
				// Use POST preview value
				return $value;
			}
		}

		$option['fw-storage'] = $storage;
	} else {
		return $value;
	}

	/** @var FW_Option_Storage_Type $storage_type */

	return $storage_type->load($id, $option, $value, $params);
}

/**
 * @param null|string $type
 * @return FW_Option_Storage_Type|FW_Option_Storage_Type[]|null
 * @since 2.5.0
 */
function fw_db_option_storage_type($type = null) {
	static $types = null;

	if (is_null($types)) {
		$access_key = new FW_Access_Key('fw:option-storage-register');
		$register = new _FW_Option_Storage_Type_Register($access_key->get_key());

		{
			$register->register(new FW_Option_Storage_Type_WP_Option());
			$register->register(new FW_Option_Storage_Type_Post_Meta());
			$register->register(new FW_Option_Storage_Type_Term_Meta());
		}

		do_action('fw:option-storage-types:register', $register);

		$types = $register->_get_types($access_key);
	}

	if (empty($type)) {
		return $types;
	} elseif (isset($types[$type])) {
		return $types[$type];
	} else {
		return null;
	}
}
