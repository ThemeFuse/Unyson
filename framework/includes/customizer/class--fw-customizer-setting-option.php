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
	 * Get the root value for a setting, especially for multidimensional ones.
	 *
	 * @since 4.4.0
	 * @access protected
	 *
	 * @param mixed $default Value to return if root does not exist.
	 * @return mixed
	 */
	protected function get_root_value( $default = null ) {
		$id_base = $this->id_data['base'];
		if ( 'option' === $this->type ) {
			return get_option( $id_base, $default );
		} else if ( 'theme_mod' ) {
			return get_theme_mod( $id_base, $default );
		} else {
			/*
			 * Any WP_Customize_Setting subclass implementing aggregate multidimensional
			 * will need to override this method to obtain the data from the appropriate
			 * location.
			 */
			return $this;
		}
	}
}
