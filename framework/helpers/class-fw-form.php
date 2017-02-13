<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Dynamic forms
 */
class FW_Form {
	/**
	 * Store all form ids created with this class
	 * @var FW_Form[] {'form_id' => instance}
	 *
	 * @deprecated 2.6.15 Use FW_Form::get_forms()
	 */
	protected static $forms = array();

	/**
	 * The id of the submitted form id
	 * @var string
	 *
	 * @deprecated 2.6.15
	 */
	protected static $submitted_id;

	/**
	 * Hidden input name that stores the form id
	 * @var string
	 *
	 * @deprecated 2.6.15 Use self::get_form_id_name()
	 */
	protected static $id_input_name = 'fwf';

	/**
	 * Form id
	 * @var string
	 *
	 * @deprecated 2.6.15 Use $this->get_id()
	 */
	protected $id;

	/**
	 * Html attributes for <form> tag
	 * @var array
	 *
	 * @deprecated 2.6.15 Use $this->attr()
	 */
	protected $attr = array();

	/**
	 * Found validation errors
	 * @var array
	 */
	protected $errors;

	/**
	 * If the get_errors() method was called at leas once
	 * @var bool
	 */
	protected $errors_accessed = false;

	/**
	 * If current request is the submit of this form
	 * @var bool
	 *
	 * @deprecated 2.6.15 Use $this->is_submitted()
	 */
	protected $is_submitted;

	/**
	 * @var bool
	 *
	 * @deprecated 2.6.15
	 */
	protected $validate_and_save_called = false;

	/**
	 * @var array
	 *
	 * @deprecated 2.6.15 Use $this->get_callbacks()
	 */
	protected $callbacks = array(
		'render'   => false,
		'validate' => false,
		'save'     => false
	);

	private $request;

	/**
	 * @param string $id Unique
	 * @param array $data (optional)
	 * array(
	 *  'render'   => callback // The callback that will render the form's html
	 *  'validate' => callback // The callback that will validate user input
	 *  'save'     => callback // The callback that will save successfully validated user input
	 *  'attr'     => array()  // Custom <form ...> attributes
	 * )
	 */
	public function __construct( $id, $data = array() ) {
		try {
			self::get_form( $id );
			trigger_error( sprintf( __( 'Form with id "%s" was already defined', 'fw' ), $id ), E_USER_ERROR );

			return;
		} catch ( FW_Form_Not_Found_Exception $e ) {
		}

		$this
			// set id
			->set_id( $id )
			// prepare callbacks
			->set_callbacks( array(
				'render'   => fw_akg( 'render', $data, false ),
				'validate' => fw_akg( 'validate', $data, false ),
				'save'     => fw_akg( 'save', $data, false ),
			) )
			// prepare attributes
			->set_attr( (array) fw_akg( 'attr', $data, array() ) );

		self::$forms[ $this->get_id() ] =& $this;

		if ( did_action( 'wp_loaded' ) ) {
			// in case if form instance was created after action
			$this->_validate_and_save();
		} else {
			// attach to an action before 'send_headers' action, to be able to do redirects
			add_action( 'wp_loaded', array( $this, '_validate_and_save' ), 101 );
		}

		add_action( 'fw_form_display:before_form', array( $this, '_action_fw_form_show_errors' ) );
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get validation errors
	 * @return array
	 */
	public function get_errors() {
		if ( ! $this->validate_and_save_called ) {
			fw_print( debug_backtrace() );
			trigger_error( __METHOD__ . ' called before validation', E_USER_WARNING );

			return array( '~' => true );
		}

		$this->errors_accessed = true;

		return $this->_get_errors();
	}

	public function get_callbacks() {
		return $this->callbacks;
	}

	public function errors_accessed() {
		return $this->errors_accessed;
	}

	/**
	 * If current form was submitted, validate and save it
	 *
	 * Note: This callback can abort script execution if save does redirect
	 *
	 * @internal
	 */
	public function _validate_and_save() {

		if ( ! self::is_form_submitted( $this->get_id() ) || $this->validate_and_save_called ) {
			return;
		}

		$this->validate_and_save_called = true;

		try {
			$data = $this->submit( self::get_form_request( $this->get_id() ) );

			if ( $this->_is_ajax() ) {
				wp_send_json_success( array(
					'save_data'      => $data,
					'flash_messages' => self::collect_flash_messages(),
				) );
			}

			if ( ( $redirect = fw_akg( 'redirect', $data ) ) ) {
				wp_redirect( $redirect );
				exit;
			}
		} catch ( FW_Form_Invalid_Submission_Exception $e ) {
			if ( $this->_is_ajax() ) {
				wp_send_json_error( array(
					'errors'         => $this->get_errors(),
					'flash_messages' => self::collect_flash_messages()
				) );
			}
		}
	}

	/**
	 * @param FW_Form $form
	 *
	 * @internal
	 *
	 * You can overwrite it in case you do not need the errors to be shown for your form
	 */
	public function _action_fw_form_show_errors( $form ) {
		if (
			$form->get_id() != $this->get_id()
			// errors in admin side are displayed by a script at the end of this file
			|| is_admin()
			|| ! $form->is_submitted()
			|| $form->is_valid()
			|| $form->errors_accessed()
		) {

			return;
		}

		/**
		 * Use this action to customize errors display in your theme
		 */
		do_action( 'fw_form_display_errors_frontend', $form );

		$errors = $form->get_errors();

		if ( empty( $errors ) ) {
			return;
		}

		echo '<ul class="fw-form-errors">';

		foreach ( $errors as $input_name => $error_message ) {
			echo fw_html_tag(
				'li',
				array(
					'data-input-name' => $input_name,
				),
				$error_message
			);
		}

		echo '</ul>';
	}

	/**
	 * Get html attribute(s)
	 *
	 * @param null|string $name
	 *
	 * @return array|string
	 */
	public function attr( $name = null ) {
		return $name !== null
			? fw_akg( $name, $this->attr )
			: $this->attr;
	}

	/**
	 * Render form's html
	 *
	 * @param array $data
	 */
	public function render( $data = array() ) {
		$render_data = array(
			'submit' => array(
				'value' => __( 'Submit', 'fw' ),
				/**
				 * you can set here custom submit button html
				 * and the 'value' parameter will not be used
				 */
				'html'  => null,
			),
			'data'   => $data,
			'attr'   => $this->attr(),
		);

		$html = '';

		if ( $render_callback = fw_akg( 'render', $this->get_callbacks() ) ) {
			ob_start();

			$data = call_user_func_array( $render_callback, array( $render_data, $this ) );

			$html = ob_get_clean();

			if ( empty( $data ) ) {
				// fix if returned wrong data from callback
				$data = $render_data;
			}

			$render_data = $data;
		}

		do_action( 'fw_form_display:before_form', $this );

		// display form errors in frontend
		echo '<form ' . fw_attr_to_html( $render_data['attr'] ) . ' >';

		do_action( 'fw_form_display:before', $this );

		echo fw_html_tag( 'input',
			array(
				'type'  => 'hidden',
				'name'  => self::get_form_id_name(),
				'value' => $this->get_id(),
			) );

		wp_nonce_field( $this->get_nonce_action(), $this->get_nonce_name( $render_data ) );

		if ( ! empty( $render_data['attr']['action'] ) && $render_data['attr']['method'] == 'get' ) {
			/**
			 * Add query vars from the action attribute url to hidden inputs to not loose them
			 */

			parse_str( parse_url( $render_data['attr']['action'], PHP_URL_QUERY ), $query_vars );

			if ( ! empty( $query_vars ) ) {
				foreach ( $query_vars as $var_name => $var_value ) {
					echo fw_html_tag( 'input',
						array(
							'type'  => 'hidden',
							'name'  => $var_name,
							'value' => $var_value,
						) );
				}
			}
		}

		echo $html;

		// In filter can be defined custom html for submit button
		if ( isset( $render_data['submit']['html'] ) ) {
			echo $render_data['submit']['html'];
		} else {
			echo fw_html_tag( 'input',
				array(
					'type'  => 'submit',
					'value' => $render_data['submit']['value']
				) );
		}

		do_action( 'fw_form_display:after', $this );

		echo '</form>';

		do_action( 'fw_form_display:after_form', $this );
	}

	/**
	 * If now is a submit of this form
	 * @return bool
	 */
	public function is_submitted() {
		return $this->request !== null;
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		if ( ! $this->is_submitted() ) {
			return null;
		}

		return count( $this->_get_errors() ) == 0;
	}

	/**
	 * @param array $request
	 *
	 * @throws FW_Form_Invalid_Submission_Exception
	 *
	 * @return mixed
	 */
	public function submit( array $request = array() ) {
		$this->request = $request;
		//Updated the deprecated member for those that extended the class and use it in code
		$this->is_submitted = true;

		$errors = $this->validate();

		if ( ! empty( $errors ) ) {
			throw new FW_Form_Invalid_Submission_Exception( $errors );
		}

		return $this->save();
	}

	protected function get_default_attr() {
		return array(
			'data-fw-form-id' => $this->get_id(),
			'method'          => 'post',
			'action'          => fw_current_url(),
			'class'           => 'fw_form_' . $this->get_id()
		);
	}

	/**
	 * @param array $attr
	 *
	 * @return $this
	 */
	protected function set_attr( array $attr ) {
		$this->attr = array_merge( $this->get_default_attr(), $attr );

		return $this;
	}

	/**
	 * @param null $key
	 *
	 * @return array|mixed|null
	 *
	 * @since 2.6.15
	 */
	protected function get_request( $key = null ) {
		return $key === null ? (array) $this->request : fw_akg( $key, $this->request );
	}

	/**
	 * @return string|null
	 *
	 * @since 2.6.15
	 */
	protected function get_nonce() {
		return $this->get_request( $this->get_nonce_name() );
	}

	/**
	 * Returns forms errors without counting them as accessed
	 * @return array
	 */
	protected function _get_errors() {
		return $this->errors;
	}

	/**
	 * @return string
	 *
	 * @since 2.6.15
	 */
	protected function get_nonce_action() {
		return 'submit_fwf';
	}

	protected function check_nonce( $nonce ) {
		return wp_verify_nonce( $nonce, $this->get_nonce_action() );
	}

	/**
	 * @return array
	 */
	protected function validate() {
		/**
		 * Errors array {'input[name]' => 'Error message'}
		 */
		$errors = array();

		if ( ! $this->check_nonce( $this->get_nonce() ) ) {
			$errors[ $this->get_nonce_name() ] = __( 'Nonce verification failed', 'fw' );
		}

		/**
		 * Call validate callback
		 *
		 * Callback must 'manually' extract input values from $_POST (or $_GET)
		 */
		if ( ( $validate = fw_akg( 'validate', $this->get_callbacks() ) ) ) {
			$errors = (array) call_user_func( $validate, $errors );
		}

		return $this->set_errors( $errors )->_get_errors();
	}

	/**
	 * @return array|mixed
	 */
	protected function save() {
		$save_data = array(
			// you can set here a url for redirect after save
			'redirect' => null
		);

		/**
		 * Call save callback
		 *
		 * Callback must 'manually' extract input values from $_POST (or $_GET)
		 */
		if ( ( $save_callback = fw_akg( 'save', $this->get_callbacks() ) ) ) {
			$data = call_user_func_array( $save_callback, array( $save_data ) );

			if ( ! is_array( $data ) ) {
				// fix if returned wrong data from callback
				$data = $save_data;
			}

			$save_data = $data;

			unset( $data );
		}

		return $save_data;
	}

	/**
	 * @return bool
	 *
	 * @deprecated 2.6.15
	 */
	protected function is_ajax() {
		return self::_is_ajax();
	}

	protected function set_id( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * @param array $callbacks
	 *
	 * @return $this
	 */
	protected function set_callbacks( array $callbacks ) {
		$this->callbacks = $callbacks;

		return $this;
	}

	protected function set_errors( array $errors ) {
		$this->errors = $errors;

		return $this;
	}

	/**
	 * Some forms (like Forms extension frontend form) uses the same FW_Form instance for all sub-forms
	 * and they must be differentiated somehow.
	 * Fixes https://github.com/ThemeFuse/Unyson/issues/2033
	 *
	 * @param array $render_data
	 *
	 * @return string
	 * @since 2.6.6
	 */
	private function get_nonce_name( $render_data = array() ) {
		return '_nonce_' . md5( $this->id . apply_filters( 'fw:form:nonce-name-data', '', $this, $render_data ) );
	}

	/**
	 * @return FW_Form[]
	 *
	 * @since 2.6.15
	 */
	public static function get_forms() {
		return self::$forms;
	}

	/**
	 * @param $id
	 *
	 * @return FW_Form
	 * @throws FW_Form_Not_Found_Exception
	 *
	 * @since 2.6.15
	 */
	public static function get_form( $id ) {
		if ( ! isset( self::$forms[ $id ] ) ) {
			throw new FW_Form_Not_Found_Exception( "FW_Form $id was not defined" );
		}

		return self::$forms[ $id ];
	}

	/**
	 * Get submitted form instance (or false if no form is currently submitted)
	 * @return FW_Form|false
	 */
	public static function get_submitted() {
		foreach ( self::get_forms() as $id => $form ) {
			if ( self::is_form_submitted( $id ) ) {
				return $form;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 *
	 * @since 2.6.15
	 */
	public static function _is_ajax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		       ||
		       strtolower( fw_akg( 'HTTP_X_REQUESTED_WITH', $_SERVER ) ) == 'xmlhttprequest';
	}

	public static function get_form_request( $id ) {
		if ( FW_Request::POST( self::get_form_id_name() ) == $id ) {
			return FW_Request::POST();
		}

		if ( FW_Request::GET( self::get_form_id_name() ) == $id ) {
			return FW_Request::GET();
		}

		return null;
	}

	/**
	 * @return string
	 *
	 * @since 2.6.15
	 */
	protected static function get_form_id_name() {
		return 'fwf';
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 *
	 * @since 2.6.15
	 */
	protected static function is_form_submitted( $id ) {
		return self::get_form_request( $id ) !== null;
	}

	private static function collect_flash_messages() {
		$flash_messages = array();

		foreach ( FW_Flash_Messages::_get_messages( true ) as $type => $messages ) {
			$flash_messages[ $type ] = array();

			foreach ( $messages as $id => $message_data ) {
				$flash_messages[ $type ][ $id ] = $message_data['message'];
			}
		}

		return $flash_messages;
	}
}