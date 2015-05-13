<?php if (!defined('FW')) die('Forbidden');

class _FW_Customizer_Control_Option_Wrapper extends WP_Customize_Control {
	private $fw_option = array();

	public function __construct( $manager, $id, array $args, array $data ) {
		parent::__construct( $manager, $id, $args);

		$this->fw_option = $data['fw_option'];
	}

	public function render_content() {
		fw()->backend->_set_default_render_design('customizer');
		?>
		<div class="fw-backend-customizer-option">
			<input class="fw-backend-customizer-option-input" type="hidden" <?php $this->link() ?> />
			<div class="fw-backend-customizer-option-inner fw-force-xs">
				<?php
				echo fw()->backend->render_options(
					array($this->id => $this->fw_option),
					array(
						$this->id => $this->value()
					),
					array(),
					'customizer'
				);
				?>
			</div>
		</div>
		<?php
		fw()->backend->_set_default_render_design();
	}

	public function setting_sanitize_callback($input) {
		$input = json_decode($input, true);

		if (is_null($input)) {
			return null;
		}

		$POST = array();
		foreach ($input as $var) {
			fw_aks(
				fw_html_attr_name_to_array_multi_key($var['name']),
				$var['value'],
				$POST
			);
		}

		$value = fw_get_options_values_from_input(
			array($this->id => $this->fw_option),
			fw_akg(FW_Option_Type::get_default_name_prefix(), $POST)
		);

		$value = array_pop($value);

		return $value;
	}
}
