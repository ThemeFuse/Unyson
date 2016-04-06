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
		// Get the callback that corresponds to the setting type.
		switch( $this->type ) {
			case 'theme_mod' :
				$function = 'get_theme_mod';
				break;
			case 'option' :
				$function = 'get_option';
				break;
			default :

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
				return apply_filters( 'customize_value_' . $this->id_data[ 'base' ], $this );
		}

		// Handle non-array value
		if ( empty( $this->id_data[ 'keys' ] ) )
			return $function( $this->id_data[ 'base' ], $this->default );

		// Handle array-based value
		$values = $function( $this->id_data[ 'base' ] );
		return $this->multidimensional_get( $values, $this->id_data[ 'keys' ], $this->default );
	}
}
