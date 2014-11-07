<?php if (!defined('FW')) die('Forbidden');
/**
 * Util functions
 */

/**
 * Recursively find a key's value in array
 *
 * @param string $keys 'a/b/c'
 * @param array|object $array_or_object
 * @param null|mixed $default_value
 * @param string $keys_delimiter
 * @return null|mixed
 */
function fw_akg($keys, &$array_or_object, $default_value = null, $keys_delimiter = '/') {
	if (!is_array($keys)) {
		$keys = explode( $keys_delimiter, (string) $keys );
	}

	$key_or_property = array_shift($keys);
	if ($key_or_property === null) {
		return $default_value;
	}

	$is_object = is_object($array_or_object);

	if ($is_object) {
		if (!property_exists($array_or_object, $key_or_property)) {
			return $default_value;
		}
	} else {
		if (!is_array($array_or_object) || !array_key_exists($key_or_property, $array_or_object)) {
			return $default_value;
		}
	}

	if (isset($keys[0])) { // not used count() for performance reasons
		if ($is_object) {
			return fw_akg($keys, $array_or_object->{$key_or_property}, $default_value);
		} else {
			return fw_akg($keys, $array_or_object[$key_or_property], $default_value);
		}
	} else {
		if ($is_object) {
			return $array_or_object->{$key_or_property};
		} else {
			return $array_or_object[$key_or_property];
		}
	}
}

/**
 * Set (or create if not exists) value for specified key in some array level
 *
 * @param string $keys 'a/b/c', or 'a/b/c/' equivalent to: $arr['a']['b']['c'][] = $val;
 * @param mixed $value
 * @param array|object $array_or_object
 * @param string $keys_delimiter
 */
function fw_aks($keys, $value, &$array_or_object, $keys_delimiter = '/') {
	if (!is_array($keys)) {
		$keys = explode($keys_delimiter, (string)$keys);
	}

	$key_or_property = array_shift($keys);
	if ($key_or_property === null) {
		return;
	}

	$is_object = is_object($array_or_object);

	if ($is_object) {
		if (!property_exists($array_or_object, $key_or_property)
			|| !(is_array($array_or_object->{$key_or_property}) || is_object($array_or_object->{$key_or_property}))
		) {
			if ($key_or_property === '') {
				// this happens when use 'empty keys' like: abc/d/e////i/j//foo/
				trigger_error('Cannot push value to object like in array ($arr[] = $val)', E_USER_WARNING);
			} else {
				$array_or_object->{$key_or_property} = array();
			}
		}
	} else {
		if (!is_array($array_or_object)) {
			$array_or_object = array();
		}

		if (!array_key_exists($key_or_property, $array_or_object) || !is_array($array_or_object[$key_or_property])) {
			if ($key_or_property === '') {
				// this happens when use 'empty keys' like: abc.d.e....i.j..foo.
				$array_or_object[] = array();

				// get auto created key (last)
				end($array_or_object);
				$key_or_property = key($array_or_object);
			} else {
				$array_or_object[$key_or_property] = array();
			}
		}
	}

	if (isset($keys[0])) { // not used count() for performance reasons
		if ($is_object) {
			fw_aks($keys, $value, $array_or_object->{$key_or_property});
		} else {
			fw_aks($keys, $value, $array_or_object[$key_or_property]);
		}
	} else {
		if ($is_object) {
			$array_or_object->{$key_or_property} = $value;
		} else {
			$array_or_object[$key_or_property] = $value;
		}
	}
}

/**
 * Unset specified key in some array level
 *
 * @param string $keys 'a/b/c' -> unset($arr['a']['b']['c']);
 * @param array|object $array_or_object
 * @param string $keys_delimiter
 */
function fw_aku($keys, &$array_or_object, $keys_delimiter = '/') {
	if (!is_array($keys)) {
		$keys = explode($keys_delimiter, (string)$keys);
	}

	$key_or_property = array_shift($keys);
	if ($key_or_property === null || $key_or_property === '') {
		return;
	}

	$is_object = is_object($array_or_object);

	if ($is_object) {
		if (!property_exists($array_or_object, $key_or_property)) {
			return;
		}
	} else {
		if (!is_array($array_or_object) || !array_key_exists($key_or_property, $array_or_object)) {
			return;
		}
	}

	if (isset($keys[0])) { // not used count() for performance reasons
		if ($is_object) {
			fw_aku($keys, $array_or_object->{$key_or_property});
		} else {
			fw_aku($keys, $array_or_object[$key_or_property]);
		}
	} else {
		if ($is_object) {
			unset($array_or_object->{$key_or_property});
		} else {
			unset($array_or_object[$key_or_property]);
		}

		return;
	}
}

/**
 * Generate random unique md5
 */
function fw_rand_md5() {
	return md5(time() .'-'. uniqid(rand(), true) .'-'. mt_rand(1, 1000));
}

/**
 * Return last + 1
 */
function fw_unique_increment() {
	static $i = null;

	if ($i === null)
		$i = mt_rand(0, 9370);

	return $i++;
}

/**
 * Nice displayed print_r alternative
 *
 * @param mixed $value Value to debug
 * @param bool  $die   Stop script after print
 */
function fw_print($value, $die = false) {
	static $first_time = true;

	if ($first_time) {
		ob_start();
		echo '<style type="text/css">
		div.fw_print_r {
			max-height: 500px;
			overflow-y: scroll;
			background: #111;
			margin: 10px 30px;
			padding: 0;
			border: 1px solid #F5F5F5;
		}

		div.fw_print_r pre {
			color: #47EE47;
			background: #111;
			text-shadow: 1px 1px 0 #000;
			font-family: Consolas, monospace;
			font-size: 12px;
			margin: 0;
			padding: 5px;
			display: block;
			line-height: 16px;
			text-align: left;
		}
		</style>';
		echo str_replace(array('  ', "\n"), '', ob_get_clean());
	}

	echo '<div class="fw_print_r"><pre>';

	echo fw_htmlspecialchars(FW_Dumper::dump($value));

	echo '</pre></div>';

	$first_time = false;

	if ($die) {
		die();
	}
}

/**
 * Generate html tag
 *
 * @param string $tag Tag name
 * @param array $attr Tag attributes
 * @param bool|string $end Append closing tag. Also accepts body content
 * @return string The tag's html
 */
function fw_html_tag($tag, $attr = array(), $end = false) {
	$html = '<'. $tag .' '. fw_attr_to_html($attr);

	if ($end === true) {
		# <script></script>
		$html .= '></'. $tag .'>';
	} else if ($end === false) {
		# <br/>
		$html .= '/>';
	} else {
		# <div>content</div>
		$html .= '>'. $end .'</'. $tag .'>';
	}

	return $html;
}

/**
 * Generate attributes string for html tag
 * @param array $attr_array array('href' => '/', 'title' => 'Test')
 * @return string 'href="/" title="Test"'
 */
function fw_attr_to_html(array $attr_array) {
	$html_attr = '';

	foreach ($attr_array as $attr_name => $attr_val) {
		if ($attr_val === false) {
			continue;
		}

		$html_attr .= $attr_name .'="'. fw_htmlspecialchars($attr_val) .'" ';
	}

	return $html_attr;
}

/**
 * Strip slashes from values, and from keys if magic_quotes_gpc = On
 */
function fw_stripslashes_deep_keys($value) {
	static $magic_quotes = null;
	if ($magic_quotes === null) {
		$magic_quotes = get_magic_quotes_gpc();
	}

	if (is_array($value)) {
		if ($magic_quotes) {
			$new_value = array();
			foreach ($value as $key => $val) {
				$new_value[ is_string($key) ? stripslashes($key) : $key ] = fw_stripslashes_deep_keys($val);
			}
			$value = $new_value;
			unset($new_value);
		} else {
			$value = array_map('fw_stripslashes_deep_keys', $value);
		}
	} elseif (is_object($value)) {
		$vars = get_object_vars($value);
		foreach ($vars as $key=>$data) {
			$value->{$key} = fw_stripslashes_deep_keys($data);
		}
	} elseif (is_string($value)) {
		$value = stripslashes($value);
	}

	return $value;
}

/**
 * Add slashes to values, and to keys if magic_quotes_gpc = On
 */
function fw_addslashes_deep_keys($value) {
	static $magic_quotes = null;
	if ($magic_quotes === null) {
		$magic_quotes = get_magic_quotes_gpc();
	}

	if (is_array($value)) {
		if ($magic_quotes) {
			$new_value = array();
			foreach ($value as $key=>$value) {
				$new_value[ is_string($key) ? addslashes($key) : $key ] = fw_addslashes_deep_keys($value);
			}
			$value = $new_value;
			unset($new_value);
		} else {
			$value = array_map('fw_addslashes_deep_keys', $value);
		}
	} elseif (is_object($value)) {
		$vars = get_object_vars($value);
		foreach ($vars as $key=>$data) {
			$value->{$key} = fw_addslashes_deep_keys($data);
		}
	} elseif (is_string($value)) {
		$value = addslashes($value);
	}

	return $value;
}

/**
 * Check if current screen pass/match give rules
 * @param array $rules Rules for current screen
 * @return bool
 */
function fw_current_screen_match(array $rules) {
	$available_options = array(
		'action'      => true,
		'base'        => true,
		'id'          => true,
		'is_network'  => true,
		'is_user'     => true,
		'parent_base' => true,
		'parent_file' => true,
		'post_type'   => true,
		'taxonomy'    => true,
	);

	if (empty($rules)) {
		return true;
	}

	$rules = array_merge(
		array(
			'exclude' => array(), // array of arrays or array with keys from $available_options
			'only'    => array(), // same as in 'exclude'
		),
		$rules
	);

	if (empty($rules['exclude']) && empty($rules['only'])) {
		return true;
	}

	global $current_screen;

	if (gettype($current_screen) != 'object') {
		return false;
	}

	// check if current screen passes the "only" rules
	do {
		$only = $rules['only'];

		if (empty($only)) {
			break;
		}

		if (!isset($only[0])) { // if not array of arrays
			$only = array($only);
		}

		$found_one = false;
		$counter  = 0;
		foreach ($only as $rule) {
			if (!count($rule)) {
				continue;
			}

			$match = true;

			foreach ($rule as $r_key => $r_val) {
				if (!isset($available_options[$r_key])) {
					continue;
				}

				if (gettype($r_val) != 'array') {
					$r_val = array($r_val);
				}

				$counter++;

				if (!in_array($current_screen->{$r_key}, $r_val)) {
					$match = false;
					break;
				}
			}

			if ($match) {
				$found_one = true;
				break;
			}
		}

		if (!$found_one && $counter) {
			return false;
		}
	} while(false);

	// check if current screen passes the "exclude" rules
	do {
		$exclude = $rules['exclude'];

		if (empty($exclude)) {
			break;
		}

		if (!isset($exclude[0])) { // if not array of arrays
			$exclude = array($exclude);
		}

		foreach ($exclude as $rule) {
			if (!count($rule)) {
				continue;
			}

			$match   = true;
			$counter = 0;

			foreach ($rule as $r_key => $r_val) {
				if (!isset($available_options[$r_key])) {
					continue;
				}

				if (gettype($r_val) != 'array') {
					$r_val = array($r_val);
				}

				$counter++;

				if (!in_array($current_screen->{$r_key}, $r_val)) {
					$match = false;
					break;
				}
			}

			if ($match && $counter) {
				return false;
			}
		}
	} while(false);

	return true;
}

/**
 * Search relative path in child then in parent theme directory and return URI
 *
 * @param  string $rel_path '/some/path_to_dir' or '/some/path_to_file.php'
 * @return string URI
 */
function fw_locate_theme_path_uri($rel_path) {
	if (is_child_theme() && file_exists(get_stylesheet_directory() . $rel_path)) {
		return get_stylesheet_directory_uri() . $rel_path;
	}

	if (file_exists(get_template_directory() . $rel_path)) {
		return get_template_directory_uri() . $rel_path;
	}

	return 'about:blank#theme-file-not-found:'. $rel_path;
}

/**
 * Search relative path in child then in parent theme directory and return full path
 *
 * @param  string $rel_path '/some/path_to_dir' or '/some/path_to_file.php'
 * @return string URI
 */
function fw_locate_theme_path($rel_path) {
	if (is_child_theme() && file_exists(get_stylesheet_directory() . $rel_path)) {
		return get_stylesheet_directory() . $rel_path;
	}

	if (file_exists(get_template_directory() . $rel_path)) {
		return get_template_directory() . $rel_path;
	}

	return false;
}

/**
 * Safe render a view and return html
 * In view will be accessible only passed variables
 * Use this function to not include files directly and to not give access to current context variables (like $this)
 * @param string $file_path
 * @param array $view_variables
 * @param bool $return In some cases, for memory saving reasons, you can disable the use of output buffering
 * @return string HTML
 */
function fw_render_view($file_path, $view_variables = array(), $return = true) {
	extract($view_variables, EXTR_REFS);

	unset($view_variables);

	if ($return) {
		ob_start();

		require $file_path;

		return ob_get_clean();
	} else {
		require $file_path;
	}
}

/**
 * Safe load variables from an file
 * Use this function to not include files directly and to not give access to current context variables (like $this)
 * @param string $file_path
 * @param array $variables array('variable_name' => 'default_value')
 * @return array
 */
function fw_get_variables_from_file($file_path, array $variables) {
	require $file_path;

	foreach ($variables as $variable_name => $default_value) {
		if (isset($$variable_name)) {
			$variables[$variable_name] = $$variable_name;
		}
	}

	return $variables;
}

/**
 * Use this function to not include files directly and to not give access to current context variables (like $this)
 * @param string $file_path
 */
function fw_include_file_isolated($file_path) {
	if (file_exists($file_path)) {
		include $file_path;
	}
}

/**
 * Extract only input options from array with: tabs, boxes, options
 * @param array $options
 * @param array $_recursion_options Do not use this parameter
 * @return array {option_id => option}
 */
function fw_extract_only_options(array $options, &$_recursion_options = array()) {
	static $recursion = null;

	if ($recursion === null) {
		$recursion = array(
			'level'  => 0,
			'result' => array(),
		);

		$_recursion_options =& $options;
	}

	foreach ($_recursion_options as $id => &$option) {
		if (isset($option['options'])) {
			// this is container with options
			$recursion['level']++;
			fw_extract_only_options(array(), $option['options']);
			$recursion['level']--;
		} elseif (isset($option['type']) && is_string($option['type'])) {
			$recursion['result'][$id] =& $option;
		} elseif (is_int($id) && is_array($option)) {
			// this is array with options
			$recursion['level']++;
			fw_extract_only_options(array(), $option);
			$recursion['level']--;
		}
	}

	if ($recursion['level'] == 0) {
		$result =& $recursion['result'];

		$recursion = null;

		return $result;
	}
}

/**
 * Collect correct options from first level on the array and group them
 * @param array $collected Will be filled with found correct options
 * @param array $options
 */
function fw_collect_first_level_options(&$collected, &$options) {
	if (empty($options))
		return;

	if (empty($collected)) {
		$collected['tabs']    = array();
		$collected['boxes']   = array();
		$collected['groups']  = array();

		$collected['options'] = array();

		$collected['groups_and_options'] = array();
	}

	foreach ($options as $option_id => &$option) {
		if (isset($option['options'])) {
			// this is container for other options

			switch ($option['type']) {
				case 'tab':
					$collected['tabs'][$option_id] =& $option;
					break;
				case 'box':
					$collected['boxes'][$option_id] =& $option;
					break;
				case 'group':
					$collected['groups'][$option_id] =& $option;

					$collected['groups_and_options'][$option_id] =& $option;
					break;
				default:
					trigger_error('Invalid option container type: '. $option['type'], E_USER_WARNING);
			}
		} elseif (is_int($option_id) && is_array($option)) {
			// array with options
			fw_collect_first_level_options($collected, $option);
		} elseif (isset($option['type'])) {
			// simple option, last possible level in options array
			$collected['options'][$option_id] =& $option;

			$collected['groups_and_options'][$option_id] =& $option;
		} else {
			trigger_error('Invalid option: '. $option_id, E_USER_WARNING);
		}
	}
}

/**
 * Get correct values from input (POST) for given options
 * This values can be saved in db then replaced with $option['value'] for each option
 * @param array $options
 * @param array $input_array
 * @return array Values
 */
function fw_get_options_values_from_input(array $options, $input_array = null) {
	if (!is_array($input_array)) {
		$input_array = FW_Request::POST(FW_Option_Type::get_default_name_prefix());
	}

	$values = array();

	foreach (fw_extract_only_options($options) as $id => $option) {
		$values[$id] = fw()->backend->option_type($option['type'])->get_value_from_input(
			$option,
			isset($input_array[$id]) ? $input_array[$id] : null
		);
	}

	return $values;
}

/**
 * 'abc[def][xyz]'   -> 'abc/def/xyz'
 * 'abc[def][xyz][]' -> 'abc/def/xyz'
 */
function fw_html_attr_name_to_array_multi_key($attr_name) {
	$attr_name = str_replace('[]', '',  $attr_name);
	$attr_name = str_replace('][', '/', $attr_name);
	$attr_name = str_replace('[',  '/', $attr_name);
	$attr_name = str_replace(']',  '',  $attr_name);

	return $attr_name;
}

/**
 * Used when getting some option value from serialized array saved in a custom place
 * and that option is unreachable for standard WordPress filters by other plugins
 * For e.g. that option cannot be translated by plugins, so we pass its value through this function and do the fixes
 * @param $value
 * @return array
 */
function fw_prepare_option_value($value) {
	if (empty($value)) {
		return $value;
	}

	if (function_exists('qtrans_use_current_language_if_not_found_use_default_language')) {
		if (is_array($value)) {
			array_walk_recursive($value, 'qtrans_use_current_language_if_not_found_use_default_language');
		} else {
			$value = qtrans_use_current_language_if_not_found_use_default_language($value);
		}
	}

	return $value;
}

/**
 * This function is used in 'save_post' action
 *
 * @param $post_id
 * @return bool
 */
function fw_is_real_post_save($post_id) {
	return !(
		wp_is_post_revision($post_id)
		|| wp_is_post_autosave($post_id)
		|| (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		|| (defined('DOING_AJAX') && DOING_AJAX)
		|| empty($_POST)
		|| empty($_POST['post_ID'])
		|| $_POST['post_ID'] != $post_id
	);
}

/**
 * @return Array with Google fonts
 */
function fw_get_google_fonts() {
	$cache_key = 'fw_google_fonts';

	try {
		return FW_Cache::get($cache_key);
	} catch (FW_Cache_Not_Found_Exception $e) {
		$fonts = apply_filters('fw_google_fonts',
			json_decode(
				file_get_contents(dirname(__FILE__) .'/fw-google-fonts.json'),
				true
			)
		);

		FW_Cache::set($cache_key, $fonts);

		return $fonts;
	}
}

/**
 * @return string Current url
 */
function fw_current_url() {
	static $cache = null;
	if ($cache !== null)
		return $cache;

	$pageURL = 'http';

	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		$pageURL .= 's';

	$pageURL .= '://';

	if ($_SERVER['SERVER_PORT'] != '80')
		$pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
	else
		$pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

	$cache = $pageURL;

	return $cache;
}

function fw_is_valid_domain_name($domain_name) {
	return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) // valid chars check
		&& preg_match("/^.{1,253}$/", $domain_name) // overall length check
		&& preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)); // length of each label
}

/**
 * Use this id do not want to enter every time same last two parameters
 * Info: Cannot use default parameters because in php 5.2 encoding is not UTF-8 by default
 *
 * @param string $string
 * @return string
 */
function fw_htmlspecialchars($string) {
	return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if current user has one capability from the given list
 *
 * @param array $capabilities list of capabilities to check
 * @param mixed $default_value
 * @return string|bool|mixed
 *  Return first capability that user can.
 *  Else, return default value if it is not null, else return first capability from list.
 *  Use default value false to check if user can some of the capabilities
 */
function fw_current_user_can($capabilities, $default_value = null)
{
	if (is_user_logged_in()) {
		foreach ($capabilities as $capability) {
			if (current_user_can($capability))
				return $capability;
		}
	}

	return ($default_value !== null ? $default_value : array_shift($capabilities));
}

/**
 * Convert number of seconds to 'X {units}'
 *
 * E.g. 123 => '2 minutes'
 * then you can use this string how you want, for e.g. append ' ago' => '2 minutes ago'
 *
 * @param int $seconds
 * @return string
 */
function fw_human_time($seconds)
{
	static $translations = null;
	if ($translations === null) {
		$translations = array(
			'year'      => __('year', 'fw'),
			'years'     => __('years', 'fw'),

			'month'     => __('month', 'fw'),
			'months'    => __('months', 'fw'),

			'week'      => __('week', 'fw'),
			'weeks'     => __('weeks', 'fw'),

			'day'       => __('day', 'fw'),
			'days'      => __('days', 'fw'),

			'hour'      => __('hour', 'fw'),
			'hours'     => __('hours', 'fw'),

			'minute'    => __('minute', 'fw'),
			'minutes'   => __('minutes', 'fw'),

			'second'    => __('second', 'fw'),
			'seconds'   => __('seconds', 'fw'),
		);
	}

	$tokens = array (
		31536000 => 'year',
		2592000 => 'month',
		604800 => 'week',
		86400 => 'day',
		3600 => 'hour',
		60  => 'minute',
		1  => 'second'
	);

	foreach ($tokens as $unit => $translation_key) {
		if ($seconds < $unit)
			continue;

		$number_of_units = floor($seconds / $unit);

		return $number_of_units .' '. $translations[ $translation_key . ($number_of_units != 1 ? 's' : '') ];
	}
}

function fw_strlen($string) {
	return mb_strlen($string, 'UTF-8');
}

/**
 * If currently is a Post Edit page display/submit
 * @return bool
 */
function fw_is_post_edit() {
	static $result = null;

	if ($result === null) {
		$result = false;

		if (is_admin()) {
			if (
				empty($_POST)
				&&
				isset($_GET['action'])
				&&
				$_GET['action'] === 'edit'
				&&
				isset($_GET['post'])
			) {
				// Display Edit Post page
				$result = true;
			} elseif (
				isset($_POST['action'])
				&&
				$_POST['action'] === 'editpost'
				&&
				isset($_POST['post_type'])
				&&
				isset($_POST['post_ID'])
				&&
				strpos(wp_get_referer(), 'action=edit') !== false
			) {
				// Submit Edit Post page
				$result = true;
			}
		}
	}

	return $result;
}

/**
 * @param string $dirname 'foo-bar'
 * @return string 'Foo_Bar'
 */
function fw_dirname_to_classname($dirname) {
	$class_name = explode('-', $dirname);
	$class_name = array_map('ucfirst', $class_name);
	$class_name = implode('_', $class_name);

	return $class_name;
}

/**
 * This function is a wrapper function that set correct width and height for iframes from wp_oembed_get() function
 *
 * @param $url
 * @param array $args
 * @return bool|string
 */
function fw_oembed_get($url, $args = array()) {
	$html = wp_oembed_get($url, $args);

	if (!empty($args['width']) and !empty($args['height']) and class_exists('DOMDocument') and !empty($html)) {
		$dom_element = new DOMDocument();
		$dom_element->loadHTML($html);
		$obj = $dom_element->getElementsByTagName('iframe')->item(0);
		$obj->setAttribute('width', $args['width']);
		$obj->setAttribute('height', $args['height']);
		//saveXml instead of SaveHTML for php version compatibility
		$html = $dom_element->saveXML($obj, LIBXML_NOEMPTYTAG);
	}

	return $html;
}

/**
 * @var $length
 * @return string
 *
 * Reference
 *
 * Strong cryptography in PHP
 * http://www.zimuel.it/en/strong-cryptography-in-php/
 * > Don't use rand() or mt_rand()
 */
function fw_secure_rand($length)
{
	if (function_exists('openssl_random_pseudo_bytes')) {
		$rnd = openssl_random_pseudo_bytes($length, $strong);
		if ($strong) {
			return $rnd;
		}
	}

	$sha ='';
	$rnd ='';

	if (file_exists('/dev/urandom')) {
		$fp = fopen('/dev/urandom', 'rb');
		if ($fp) {
			if (function_exists('stream_set_read_buffer')) {
				stream_set_read_buffer($fp, 0);
			}
			$sha = fread($fp, $length);
			fclose($fp);
		}
	}

	for ($i = 0; $i < $length; $i++) {
		$sha = hash('sha256', $sha.mt_rand());
		$char = mt_rand(0, 62);
		$rnd .= chr(hexdec($sha[$char].$sha[$char+1]));
	}

	return $rnd;
}

/**
 * Try to make user friendly title from an id
 * @param string $id 'hello-world'
 * @return string 'Hello world'
 */
function fw_id_to_title($id) {
	// mb_ucfirst()
	$id = mb_strtoupper(mb_substr($id, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($id, 1, mb_strlen($id, 'UTF-8'), 'UTF-8');

	return str_replace(array('_', '-'), ' ', $id);
}
