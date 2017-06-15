<?php if (!defined('FW')) die('Forbidden');

class _FW_Customizer_Setting_Option extends WP_Customize_Setting {
	/**
	 * @var array
	 * This is sent in args and set in parent construct
	 */
	protected $fw_option = array();

	/**
	 * @var string
	 * This is sent in args and set in parent construct
	 */
	protected $fw_option_id;

	public function get_fw_option() {
		return $this->fw_option;
	}

	public function sanitize($value) {
		if ( is_array( $value ) ) {
			return null;
		}
		   
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
	 * {@inheritdoc}
	 */
	public function value() {
		return fw_db_option_storage_load(
			$this->fw_option_id,
			$this->fw_option,
			parent::value(),
			array('customizer' => true)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function update( $value ) {
		return parent::update(
			fw_db_option_storage_save(
				$this->fw_option_id,
				$this->fw_option,
				$value,
				array('customizer' => true)
			)
		);
	}
}
