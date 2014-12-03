<?php if (!defined('FW')) die('Forbidden');

/**
 * Dynamic forms
 */
class FW_Form
{
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
	public function __construct($id, $data = array())
	{
		if (isset(self::$forms[$id])) {
			trigger_error(sprintf(__('Form with id "%s" was already defined', 'fw'), $id), E_USER_ERROR);
		}

		$this->id = $id;

		self::$forms[$this->id] =& $this;

		// prepare $this->attr
		{
			if (!isset($data['attr']) || !is_array($data['attr'])) {
				$data['attr'] = array();
			}

			$data['attr']['id'] = 'fw_form_'. $this->id;

			if (isset($data['attr']['method'])) {
				$data['attr']['method'] = strtolower($data['attr']['method']);

				$data['attr']['method'] = in_array($data['attr']['method'], array('get', 'post'))
					? $data['attr']['method']
					: 'post';
			} else {
				$data['attr']['method'] = 'post';
			}

			if (!isset($data['attr']['action'])) {
				$data['attr']['action'] = '';
			}

			$this->attr = $data['attr'];
		}

		// prepare $this->callbacks
		{
			$this->callbacks = array(
				'render'   => empty($data['render'])   ? false : $data['render'],
				'validate' => empty($data['validate']) ? false : $data['validate'],
				'save'     => empty($data['save'])     ? false : $data['save'],
			);
		}

		if (did_action('wp_loaded')) {
			// in case if form instance was created after action
			$this->_validate_and_save();
		} else {
			// attach to an action before 'send_headers' action, to be able to do redirects
			add_action('wp_loaded', array($this, '_validate_and_save'), 101);
		}
	}

	protected function validate()
	{
		if (is_array($this->errors)) {
			trigger_error(__METHOD__ .' already called', E_USER_WARNING);
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
		if ($this->callbacks['validate']) {
			$errors = call_user_func_array($this->callbacks['validate'], array($errors));

			if (!is_array($errors)) {

				$errors = array();
			}
		}

		/**
		 * check nonce
		 */
		if ($this->attr['method'] == 'post') {
			$nonce_name = '_nonce_'. md5($this->id);

			if (!isset($_REQUEST[$nonce_name]) || wp_verify_nonce($_REQUEST[$nonce_name], 'submit_fwf') === false) {
				$errors[$nonce_name] = __('Nonce verification failed', 'fw');
			}
		}

		$this->errors = $errors;
	}

	protected function save()
	{
		$save_data = array(
			// you can set here a url for redirect after save
			'redirect' => null
		);

		/**
		 * Call save callback
		 *
		 * Callback must 'manually' extract input values from $_POST (or $_GET)
		 */
		if ($this->callbacks['save']) {
			$data = call_user_func_array($this->callbacks['save'], array($save_data));

			if (!is_array($data)) {
				// fix if returned wrong data from callback
				$data = $save_data;
			}

			$save_data = $data;

			unset($data);
		}

		if (isset($save_data['redirect'])) {
			wp_redirect($save_data['redirect']);
			exit;
		}
	}

	/**
	 * If current form was submitted, validate and save it
	 *
	 * Note: This callback can abort script execution if save does redirect
	 *
	 * @return bool|null
	 * @internal
	 */
	public function _validate_and_save()
	{
		if ($this->validate_and_save_called) {
			trigger_error(__METHOD__ .' already called', E_USER_WARNING);
			return null;
		} else {
			$this->validate_and_save_called = true;
		}

		if (!$this->is_submitted()) {
			return;
		}

		$this->validate();

		if (!$this->is_valid()) {
			return false;
		}

		$this->save();

		return true;
	}

	/**
	 * @return string
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * Get html attribute(s)
	 *
	 * @param null|string $name
	 * @return array|string
	 */
	public function attr($name = null)
	{
		if ($name) {
			return isset($this->attr[$name]) ? $this->attr[$name] : null;
		} else {
			return $this->attr;
		}
	}

	/**
	 * Render form's html
	 */
	public function render($data = array())
	{
		?><form <?php echo fw_attr_to_html($this->attr) ?> ><?php

		if (!empty($this->attr['action']) && $this->attr['method'] == 'get') {
			/**
			 * Add query vars from action attribute url to hidden inputs to not loose them
			 * For cases when get_search_link() will return '.../?s=~',
			 *  the 's' will be lost after submit and no search page will be shown
			 */

			parse_str(parse_url($this->attr['action'], PHP_URL_QUERY), $query_vars);

			if (!empty($query_vars)) {
				foreach ($query_vars as $var_name => $var_value) {
					?><input type="hidden" name="<?php print esc_attr($var_name) ?>" value="<?php print fw_htmlspecialchars($var_value) ?>" /><?php
				}
			}
		}

		?><input type="hidden" name="<?php print self::$id_input_name; ?>" value="<?php print $this->id ?>" /><?php

		if ($this->attr['method'] == 'post') {
			wp_nonce_field('submit_fwf', '_nonce_'. md5($this->id));
		}

		$render_data = array(
			'submit' => array(
				'value' => __('Submit', 'fw'),
				/**
				 * you can set here custom submit button html
				 * and the 'value' parameter will not be used
				 */
				'html' => null,
			),
			'data' => $data,
			'attr' => $this->attr,
		);

		unset($data);

		if ($this->callbacks['render']) {
			$data = call_user_func_array($this->callbacks['render'], array($render_data));

			if (empty($data)) {
				// fix if returned wrong data from callback
				$data = $render_data;
			}

			$render_data = $data;

			unset($data);
		}

		// In filter can be defined custom html for submit button
		if (isset($render_data['submit']['html'])):
			print $render_data['submit']['html'];
		else:
			?><input type="submit" value="<?php print $render_data['submit']['value'] ?>"><?php
		endif;

		?></form><?php
	}

	/**
	 * If now is a submit of this form
	 * @return bool
	 */
	public function is_submitted()
	{
		if (is_null($this->is_submitted)) {
			$method = strtoupper($this->attr('method'));

			if ($method === 'POST') {
				$this->is_submitted = (
					isset($_POST[self::$id_input_name])
					&&
					FW_Request::POST(self::$id_input_name) === $this->id
				);
			} elseif ($method === 'GET') {
				$this->is_submitted = (
					isset($_GET[self::$id_input_name])
					&&
					FW_Request::GET(self::$id_input_name) === $this->id
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
	public function is_valid()
	{
		if (!$this->validate_and_save_called) {
			trigger_error(__METHOD__ .' called before validation', E_USER_WARNING);
			return null;
		}

		return empty($this->errors);
	}

	/**
	 * Get validation errors
	 * @return array
	 */
	public function get_errors()
	{
		if (!$this->validate_and_save_called) {
			trigger_error(__METHOD__ .' called before validation', E_USER_WARNING);
			return array('~' => true);
		}

		return $this->errors;
	}

	/**
	 * Get submitted form instance (or false if no form is currently submitted)
	 * @return FW_Form|false
	 */
	public static function get_submitted()
	{
		if (is_null(self::$submitted_id)) {
			// method called first time, search for submitted form
			do {
				foreach (self::$forms as $form) {
					if ($form->is_submitted()) {
						self::$submitted_id = $form->get_id();
						break 2;
					}
				}

				self::$submitted_id = false;
			} while(false);
		}

		if (is_string(self::$submitted_id)) {
			return self::$forms[ self::$submitted_id ];
		} else {
			return false;
		}
	}
}

if (is_admin()) {
	/**
	 * Display form errors in admin side
	 */
	function _action_show_fw_form_errors_in_admin() {
		$form = FW_Form::get_submitted();

		if (!$form || $form->is_valid()) {
			return;
		}

		foreach ($form->get_errors() as $input_name => $error_message) {
			FW_Flash_Messages::add('fw-form-admin-'. $input_name, $error_message, 'error');
		}
	}
	add_action('wp_loaded', '_action_show_fw_form_errors_in_admin', 111);
}