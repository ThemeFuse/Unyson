<?php if (!defined('FW')) die('Forbidden');

class _FW_Customizer_Control_Option_Wrapper extends WP_Customize_Control {
	public function render_content() {
		fw()->backend->_set_default_render_design('customizer');
		?>
		<div class="fw-backend-customizer-option">
			<input class="fw-backend-customizer-option-input" type="hidden" <?php $this->link() ?> />
			<div class="fw-backend-customizer-option-inner fw-force-xs">
				<?php
				echo fw()->backend->render_options(
					array($this->id => $this->setting->get_fw_option()),
					array($this->id => $this->value()),
					array(),
					'customizer'
				);
				?>
			</div>
		</div>
		<?php
		fw()->backend->_set_default_render_design();
	}
}
