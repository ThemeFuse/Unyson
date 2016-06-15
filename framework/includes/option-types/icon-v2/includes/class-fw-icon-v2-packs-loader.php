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

	public function __construct()
	{
		$cache_key = 'fw_option_type_icon_v2/packs';

		try {
			$this->icon_packs = FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {

			/**
			 * You are able to load more packs at this step.
			 *
			 * Default packs can't be changed.
			 *
			 * Example:
			 *
			 * add_filter(
			 *   'fw:option-type:icon-v2:packs',
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
						'require_css_file' => true,
						'icons' => false,
						'apply_root_class' => true
					),

					$pack
				);
			}

			FW_Cache::set($cache_key, $this->icon_packs);

			return $packs;
		}
	}

	public function enqueue_frontend_css()
	{
		foreach ($this->icon_packs as $pack_name => $pack) {
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

				wp_enqueue_style(
					'fw-option-type-icon-v2-pack-' . $pack_name . '-css',
					$pack['css_file_uri']
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
		foreach ($this->icon_packs as $pack_name => $pack) {
			wp_enqueue_style(
				'fw-option-type-icon-v2-pack-' . $pack_name . '-css',
				$pack['css_file_uri'],
				array(),
				'1.0'
			);
		}
	}

	public function get_packs()
	{
		$this->_load_icons_for_each_pack();
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
		global $wp_filesystem;

		if (empty($wp_filesystem)) {
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();
		}

		foreach ($this->icon_packs as $pack_name => $pack) {
			$this->icon_packs[$pack_name]['icons'] = array();


			if (! $pack['css_file']) { continue; }
			if ( is_array($pack['icons']) ) { continue; }

			if (! FW_WP_Filesystem::has_direct_access($pack['css_file'])) { continue; }

			$css = $wp_filesystem->get_contents(
				FW_WP_Filesystem::real_path_to_filesystem_path(
					$pack['css_file']
				)
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
					$icon = explode(':', ltrim($selector, '.'))[0];
					$this->icon_packs[$pack_name]['icons'][] = $icon;
				}
			}
		}
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
		$base_path = fw_get_framework_directory('/includes/option-types/icon-v2/static/css/');
		$base_uri  = fw_get_framework_directory_uri(
			'/includes/option-types/icon-v2/static/css/'
		);

		return array(
			'font-awesome' => array(
				'name' => 'font-awesome',
				'title' => 'Font Awesome',
				'css_class_prefix' => 'fa',
				'css_file' => $base_path . 'fa.css',
				'css_file_uri' => $base_uri . 'fa.css'
			),

			'entypo' => array(
				'name' => 'entypo',
				'title' => 'Entypo',
				'css_class_prefix' => 'entypo',
				'css_file' => $base_path . 'entypo.css',
				'css_file_uri' => $base_uri . 'entypo.css'
			),

			'linecons' => array(
				'name' => 'linecons',
				'title' => 'Linecons',
				'css_class_prefix' => 'linecons',
				'css_file' => $base_path . 'linecons.css',
				'css_file_uri' => $base_uri . 'linecons.css'
			),

			'linearicons' => array(
				'name' => 'linearicons',
				'title' => 'Linearicons Free',
				'css_class_prefix' => 'lnr',
				'css_file' => $base_path . 'lnr.css',
				'css_file_uri' => $base_uri . 'lnr.css',
			),

			'typicons' => array(
				'name' => 'typicons',
				'title' => 'Typicons',
				'css_class_prefix' => 'typcn',
				'css_file' => $base_path . 'typcn.css',
				'css_file_uri' => $base_uri . 'typcn.css'
			),

			'unycon' => array(
				'name' => 'unycon',
				'title' => 'Unycon',
				'css_class_prefix' => 'unycon',
				'css_file' => fw_get_framework_directory( '/static/libs/unycon/unycon.css' ),
				'css_file_uri' => fw_get_framework_directory_uri(
					'/static/libs/unycon/unycon.css'
				)
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

