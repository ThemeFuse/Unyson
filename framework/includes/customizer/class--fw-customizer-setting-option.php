<?php if (!defined('FW')) die('Forbidden');

class _FW_Customizer_Setting_Option extends WP_Customize_Setting {
	/**
	 * @var array
	 * This is sent in args and set in parent construct
	 */
	protected $fw_option = array();

	public function get_fw_option() {
		return $this->fw_option;
	}

	public function sanitize($value) {
		$value = json_decode($value, true);

		if (is_null($value) || !is_array($value)) {
			return null;
		}

		$POST = array();

		foreach ($value as $var) {
			fw_aks(
				fw_html_attr_name_to_array_multi_key($var['name'], true),
				$var['value'],
				$POST
			);
		}

		$value = fw()->backend->option_type($this->fw_option['type'])->get_value_from_input(
			$this->fw_option,
			fw_akg(fw_html_attr_name_to_array_multi_key($this->id), $POST)
		);

		return $value;
	}
	
/**
	 * Fetch the value of the setting.
	 *
	 * @since 3.4.0
	 *
	 * @return mixed The value.
	 */
	public function value() {
		$id_base = $this->id_data['base'];
		$is_core_type = ( 'option' === $this->type || 'theme_mod' === $this->type );

		if ( ! $is_core_type && ! $this->is_multidimensional_aggregated ) {
			$value = $this->get_root_value( $this->default );

			/**
			 * Filter a Customize setting value not handled as a theme_mod or option.
			 *
			 * The dynamic portion of the hook name, `$this->id_date['base']`, refers to
			 * the base slug of the setting name.
			 *
			 * For settings handled as theme_mods or options, see those corresponding
			 * functions for available hooks.
			 *
			 * @since 3.4.0
			 *
			 * @param mixed $default The setting default value. Default empty.
			 */
			$value = apply_filters( "customize_value_{$id_base}", $this );
		} else if ( $this->is_multidimensional_aggregated ) {
			$root_value = self::$aggregated_multidimensionals[ $this->type ][ $id_base ]['root_value'];
			$value = $this->multidimensional_get( $root_value, $this->id_data['keys'], $this->default );
		} else {
			$value = $this->get_root_value( $this->default );
		}
		return $value;
	}
}
