<?php

if (! defined('FW')) { die('Forbidden'); }

class FW_Icon_V2_Packs_Loader
{
	public $icon_packs = array();

	/**
	 * This array will contain name of packs retrieved from the
	 * `fw:option_type:icon-v2:filter_packs` filter.
	 *
	 * array( 'font-awesome', 'unycon' )
	 */
	public $filtered_icon_packs = null;

	private $icons_for_packs_parsed = null;

	public function __construct()
	{
		/**
		 * You are able to load more packs at this step.
		 *
		 * Default packs can't be changed.
		 *
		 * Example:
		 *
		 * add_filter(
		 *   'fw:option_type:icon-v2:packs',
		 *   '_add_more_packs'
		 * );
		 *
		 * function _add_more_packs($default_packs) {
		 *   return array(
		 *     'new_pack_name' => array(
		 *       'name' => 'new_pack_name', // note that it have to be the same as array key
		 *       'title' => 'New Pack', // This one will be displayed inside picker
		 *       'css_class_prefix' => 'new-pack-name', // the class that will be used in CSS
		 *
		 *       // Path to the CSS file that will define your classes
		 *       // Please, note that you will be responsible for referencing
		 *       // your icon fonts in a correct way from your @font-face rules.
		 *       //
		 *       // Both of them are required.
		 *       //
		 *       'css_file' => 'path_to_your_css_file',
		 *       'css_file_uri' => 'network_accessible_path_to_your_css_file',
		 *
		 *       // Sometimes, you don't want to enqueue one more CSS file for
		 *       // an already existing pack. Just give the correct handle here
		 *       // and it'll work as expected.
		 *       // Please note that the handle should be correctly registered
		 *       // with wp_register_style() or wp_enqueue_style().
		 *       'admin_wp_enqueue_handle' => null,
		 *       'frontend_wp_enqueue_handle' => null,
		 *
		 *       // By default, the option type will enqueue all CSS from all
		 *       // packs. You can handle CSS by yourself by making this option falsy.
		 *       //
		 *       // Please note that you will have to enqueue your CSS
		 *       // both on the admin and frontend side.
		 *       'require_css_file' => false,
		 *
		 *       // Possible options:
		 *       // - false (default) - I'll try to it describe below
		 *       // - array - define list of icons by hand, that's the error prone one
		 *       //
		 *       // When you will set `icons` option as false for a specific pack,
		 *       // the option type icon-v2 will try to do a guess by itself
		 *       // about the icons you will want to be displayed inside the
		 *       // picker. The mechanics are the following: Option type will
		 *       // read all of CSS rules from the file in the `css_file` option.
		 *       // It will use `css_class_prefix` in order to filter the rules
		 *       // that will match actual icons. Your CSS should have this
		 *       // form in order to be matched:
		 *       //
		 *       // .`css_class_prefix`-some-icon:before { // after also works fine
		 *       //   content: '\266a';
		 *       // }
		 *       'icons' => array(
		 *         'new-pack-name-search',
		 *         'new-pack-name-arrow-right',
		 *         'new-pack-name-arrow-left'
		 *       ),
		 *
		 *       'apply_root_class' => true
		 *
		 *     );
		 *   );
		 * }
		 */
		$packs = apply_filters(
			'fw:option_type:icon-v2:packs',
			$this->get_default_icon_packs()
		);

		/**
		 * Default packs should be kept as they are.
		 *
		 * We update them to match the last versions from their sources.
		 */
		$packs = array_merge(
			$this->get_default_icon_packs(),
			$packs
		);

		foreach ($packs as $pack_name => $pack) {
			$this->icon_packs[$pack_name] = array_merge(
				array(
					'title' => ucfirst($pack_name),
					'css_class_prefix' => $pack_name,
					'css_file' => false,
					'css_file_uri' => false,
					'admin_wp_enqueue_handle' => null,
					'frontend_wp_enqueue_handle' => null,
					'require_css_file' => true,
					'icons' => false,
					'apply_root_class' => true
				),

				$pack
			);
		}

		return $packs;
	}

	public function enqueue_frontend_css()
	{
		foreach ($this->get_packs() as $pack_name => $pack) {
			/**
			 * The file will be required on the frontend side only if you want
			 * it to. You can totally change this behavior by using
			 * `fw:option-type:icon-v2:packs` filter.
			 *
			 * If you want to lazy load CSS on the frontend side you can 
			 * set `require_css_file` for this pack and load it by yourself,
			 * you will receive information about the pack in the value
			 * of the icon-v2 option type.
			 *
			 * By the way, even if you'll load all the CSS at once,
			 * the browser won't download any fonts before you actually use any
			 * icon in your HTML. So, the overhead is not that big.
			 */
			if ($pack['require_css_file']) {

				if ($pack['frontend_wp_enqueue_handle']) {
					wp_enqueue_style($pack['frontend_wp_enqueue_handle']);
					continue;
				}

				wp_enqueue_style(
					'fw-option-type-icon-v2-pack-' . $pack_name,
					$pack['css_file_uri'],
					array(),
					fw()->manifest->get_version()
				);

			}
		}
	}

	/**
	 * This method will enqueue css for each pack for the admin side
	 *
	 * It won't check `require_css_file` option. This option is only
	 * used on the frontend.
	 */
	public function enqueue_admin_css()
	{
		foreach ($this->get_packs() as $pack_name => $pack) {
			if ($pack['admin_wp_enqueue_handle']) {
				wp_enqueue_style($pack['admin_wp_enqueue_handle']);
				continue;
			}

			wp_enqueue_style(
				'fw-option-type-icon-v2-pack-' . $pack_name,
				$pack['css_file_uri'],
				array(),
				fw()->manifest->get_version()
			);
		}
	}

	public function get_packs($needs_to_load_icons_for_each_pack = false)
	{
		/**
		 * Be aggressive about doing this operation. It costs lots of time.
		 * Totally don't want it to be in frontend.
		 */
		if ($needs_to_load_icons_for_each_pack) {
			$this->_load_icons_for_each_pack();
		}

		$this->_load_filtered_icon_packs();

		return $this->_get_packs_for_names(
			$this->filtered_icon_packs
		);
	}

	public function get_icon_packs_names()
	{
		$collect_names = array();

		foreach ($this->icon_packs as $pack_name => $pack) {
			$collect_names[] = $pack_name;
		}

		return $collect_names;
	}

	private function _load_icons_for_each_pack()
	{
		if ($this->icons_for_packs_parsed) { return; }

		foreach ($this->icon_packs as $pack_name => $pack) {
			$this->icon_packs[$pack_name]['icons'] = array();

			if (! $pack['css_file']) { continue; }
			if ( is_array($pack['icons']) ) { continue; }

			$css = file_get_contents(
				$pack['css_file']
			);

			$parser_matches = array();

			preg_match_all(
				'/(?ims)([a-z0-9\s\,\.\:#_\-@]+)\{([^\}]*)\}/',
				$css,
				$parser_matches
			);

			foreach ($parser_matches[0] as $i => $x) {
				$selector = trim($parser_matches[1][$i]);
				$value = trim($parser_matches[2][$i]);

				$is_correct_prefix = substr(
					$selector, 0,
					strlen('.' . $pack['css_class_prefix'])
				) === '.' . $pack['css_class_prefix'];

				$is_with_pseudo_element = is_numeric(strpos($selector, ':'));
				$has_content_for_pseudo = is_numeric(strpos($value, 'content'));

				/**
				 * It's probably an icon definition at this point.
				 */
				$selector_is_icon = $is_correct_prefix &&
									$is_with_pseudo_element &&
									$has_content_for_pseudo;

				if ($selector_is_icon) {
					$icon = explode(':', ltrim($selector, '.'));

					$icon = $icon[0];

					$this->icon_packs[$pack_name]['icons'][] = $icon;
				}
			}
		}

		$this->icons_for_packs_parsed = true;
	}

	private function _load_filtered_icon_packs()
	{
		if ($this->filtered_icon_packs) return;

		$packs = apply_filters(
			'fw:option_type:icon-v2:filter_packs',
			$this->get_icon_packs_names()
		);

		$this->filtered_icon_packs = $packs;
	}

	private function _get_packs_for_names($names)
	{
		$packs = array();

		foreach ($this->icon_packs as $pack_name => $pack) {
			$pack_allowed = in_array($pack_name, $names);

			if ($pack_allowed) {
				$packs[$pack_name] = $pack;
			}
		}

		return $packs;
	}

	public function get_default_icon_packs()
	{
		return array(
			'dashicons' => array(
				'name' => 'dashicons',
				'title' => 'Dashicons',
				'css_class_prefix' => 'dashicons',
				'css_file' => ABSPATH . WPINC . '/css/dashicons.min.css',
				'css_file_uri' => includes_url('css/dashicons.min.css'),

				'admin_wp_enqueue_handle' => 'dashicons',
				'frontend_wp_enqueue_handle' => 'dashicons',
			),

			'linecons' => array(
				'name' => 'linecons',
				'title' => 'Linecons',
				'css_class_prefix' => 'linecons',
				'css_file' => fw_get_framework_directory(
					'/static/libs/linecons/css/linecons.css'
				),

				'css_file_uri' => fw_get_framework_directory_uri(
					'/static/libs/linecons/css/linecons.css'
				),
			),

			'font-awesome' => array(
				'name' => 'font-awesome',
				'title' => 'Font Awesome',
				'css_class_prefix' => 'fa',
				'css_file' => fw_get_framework_directory(
					'/static/libs/font-awesome/css/font-awesome.min.css'
				),

				'css_file_uri' => fw_get_framework_directory_uri(
					'/static/libs/font-awesome/css/font-awesome.min.css'
				),

				'admin_wp_enqueue_handle' => 'font-awesome'
			),

			'entypo' => array(
				'name' => 'entypo',
				'title' => 'Entypo',
				'css_class_prefix' => 'entypo',
				'css_file' => fw_get_framework_directory(
					'/static/libs/entypo/css/entypo.css'
				),

				'css_file_uri' => fw_get_framework_directory_uri(
					'/static/libs/entypo/css/entypo.css'
				),
			),


			'linearicons' => array(
				'name' => 'linearicons',
				'title' => 'Linearicons Free',
				'css_class_prefix' => 'lnr',

				'css_file' => fw_get_framework_directory(
					'/static/libs/lnr/css/lnr.css'
				),

				'css_file_uri' => fw_get_framework_directory_uri(
					'/static/libs/lnr/css/lnr.css'
				),
			),

			'typicons' => array(
				'name' => 'typicons',
				'title' => 'Typicons',
				'css_class_prefix' => 'typcn',

				'css_file' => fw_get_framework_directory(
					'/static/libs/typcn/css/typcn.css'
				),

				'css_file_uri' => fw_get_framework_directory_uri(
					'/static/libs/typcn/css/typcn.css'
				),
			),

			'unycon' => array(
				'name' => 'unycon',
				'title' => 'Unycon',
				'css_class_prefix' => 'unycon',
				'css_file' => fw_get_framework_directory( '/static/libs/unycon/unycon.css' ),
				'css_file_uri' => fw_get_framework_directory_uri(
					'/static/libs/unycon/unycon.css'
				),

				'admin_wp_enqueue_handle' => 'fw-unycon'
			)
		);
	}

	public function class_without_root_for($icon_class)
	{
		/**
		 * array('fa', 'fa-gear')
		 */
		$tokens = explode(' ', $icon_class);

		return count($tokens) === 2 ? $tokens[1] : $icon_class;
	}

	public function pack_name_for($icon_class)
	{
		/**
		 * array('fa', 'fa-gear')
		 */
		$tokens = explode(' ', $icon_class);
		$class_name = $icon_class;

		if (count($tokens) >= 2) {
			$class_name = $tokens[0];
		}

		$resulting_pack = null;

		foreach ($this->icon_packs as $pack_name => $pack) {
			if ($pack['css_class_prefix'] === $class_name) {
				$resulting_pack = $pack;
			}
		}

		return $resulting_pack;
	}
}

