<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Styling extends FW_Extension {

	/** @var FW_Form */
	private $form;

	/** @var  array */
	private static $user_options = array();

	/**
	 * @internal
	 */
	public function _init() {

		self::$user_options = $this->get_settings_options();

		if (!empty(self::$user_options)) {
			if ( is_admin() ) {
				$this->form = new FW_Form( $this->get_name(), array(
					'render'   => array( $this, '_form_render' ),
					'validate' => array( $this, '_form_validate' ),
					'save'     => array( $this, '_form_save' )
				) );

				$this->add_admin_actions();
			} else {
				$this->add_theme_actions();
			}
		}

	}

	protected function add_admin_actions() {
		add_action( 'fw_' . $this->get_name() . '_form_save', array( $this, '_admin_action_generate_css' ) );

		add_action( 'admin_menu', array( $this, '_admin_action_add_menu' ), 20 );
	}

	protected function add_theme_actions() {
		add_action( 'wp_head', array( $this, '_theme_action_print_css' ), 99 );
	}

	/**
	 * @internal
	 */
	public function _admin_action_add_menu() {

		$default_args  = array(
			'page_title' => __( 'Styling', 'fw' ),
			'menu_title' => __( 'Styling', 'fw' ),
			'menu_slug'  => 'fw-styling'
		);
		$filtered_args = apply_filters( 'fw_ext_' . $this->get_name() . '_page_' . $default_args['menu_slug'], $default_args );
		$args          = array_merge( $default_args, $filtered_args );
		add_theme_page(
			$args['page_title'],
			$args['menu_title'],
			'manage_options',
			$args['menu_slug'],
			array( $this, 'render_styling_settings_page' )
		);
		return true;
	}

	public function render_styling_settings_page() {
		echo '<div class="wrap" style="opacity:0">';
		echo '<h2>' . __( 'Styling', 'fw' ) . '</h2><p></p>';
		$this->form->render();
		echo '</div>';
	}

	/**
	 * @internal
	 */
	public function _form_render( $data ) {

		$this->add_admin_static();

		$options = array(
			array(
				'custom_css' => array(
					'title'   => false,
					'type'    => 'box',
					'options' => self::$user_options
				)
			)
		);

		$values = FW_Request::POST( FW_Option_Type::get_default_name_prefix(), fw_get_db_extension_data( $this->get_name(), 'options' ) );

		echo fw()->backend->render_options( $options, $values );

		$data['submit']['html'] = '<button class="button-primary button-large">' . __( 'Save', 'fw' ) . '</button>';

		unset( $options );

		return $data;
	}

	/**
	 * @internal
	 * @param $errors
	 * @return array
	 */
	public function _form_validate( $errors ) {
		if (!current_user_can('manage_options')) {
			$errors[] = __('You have no permission to change Styling options', 'fw');
		}

		return $errors;
	}

	/**
	 * @internal
	 */
	public function _form_save( $data ) {
		fw_set_db_extension_data( $this->get_name(), 'options', fw_get_options_values_from_input( $this->get_settings_options() ) );

		do_action( 'fw_' . $this->get_name() . '_form_save' );

		$data['redirect'] = fw_current_url();

		return $data;
	}

	/*
	 * @internal
	 */
	private function add_admin_static() {
		wp_enqueue_style(
			'fw-extension-' . $this->get_name() . '-styles',
			$this->get_declared_URI( '/static/css/styles.css' ),
			array(),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-extension-' . $this->get_name(),
			$this->get_declared_URI('/static/js/scripts.js'),
			array('jquery'),
			fw()->manifest->get_version(),
			true
		);
	}

	/**
	 * Triggers when the framework settings are saved,
	 * it generates css from the styling settings and stores it
	 * @internal
	 */
	public function _admin_action_generate_css() {
		$theme_options         = fw_extract_only_options( $this->get_settings_options() );
		$saved_data            = fw_get_db_extension_data( $this->get_name(), 'options' );
		$css_for_style_options = FW_Styling_Css_Generator::get_css( $theme_options, $saved_data );
		fw_set_db_extension_data( $this->get_name(), 'css', $css_for_style_options );
	}

	/**
	 * Prints the css generated from the styling settings
	 * @internal
	 */
	public function _theme_action_print_css() {
		$css = fw_get_db_extension_data( $this->get_name(), 'css' );
		if ( ! empty( $css ) ) {
			echo $css;
		}
	}
}
