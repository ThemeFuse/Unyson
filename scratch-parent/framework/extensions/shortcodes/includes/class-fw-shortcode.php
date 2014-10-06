<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode
{
	private $tag;
	private $path;
	private $uri;
	private $options;
	private $config;

	final public function __construct($args)
	{
		$this->tag  = $args['tag'];
		$this->path = $args['path'];
		$this->uri  = $args['uri'];

		$this->_init();
	}

	protected function _init()
	{
	}

	/**
	 * Gets the shortcodes' tag (id)
	 * @return string
	 */
	final public function get_tag()
	{
		return $this->tag;
	}

	/**
	 * Gets the path at which the shortcode is located
	 * @return string
	 */
	final public function get_path()
	{
		return $this->path;
	}

	/**
	 * Gets the uri at which the shortcode is located
	 * @return string
	 */
	final public function get_uri()
	{
		return $this->uri;
	}

	public function get_config($key = null)
	{
		if (!$this->config) {
			$config_path = $this->path . '/config.php';
			if (file_exists($config_path)) {
				$vars = fw_get_variables_from_file($config_path, array('cfg' => null));
				$this->config = $vars['cfg'];
			}
		}

		if (!is_array($this->config)) {
			return null;
		} else {
			return $key === null ? $this->config : fw_akg($key, $this->config);
		}
	}

	public function get_options()
	{
		if (!$this->options) {
			$options_path = $this->path . '/options.php';
			if (file_exists($options_path)) {
				$vars = fw_get_variables_from_file($options_path, array('options' => null));
				$this->options = $vars['options'];
			}
		}
		return $this->options;
	}

	/**
	 * Generates shortcode notation from array of attributes
	 *
	 * From a given array of attributes e.g.:
	 * array(
	 *     'width'  => 200,
	 *     'height' => 100
	 * )
	 * the method generates the corresponding shortcode notation:
	 * [$tag width="200" height="100"]
	 *
	 * @param $atts The array of attributes
	 * @return string The shortcode notation
	 */
	final public function generate_shortcode_notation($atts)
	{
		$attributes_str     = '';
		$json_encoded_keys  = array();
		foreach ($atts as $key => $value) {
			$corrected_key = str_replace('-', '_', $key);
			if (is_array($value)) {

				/*
				 * when an att is an array, we need to serialize it so that it can be
				 * a valid shortcode attribute (which are strings)
				 */
				$value = json_encode($value);

				/*
				 * keep track of what keys were encoded as json to decode them
				 * before sending to the shortcode_handler
				 */
				$json_encoded_keys[] = $corrected_key;
			}

			/*
			 * some characters break the wp shortcode parser
			 * (characters like [ "), so we encode to base64
			 * to get rid of them
			 */
			$attributes_str .= $corrected_key . '="' . base64_encode($value) . '" ';
		}
		$attributes_str  = substr($attributes_str, 0, -1);
		$attributes_str .= !empty($json_encoded_keys) ? ' _json_keys="' . base64_encode(json_encode($json_encoded_keys)) . '"' : '';

		$notation        = $attributes_str ? "[{$this->tag} {$attributes_str}]" : "[{$this->tag}]";
		return $notation;
	}

	final public function render($atts, $content = null, $tag = '')
	{
		$correct_atts = $atts;

		// the atts are empty when the shortcode does not have attributes
		if (!empty($atts)) {

			// decode the attributes from base64 | json (they were encoded when generating the shortcode notation)
			$decoded_atts = $this->decode_atts($atts);

			// remove unwanted attributes if such setting exists in config
			$correct_atts = $this->get_correct_atts($decoded_atts);
		}

		return $this->handle_shortcode($correct_atts, $content, $tag);
	}

	private function get_correct_atts($given_atts)
	{
		$default_atts = $this->get_config('default_atts');
		return $default_atts
				? shortcode_atts($default_atts, $given_atts)
				: $given_atts;
	}

	/**
	 * decodes the attributes that were encoded
	 * when generating the shortcode notation
	 */
	private function decode_atts($atts)
	{
		$decoded_atts = array();
		foreach ($atts as $key => $value) {
			$decoded_atts[$key] = base64_decode($value);
		}

		if (!empty($decoded_atts['_json_keys'])) {
			$json_keys = json_decode($decoded_atts['_json_keys']);
			foreach ($json_keys as $json_key) {
				if (isset($decoded_atts[$json_key])) {
					$decoded_atts[$json_key] = json_decode($decoded_atts[$json_key], true);
				}
			}
			unset($decoded_atts['_json_keys']);
		}

		return $decoded_atts;
	}

	protected function handle_shortcode($atts, $content, $tag)
	{
		$view_file = $this->path . '/views/view.php';
		if (!file_exists($view_file)) {
			trigger_error(
				sprintf(__('No default view (views/view.php) found for shortcode: %s', 'fw'), $tag),
				E_USER_ERROR
			);
		}

		$this->enqueue_static();
		return fw_render_view($view_file, array(
			'atts'    => $atts,
			'content' => $content,
			'tag'     => $tag
		));
	}

	private function enqueue_static()
	{
		$version = fw()->extensions->get('shortcodes')->manifest->get_version();

		$css_path = $this->path . '/static/css/';
		$css_uri  = $this->uri  . '/static/css/';
		if ($files = glob($css_path . '/*.css')) {
			foreach ($files as $key => $css_file_path) {
				$file_name = basename($css_file_path);
				wp_enqueue_style(
					"shortcode_{$this->tag}_css_$key",
					$css_uri . $file_name,
					array(),
					$version
				);
			}
		}

		$js_path = $this->path . '/static/js/';
		$js_uri  = $this->uri  . '/static/js/';
		if ($files = glob($js_path . '/*.js')) {
			foreach ($files as $key => $js_file_path) {
				$file_name = basename($js_file_path);
				wp_enqueue_script(
					"shortcode_{$this->tag}_js_$key",
					$js_uri . $file_name,
					array(),
					$version,
					true
				);
			}
		}
	}
}
