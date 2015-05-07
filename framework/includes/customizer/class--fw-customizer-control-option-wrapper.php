<?php if (!defined('FW')) die('Forbidden');

class _FW_Customizer_Control_Option_Wrapper extends WP_Customize_Control {
	public $fw_option = array();

	public function __construct( $manager, $id, array $args, array $data ) {
		parent::__construct( $manager, $id, $args);

		$this->fw_option = $data['fw_option'];
	}

	public function render_content() {
		?>
		<label>
		<?php if ( ! empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif;
		if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif; ?>
		</label>
		<div>
		<?php

		echo fw()->backend->option_type($this->fw_option['type'])->render(
			$this->id,
			$this->fw_option
		);

		?>
		</div>
		<?php
	}
}
