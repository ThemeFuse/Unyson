<?php if (!defined('FW')) die('Forbidden');

class _FW_Customizer_Control_Option_Wrapper extends WP_Customize_Control {
	public $fw_option = array();

	public function __construct( $manager, $id, array $args, array $data ) {
		parent::__construct( $manager, $id, $args);

		$this->fw_option = $data['fw_option'];
	}

	public function render_content() {
		?>
		<div>
			<input type="hidden" <?php $this->link() ?> />
			<div class="fw-force-xs">
			<?php

			echo fw()->backend->render_options(
				array(
					$this->id => $this->fw_option
				),
				array(
					// $this->id => $this->value() // fixme
				),
				array(),
				'customizer'
			);

			?>
			</div>
		</div>
		<?php
	}
}
