<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Analytics extends FW_Extension {

	private $name = 'Analytics';

	/**
	 * @internal
	 */
	public function _init() {
		if ( is_admin() ) {
			$this->name = __( 'Analytics', 'fw' );
			$this->admin_actions();
		}
	}

	/**
	 * @internal
	 */
	private function admin_actions() {
		add_action( 'admin_init', array( $this, '_admin_action_add_options' ) );
		add_action( 'admin_init', array( $this, '_admin_action_register_option' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_action_register_styles' ) );
	}

	/**
	 * @internal
	 */
	public function _admin_action_register_styles() {
		$current_screen = array(
			'only'  => array(
				array( 'base'   => 'options-general' )
			)
		);

		if ( fw_current_screen_match( $current_screen ) ) {
			wp_enqueue_style(
				$this->get_name() . '-styles',
				$this->get_declared_URI( '/static/css/style.css' ),
				array(),
				fw()->theme->manifest->get_version()
			);
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_register_option() {
		register_setting( 'general', 'fw-ext-' . $this->get_name() . '-code' );
	}

	/**
	 * @internal
	 */
	public function _admin_action_add_options() {
		add_settings_field(
			'fw-ext-' . $this->get_name() . '-code',
			$this->name,
			array( $this, '_get_form' ),
			'general'
		);
	}

	/**
	 * @internal
	 */
	public function _get_form() {
		echo '<textarea
		name="fw-ext-' . $this->get_name() . '-code"
		id="fw-ext-' . $this->get_name() . '-code"
		rows="5" cols="30">' . get_option( 'fw-ext-' . $this->get_name() . '-code' ) . '</textarea>';
	}

	public function get_analytics_code() {
		return get_option( 'fw-ext-' . $this->get_name() . '-code' );
	}
}