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
	 */
	protected static $forms = array();

	/**
	 * The id of the submitted form id
	 * @var string
	 */
	protected static $submitted_id;

	/**
	 * Hidden input name that stores the form id
	 * @var string
	 */
	protected static $id_input_name = 'fwf';

	/**
	 * Form id
	 * @var string
	 */
	protected $id;

	/**
	 * Html attributes for <form> tag
	 * @var array
	 */
	protected $attr;

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
	 */
	protected $is_submitted;

	/**
	 * @var bool
	 */
	protected $validate_and_save_called = false;

	protected $callbacks = array(
		'render'   => false,
		'validate' => false,
		'save'     => false
	);

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
		if ( isset( self::$forms[ $id ] ) ) {
			trigger_error( sprintf( __( 'Form with id "%s" was already defined', 'fw' ), $id ), E_USER_ERROR );
		}

		$this->id = $id;

		self::$forms[ $this->id ] =& $this;

		// prepare $this->attr
		{
			if ( ! isset( $data['attr'] ) || ! is_array( $data['attr'] ) ) {
				$data['attr'] = array();
			}

			$data['attr']['data-fw-form-id'] = $this->id;

			/** @deprecated */
			$data['attr']['class'] = 'fw_form_' . $this->id;

			if ( isset( $data['attr']['method'] ) ) {
				$data['attr']['method'] = strtolower( $data['attr']['method'] );

				$data['attr']['method'] = in_array( $data['attr']['method'], array( 'get', 'post' ) )
					? $data['attr']['method']
					: 'post';
			} else {
				$data['attr']['method'] = 'post';
			}

			if ( ! isset( $data['attr']['action'] ) ) {
				$data['attr']['action'] = '';
			}

			$this->attr = $data['attr'];
		}

		// prepare $this->callbacks
		{
			$this->callbacks = array(
				'render'   => empty( $data['render'] ) ? false : $data['render'],
				'validate' => empty( $data['validate'] ) ? false : $data['validate'],
				'save'     => empty( $data['save'] ) ? false : $data['save'],
			);
		}

		if ( did_action( 'wp_loaded' ) ) {
			// in case if form instance was created after action
			$this->_validate_and_save();
		} else {
			// attach to an action before 'send_headers' action, to be able to do redirects
			add_action( 'wp_loaded', array( $this, '_validate_and_save' ), 101 );
		}
	}

	protected function validate() {
		if ( is_array( $this->errors ) ) {
			trigger_error( __METHOD__ . ' already called', E_USER_WARNING );

			return;
		}

		/**
		 * Errors array {'input[name]' => 'Error message'}
		 */
		$errors = array();

		/**
		 * Call validate callback
		 *
		 * Callback must 'manually' extract input values from $_POST (or $_GET)
		 */
		if ( $this->callbacks['validate'] ) {
			$errors = call_user_func_array( $this->callbacks['validate'], array( $errors ) );

			if ( ! is_array( $errors ) ) {

				$errors = array();
			}
		}

		/**
		 * check nonce
		 */
		if ( $this->attr['method'] == 'post' ) {
			$nonce_name = '_nonce_' . md5( $this->id );

			if ( ! isset( $_REQUEST[ $nonce_name ] ) || wp_verify_nonce( $_REQUEST[ $nonce_name ],
					'submit_fwf' ) === false
			) {
				$errors[ $nonce_name ] = __( 'Nonce verification failed', 'fw' );
			}
		}

		$this->errors = $errors;
	}

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
		if ( $this->callbacks['save'] ) {
			$data = call_user_func_array( $this->callbacks['save'], array( $save_data ) );

			if ( ! is_array( $data ) ) {
				// fix if returned wrong data from callback
				$data = $save_data;
			}

			$save_data = $data;

			unset( $data );
		}

		if ( ! $this->is_ajax() ) {
			if ( isset( $save_data['redirect'] ) ) {
				wp_redirect( $save_data['redirect'] );
				exit;
			}
		}

		return $save_data;
	}

	protected function is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * If current form was submitted, validate and save it
	 *
	 * Note: This callback can abort script execution if save does redirect
	 *
	 * @return bool|null
	 * @internal
	 */
	public function _validate_and_save() {
		if ( $this->validate_and_save_called ) {
			trigger_error( __METHOD__ . ' already called', E_USER_WARNING );

			return null;
		} else {
			$this->validate_and_save_called = true;
		}

		if ( ! $this->is_submitted() ) {
			return null;
		}

		$this->validate();

		if ( $this->is_ajax() ) {
			$json_data = array();

			if ( $this->is_valid() ) {
				$json_data['save_data'] = $this->save();
			} else {
				$json_data['errors'] = $this->get_errors();
			}

			/**
			 * Transform flash messages structure from
			 * array( 'type' => array( 'message_id' => array(...) ) )
			 * to
			 * array( 'type' => array( 'message_id' => 'Message' ) )
			 */
			{
				$flash_messages = array();

				foreach (FW_Flash_Messages::_get_messages(true) as $type => $messages) {
					$flash_messages[$type] = array();

					foreach ($messages as $id => $message_data) {
						$flash_messages[$type][$id] = $message_data['message'];
					}
				}

				$json_data['flash_messages'] = $flash_messages;
			}

			/**
			 * Important!
			 * We can't send form html in response:
			 *
			 * ob_start();
			 * $this->render();
			 * $json_data['html'] = ob_get_clean();
			 *
			 * because the render() method is not called within this class
			 * but by the code that created and owns the $form,
			 * and it's usually called with some custom data $this->render(array(...))
			 * that it's impossible to know here which data is that.
			 * If we will call $this->render(); without data, this may throw errors because
			 * the render callback may expect some custom data.
			 * Also it may be called or not, depending on the owner code inner logic.
			 *
			 * The only way to get the latest form html on ajax submit
			 * is to make a new ajax GET to current page and extract form html from the response.
			 */

			if ( $this->is_valid() ) {
				wp_send_json_success($json_data);
			} else {
				wp_send_json_error($json_data);
			}
		} else {
			if ( ! $this->is_valid() ) {
				return false;
			}

			$this->save();
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get html attribute(s)
	 *
	 * @param null|string $name
	 *
	 * @return array|string
	 */
	public function attr( $name = null ) {
		if ( $name ) {
			return isset( $this->attr[ $name ] ) ? $this->attr[ $name ] : null;
		} else {
			return $this->attr;
		}
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
			'data' => $data,
			'attr' => $this->attr,
		);

		unset( $data );

		if ( $this->callbacks['render'] ) {
			ob_start();

			$data = call_user_func_array( $this->callbacks['render'], array( $render_data, $this ) );

			$html = ob_get_clean();

			if ( empty( $data ) ) {
				// fix if returned wrong data from callback
				$data = $render_data;
			}

			$render_data = $data;

			unset( $data );
		}

		echo '<form '. fw_attr_to_html( $render_data['attr'] ) .' >';

		echo fw_html_tag('input', array(
			'type'  => 'hidden',
			'name'  => self::$id_input_name,
			'value' => $this->id,
		));

		if ( $render_data['attr']['method'] == 'post' ) {
			wp_nonce_field( 'submit_fwf', '_nonce_' . md5( $this->id ) );
		}

		if ( ! empty( $render_data['attr']['action'] ) && $render_data['attr']['method'] == 'get' ) {
			/**
			 * Add query vars from the action attribute url to hidden inputs to not loose them
			 */

			parse_str( parse_url( $render_data['attr']['action'], PHP_URL_QUERY ), $query_vars );

			if ( ! empty( $query_vars ) ) {
				foreach ( $query_vars as $var_name => $var_value ) {
					echo fw_html_tag('input', array(
						'type'  => 'hidden',
						'name'  => $var_name,
						'value' => $var_value,
					));
				}
			}
		}

		echo $html;

		// In filter can be defined custom html for submit button
		if ( isset( $render_data['submit']['html'] ) ) {
			echo $render_data['submit']['html'];
		} else {
			echo fw_html_tag('input', array(
				'type' => 'submit',
				'value' => $render_data['submit']['value']
			));
		}

		echo '</form>';
	}

	/**
	 * If now is a submit of this form
	 * @return bool
	 */
	public function is_submitted() {
		if ( is_null( $this->is_submitted ) ) {
			$method = strtoupper( $this->attr( 'method' ) );

			if ( $method === 'POST' ) {
				$this->is_submitted = (
					isset( $_POST[ self::$id_input_name ] )
					&&
					FW_Request::POST( self::$id_input_name ) === $this->id
				);

			} elseif ( $method === 'GET' ) {
				$this->is_submitted = (
					isset( $_GET[ self::$id_input_name ] )
					&&
					FW_Request::GET( self::$id_input_name ) === $this->id
				);
			} else {
				$this->is_submitted = false;
			}
		}

		return $this->is_submitted;
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		if ( ! $this->validate_and_save_called ) {
			trigger_error( __METHOD__ . ' called before validation', E_USER_WARNING );

			return null;
		}

		return empty( $this->errors );
	}

	/**
	 * Get validation errors
	 * @return array
	 */
	public function get_errors() {
		if ( ! $this->validate_and_save_called ) {
			trigger_error( __METHOD__ . ' called before validation', E_USER_WARNING );

			return array( '~' => true );
		}

		$this->errors_accessed = true;

		return $this->errors;
	}

	public function errors_accessed()
	{
		return $this->errors_accessed;
	}

	/**
	 * Get submitted form instance (or false if no form is currently submitted)
	 * @return FW_Form|false
	 */
	public static function get_submitted() {
		if ( is_null( self::$submitted_id ) ) {
			// method called first time, search for submitted form
			do {
				foreach ( self::$forms as $form ) {
					if ( $form->is_submitted() ) {
						self::$submitted_id = $form->get_id();
						break 2;
					}
				}

				self::$submitted_id = false;
			} while ( false );
		}

		if ( is_string( self::$submitted_id ) ) {
			return self::$forms[ self::$submitted_id ];
		} else {
			return false;
		}
	}
}

if ( is_admin() ) {
	/**
	 * Display form errors in admin side
	 * @internal
	 */
	function _action_show_fw_form_errors_in_admin() {
		$form = FW_Form::get_submitted();

		if ( ! $form || $form->is_valid() ) {
			return;
		}

		foreach ( $form->get_errors() as $input_name => $error_message ) {
			FW_Flash_Messages::add( 'fw-form-admin-' . $input_name, $error_message, 'error' );
		}
	}
	add_action( 'wp_loaded', '_action_show_fw_form_errors_in_admin', 111 );
} else {
	/**
	 * Detect if form errors was not displayed in frontend then display them with default design
	 * Do nothing if the theme already displayed the errors
	 * @internal
	 */
	function _action_show_fw_form_errors_in_frontend() {
		$form = FW_Form::get_submitted();

		if ( ! $form || $form->is_valid() ) {
			return;
		}

		if ( $form->errors_accessed() ) {
			// already displayed
			return;
		}

		foreach ($form->get_errors() as $input_name => $error_message) {
			FW_Flash_Messages::add(
				'fw-form-error-'. $input_name,
				$error_message,
				'error'
			);
		}
	}
	add_action( 'wp_footer', '_action_show_fw_form_errors_in_frontend',
		/**
		 * Use priority later than the default 10.
		 * In docs (to customize the error messages) will be easier to explain
		 * to use just add_action('wp_footer', ...) and not bother about priority
		 */
		11
	);
}
