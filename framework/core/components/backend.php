<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Backend functionality
 */
final class _FW_Component_Backend {

	/** @var callable */
	private $print_meta_box_content_callback;

	/** @var FW_Settings_Form */
	private $settings_form;

	private $available_render_designs = array( 'default', 'taxonomy', 'customizer' );

	private $default_render_design = 'default';

	/**
	 * The singleton instance of Parsedown class that is used across
	 * whole framework.
	 *
	 * @since 2.6.9
	 */
	private $markdown_parser = null;

	/**
	 * Contains all option types
	 * @var FW_Option_Type[]
	 */
	private $option_types = array();

	/**
	 * @var FW_Option_Type_Undefined
	 */
	private $undefined_option_type;

	/**
	 * Store container types for registration, until they will be required
	 * @var array|false
	 *      array Can have some pending container types in it
	 *      false Container types already requested and was registered, so do not use pending anymore
	 */
	private $container_types_pending_registration = array();

	/**
	 * Contains all container types
	 * @var FW_Container_Type[]
	 */
	private $container_types = array();

	/**
	 * @var FW_Container_Type_Undefined
	 */
	private $undefined_container_type;

	private $static_registered = false;

	/**
	 * @var FW_Access_Key
	 */
	private $access_key;

	/**
	 * @internal
	 */
	public function _get_settings_page_slug() {
		return 'fw-settings';
	}

	/**
	 * @return string
	 * @since 2.6.3
	 */
	public function get_options_name_attr_prefix() {
		return 'fw_options';
	}

	/**
	 * @return string
	 * @since 2.6.3
	 */
	public function get_options_id_attr_prefix() {
		return 'fw-option-';
	}

	private function get_current_edit_taxonomy() {
		static $cache_current_taxonomy_data = null;

		if ( $cache_current_taxonomy_data !== null ) {
			return $cache_current_taxonomy_data;
		}

		$result = array(
			'taxonomy' => null,
			'term_id'  => 0,
		);

		do {
			if ( ! is_admin() ) {
				break;
			}

			// code from /wp-admin/admin.php line 110
			{
				if ( isset( $_REQUEST['taxonomy'] ) && taxonomy_exists( $_REQUEST['taxonomy'] ) ) {
					$taxnow = $_REQUEST['taxonomy'];
				} else {
					$taxnow = '';
				}
			}

			if ( empty( $taxnow ) ) {
				break;
			}

			$result['taxonomy'] = $taxnow;

			if ( empty( $_REQUEST['tag_ID'] ) ) {
				return $result;
			}

			// code from /wp-admin/edit-tags.php
			{
				$tag_ID = (int) $_REQUEST['tag_ID'];
			}

			$result['term_id'] = $tag_ID;
		} while ( false );

		$cache_current_taxonomy_data = $result;

		return $cache_current_taxonomy_data;
	}

	public function __construct() {
		$this->print_meta_box_content_callback = create_function( '$post,$args', 'echo $args["args"];' );
	}

	/**
	 * @internal
	 */
	public function _init() {
		if ( is_admin() ) {
			$this->settings_form = new FW_Settings_Form_Theme('theme-settings');
		}

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @internal
	 */
	public function _after_components_init() {}

	private function get_access_key()
	{
		if (!$this->access_key) {
			$this->access_key = new FW_Access_Key('fw_backend');
		}

		return $this->access_key;
	}

	private function add_actions() {
		if ( is_admin() ) {
			add_action('add_meta_boxes', array($this, '_action_create_post_meta_boxes'), 10, 2);
			add_action('init', array($this, '_action_init'), 20);
			add_action('admin_enqueue_scripts', array($this, '_action_admin_register_scripts'),
				/**
				 * Usually when someone register/enqueue a script/style to be used in other places
				 * in 'admin_enqueue_scripts' actions with default (not set) priority 10, they use priority 9.
				 * Use here priority 8, in case those scripts/styles used in actions with priority 9
				 * are using scripts/styles registered here
				 */
				8
			);
			add_action('admin_enqueue_scripts', array($this, '_action_admin_enqueue_scripts'),
				/**
				 * In case some custom defined option types are using script/styles registered
				 * in actions with default priority 10 (make sure the enqueue is executed after register)
				 */
				11
			);

			// render and submit options from javascript
			{
				add_action('wp_ajax_fw_backend_options_render', array($this, '_action_ajax_options_render'));
				add_action('wp_ajax_fw_backend_options_get_values', array($this, '_action_ajax_options_get_values'));
				add_action('wp_ajax_fw_backend_options_get_values_json', array($this, '_action_ajax_options_get_values_json'));
			}
		}

		add_action('save_post', array($this, '_action_save_post'), 7, 3);
		add_action('wp_restore_post_revision', array($this, '_action_restore_post_revision'), 10, 2);
		add_action('_wp_put_post_revision', array($this, '_action__wp_put_post_revision'));

		add_action('customize_register', array($this, '_action_customize_register'), 7);
	}

	private function add_filters() {
		if ( is_admin() ) {
			add_filter('admin_footer_text', array($this, '_filter_admin_footer_text'), 11);
			add_filter('update_footer', array($this, '_filter_footer_version'), 11);
		}
	}

	/**
	 * @param string|FW_Option_Type $option_type_class
	 * @param string|null $type
	 *
	 * @internal
	 */
	private function register_option_type( $option_type_class, $type = null ) {
		if ( $type == null ) {
			try {
				$type = $this->get_instance( $option_type_class )->get_type();
			} catch ( FW_Option_Type_Exception_Invalid_Class $exception ) {
				if ( ! is_subclass_of( $option_type_class, 'FW_Option_Type' ) ) {
					trigger_error( 'Invalid option type class ' . get_class( $option_type_class ), E_USER_WARNING );

					return;
				}
			}
		}

		if ( isset( $this->option_types[ $type ] ) ) {
			trigger_error( 'Option type "' . $type . '" already registered', E_USER_WARNING );

			return;
		}

		$this->option_types[$type] = $option_type_class;
	}

	/**
	 * @param string|FW_Container_Type $container_type_class
	 * @param string|null $type
	 *
	 * @internal
	 */
	private function register_container_type( $container_type_class, $type = null ) {
		if ( $type == null ) {
			try {
				$type = $this->get_instance( $container_type_class )->get_type();
			} catch ( FW_Option_Type_Exception_Invalid_Class $exception ) {
				if ( ! is_subclass_of( $container_type_class, 'FW_Container_Type' ) ) {
					trigger_error( 'Invalid container type class ' . get_class( $container_type_class ), E_USER_WARNING );

					return;
				}
			}
		}

		if ( isset( $this->container_types[ $type ] ) ) {
			trigger_error( 'Container type "' . $type . '" already registered', E_USER_WARNING );

			return;
		}

		$this->container_types[$type] = $container_type_class;
	}

	private function register_static() {
		if (
			!doing_action('admin_enqueue_scripts')
			&&
			!did_action('admin_enqueue_scripts')
		) {
			/**
			 * Do not wp_enqueue/register_...() because at this point not all handles has been registered
			 * and maybe they are used in dependencies in handles that are going to be enqueued.
			 * So as a result some handles will not be equeued because of not registered dependecies.
			 */
			return;
		}

		if ( $this->static_registered ) {
			return;
		}

		/**
		 * Register styles/scripts only in admin area, on frontend it's not allowed to use styles/scripts from framework backend core
		 * because they are meant to be used only in backend and can be changed in the future.
		 * If you want to use a style/script from framework backend core, copy it to your theme and enqueue as a theme style/script.
		 */
		if ( ! is_admin() ) {
			$this->static_registered = true;

			return;
		}

		wp_register_script(
			'fw-events',
			fw_get_framework_directory_uri( '/static/js/fw-events.js' ),
			array(),
			fw()->manifest->get_version(),
			true
		);

		wp_register_script(
			'fw-ie-fixes',
			fw_get_framework_directory_uri( '/static/js/ie-fixes.js' ),
			array(),
			fw()->manifest->get_version(),
			true
		);

		{
			wp_register_style(
				'qtip',
				fw_get_framework_directory_uri( '/static/libs/qtip/css/jquery.qtip.min.css' ),
				array(),
				fw()->manifest->get_version()
			);
			wp_register_script(
				'qtip',
				fw_get_framework_directory_uri( '/static/libs/qtip/jquery.qtip.min.js' ),
				array( 'jquery' ),
				fw()->manifest->get_version()
			);
		}

		/**
		 * Important!
		 * Call wp_enqueue_media() before wp_enqueue_script('fw') (or using 'fw' in your script dependencies)
		 * otherwise fw.OptionsModal won't work
		 */
		{
			wp_register_style(
				'fw',
				fw_get_framework_directory_uri( '/static/css/fw.css' ),
				array( 'qtip' ),
				fw()->manifest->get_version()
			);

			wp_register_script(
				'fw-reactive-options-registry',
				fw_get_framework_directory_uri(
					'/static/js/fw-reactive-options-registry.js'
				),
				array('fw', 'fw-events'),
				false
			);

			wp_register_script(
				'fw-reactive-options-simple-options',
				fw_get_framework_directory_uri(
					'/static/js/fw-reactive-options-simple-options.js'
				),
				array('fw', 'fw-events', 'fw-reactive-options-undefined-option'),
				false
			);

			wp_register_script(
				'fw-reactive-options-undefined-option',
				fw_get_framework_directory_uri(
					'/static/js/fw-reactive-options-undefined-option.js'
				),
				array(
					'fw', 'fw-events', 'fw-reactive-options-registry'
				),
				false
			);

			wp_register_script(
				'fw-reactive-options',
				fw_get_framework_directory_uri('/static/js/fw-reactive-options.js'),
				array(
					'fw', 'fw-events', 'fw-reactive-options-undefined-option',
					'fw-reactive-options-simple-options'
				),
				false
			);

			wp_register_script(
				'fw',
				fw_get_framework_directory_uri( '/static/js/fw.js' ),
				array( 'jquery', 'fw-events', 'backbone', 'qtip' ),
				fw()->manifest->get_version(),
				false // false fixes https://github.com/ThemeFuse/Unyson/issues/1625#issuecomment-224219454
			);

			wp_localize_script( 'fw', '_fw_localized', array(
				'FW_URI'     => fw_get_framework_directory_uri(),
				'SITE_URI'   => site_url(),
				'LOADER_URI' => apply_filters( 'fw_loader_image', fw_get_framework_directory_uri() . '/static/img/logo.svg' ),
				'l10n'       => array_merge(
					$l10n = array(
						'modal_save_btn' => __( 'Save', 'fw' ),
						'done'     => __( 'Done', 'fw' ),
						'ah_sorry' => __( 'Ah, Sorry', 'fw' ),
						'reset'    => __( 'Reset', 'fw' ),
						'apply'    => __( 'Apply', 'fw' ),
						'cancel'   => __( 'Cancel', 'fw' ),
						'ok'       => __( 'Ok', 'fw' )
					),
					/**
					 * fixes https://github.com/ThemeFuse/Unyson/issues/2381
					 * @since 2.6.14
					 */
					apply_filters('fw_js_l10n', $l10n)
				),
				'options_modal' => array(
					/** @since 2.6.13 */
					'default_reset_bnt_disabled' => apply_filters('fw:option-modal:default:reset-btn-disabled', false)
				),
			) );
		}

		{
			wp_register_style(
				'fw-backend-options',
				fw_get_framework_directory_uri( '/static/css/backend-options.css' ),
				array( 'fw' ),
				fw()->manifest->get_version()
			);

			wp_register_script(
				'fw-backend-options',
				fw_get_framework_directory_uri( '/static/js/backend-options.js' ),
				array( 'fw', 'fw-events', 'fw-reactive-options', 'postbox', 'jquery-ui-tabs' ),
				fw()->manifest->get_version(),
				true
			);

			wp_localize_script( 'fw', '_fw_backend_options_localized', array(
				'lazy_tabs' => fw()->theme->get_config('lazy_tabs')
			) );
		}

		{
			wp_register_style(
				'fw-selectize',
				fw_get_framework_directory_uri( '/static/libs/selectize/selectize.css' ),
				array(),
				fw()->manifest->get_version()
			);
			wp_register_script(
				'fw-selectize',
				fw_get_framework_directory_uri( '/static/libs/selectize/selectize.min.js' ),
				array( 'jquery', 'fw-ie-fixes' ),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_register_script(
				'fw-mousewheel',
				fw_get_framework_directory_uri( '/static/libs/mousewheel/jquery.mousewheel.min.js' ),
				array( 'jquery' ),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_register_style(
				'fw-jscrollpane',
				fw_get_framework_directory_uri( '/static/libs/jscrollpane/jquery.jscrollpane.css' ),
				array(),
				fw()->manifest->get_version()
			);
			wp_register_script( 'fw-jscrollpane',
				fw_get_framework_directory_uri( '/static/libs/jscrollpane/jquery.jscrollpane.min.js' ),
				array( 'jquery', 'fw-mousewheel' ),
				fw()->manifest->get_version(),
				true
			);
		}

		wp_register_style(
			'font-awesome',
			fw_get_framework_directory_uri( '/static/libs/font-awesome/css/font-awesome.min.css' ),
			array(),
			fw()->manifest->get_version()
		);
		/**
		 * backwards compatibility, in case extensions are not up-to-date
		 * todo: remove in next major version
		 * https://github.com/ThemeFuse/Unyson/issues/2198
		 * @deprecated
		 */
		wp_register_style('fw-font-awesome', fw_get_framework_directory_uri( '/static/libs/font-awesome/css/font-awesome.min.css' ));

		wp_register_script(
			'backbone-relational',
			fw_get_framework_directory_uri( '/static/libs/backbone-relational/backbone-relational.js' ),
			array( 'backbone' ),
			fw()->manifest->get_version(),
			true
		);

		wp_register_script(
			'fw-uri',
			fw_get_framework_directory_uri( '/static/libs/uri/URI.js' ),
			array(),
			fw()->manifest->get_version(),
			true
		);

		wp_register_script(
			'fw-moment',
			/**
			 * IMPORTANT: At the end of the script is added this line:
			 * moment.locale(document.documentElement.lang.slice(0, 2)); // fixes https://github.com/ThemeFuse/Unyson/issues/1767
			 */
			fw_get_framework_directory_uri( '/static/libs/moment/moment-with-locales.min.js' ),
			array(),
			fw()->manifest->get_version(),
			true
		);

		wp_register_script(
			'fw-form-helpers',
			fw_get_framework_directory_uri( '/static/js/fw-form-helpers.js' ),
			array( 'jquery' ),
			fw()->manifest->get_version(),
			true
		);

		wp_register_style(
			'fw-unycon',
			fw_get_framework_directory_uri( '/static/libs/unycon/unycon.css' ),
			array(),
			fw()->manifest->get_version()
		);

		$this->static_registered = true;
	}

	/**
	 * @param $class
	 *
	 * @return FW_Option_Type
	 * @throws FW_Option_Type_Exception_Invalid_Class
	 */
	protected function get_instance( $class ) {
		if ( ! class_exists( $class )
		     || (
			     ! is_subclass_of( $class, 'FW_Option_Type' )
			     &&
			     ! is_subclass_of( $class, 'FW_Container_Type' )
		     )
		) {
			throw new FW_Option_Type_Exception_Invalid_Class( $class );
		}

		return new $class;
	}

	public function _filter_admin_footer_text( $html ) {
		if (
			(
				current_user_can( 'update_themes' )
				||
				current_user_can( 'update_plugins' )
			)
			&&
			fw_current_screen_match(array(
				'only' => array(
					array('parent_base' => fw()->extensions->manager->get_page_slug()) // Unyson Extensions page
				)
			))
		) {
			return ( empty( $html ) ? '' : $html . '<br/>' )
			. '<em>'
			. str_replace(
				array(
					'{wp_review_link}',
					'{facebook_share_link}',
					'{twitter_share_link}',
				),
				array(
					fw_html_tag('a', array(
						'target' => '_blank',
						'href'   => 'https://wordpress.org/support/view/plugin-reviews/unyson?filter=5#postform',
					), __('leave a review', 'fw')),
					fw_html_tag('a', array(
						'target' => '_blank',
						'href'   => 'https://www.facebook.com/sharer/sharer.php?'. http_build_query(array(
							'u' => 'http://unyson.io',
						)),
						'onclick' => 'return !window.open(this.href, \'Facebook\', \'width=640,height=300\')',
					), __('Facebook', 'fw')),
					fw_html_tag('a', array(
						'target' => '_blank',
						'href'   => 'https://twitter.com/home?'. http_build_query(array(
							'status' => __('Unyson WordPress Framework is the fastest and easiest way to develop a premium theme. I highly recommend it', 'fw')
								.' http://unyson.io/ #UnysonWP',
						)),
						'onclick' => 'return !window.open(this.href, \'Twitter\', \'width=640,height=430\')',
					), __('Twitter', 'fw')),
				),
				__('If you like Unyson, {wp_review_link}, share on {facebook_share_link} or {twitter_share_link}.', 'fw')
			)
			. '</em>';
		} else {
			return $html;
		}
	}

	/**
	 * Print framework version in the admin footer
	 *
	 * @param string $html
	 *
	 * @return string
	 * @internal
	 */
	public function _filter_footer_version( $html ) {
		if ( current_user_can( 'update_themes' ) || current_user_can( 'update_plugins' ) ) {
			return ( empty( $html ) ? '' : $html . ' | ' ) . fw()->manifest->get_name() . ' ' . fw()->manifest->get_version();
		} else {
			return $html;
		}
	}

	/**
	 * @param string $post_type
	 * @param WP_Post $post
	 */
	public function _action_create_post_meta_boxes( $post_type, $post ) {
		if ( 'comment' === $post_type ) {
			/**
			 * This is wrong, comment is not a post(type)
			 * it is stored in a separate db table and has a separate meta (wp_comments and wp_commentmeta)
			 */
			return;
		}

		$options = fw()->theme->get_post_options( $post_type );

		if ( empty( $options ) ) {
			return;
		}

		$collected = array();

		fw_collect_options( $collected, $options, array(
			'limit_option_types'    => false,
			'limit_container_types' => false,
			'limit_level'           => 1,
		) );

		if ( empty( $collected ) ) {
			return;
		}

		$values = fw_get_db_post_option( $post->ID );

		foreach ( $collected as $id => &$option ) {
			if ( isset( $option['options'] ) && ( $option['type'] === 'box' || $option['type'] === 'group' ) ) {
				$context  = isset( $option['context'] ) ? $option['context'] : 'normal';
				$priority = isset( $option['priority'] ) ? $option['priority'] : 'default';

				add_meta_box(
					"fw-options-box-{$id}",
					empty( $option['title'] ) ? ' ' : $option['title'],
					$this->print_meta_box_content_callback,
					$post_type,
					$context,
					$priority,
					$this->render_options( $option['options'], $values )
				);
			} else { // this is not a box, wrap it in auto-generated box
				add_meta_box(
					'fw-options-box:auto-generated:' . time() . ':' . fw_unique_increment(),
					' ',
					$this->print_meta_box_content_callback,
					$post_type,
					'normal',
					'default',
					$this->render_options( array( $id => $option ), $values )
				);
			}
		}
	}

	/**
	 * @param object $term
	 */
	public function _action_create_taxonomy_options( $term ) {
		$options = fw()->theme->get_taxonomy_options( $term->taxonomy );

		if ( empty( $options ) ) {
			return;
		}

		$collected = array();

		fw_collect_options( $collected, $options, array(
			'limit_option_types' => false,
			'limit_container_types' => false,
			'limit_level' => 1,
		) );

		if ( empty( $collected ) ) {
			return;
		}

		$values = fw_get_db_term_option( $term->term_id, $term->taxonomy );

		// fixes word_press style: .form-field input { width: 95% }
		echo '<style type="text/css">.fw-option-type-radio input, .fw-option-type-checkbox input { width: auto; }</style>';

		do_action( 'fw_backend_options_render:taxonomy:before' );
		echo $this->render_options( $collected, $values, array(), 'taxonomy' );
		do_action( 'fw_backend_options_render:taxonomy:after' );
	}

	/**
	 * @param string $taxonomy
	 */
	public function _action_create_add_taxonomy_options( $taxonomy ) {
		$options = fw()->theme->get_taxonomy_options( $taxonomy );

		if ( empty( $options ) ) {
			return;
		}

		$collected = array();

		fw_collect_options( $collected, $options, array(
			'limit_option_types'    => false,
			'limit_container_types' => false,
			'limit_level'           => 1,
		) );

		if ( empty( $collected ) ) {
			return;
		}

		// fixes word_press style: .form-field input { width: 95% }
		echo '<style type="text/css">.fw-option-type-radio input, .fw-option-type-checkbox input { width: auto; }</style>';

		do_action( 'fw_backend_options_render:taxonomy:before' );

		echo '<div class="fw-force-xs">';
		echo $this->render_options( $collected, array(), array(), 'taxonomy' );
		echo '</div>';

		do_action( 'fw_backend_options_render:taxonomy:after' );

		echo '<script type="text/javascript">'
			.'jQuery(function($){'
			.'    $("#submit").on("click", function(){'
			.'        $("html, body").animate({ scrollTop: $("#col-left").offset().top });'
			.'    });'
			.'});'
			.'</script>';
	}

	public function _action_init() {
		$current_edit_taxonomy = $this->get_current_edit_taxonomy();

		if ( $current_edit_taxonomy['taxonomy'] ) {
			add_action(
				$current_edit_taxonomy['taxonomy'] . '_edit_form',
				array( $this, '_action_create_taxonomy_options' )
			);

			if (fw()->theme->get_config('taxonomy_create_has_unyson_options', true)) {
				add_action(
					$current_edit_taxonomy['taxonomy'] . '_add_form_fields',
					array( $this, '_action_create_add_taxonomy_options' )
				);
			}
		}

		if ( ! empty( $_POST ) ) {
			// is form submit
			add_action( 'edited_term', array( $this, '_action_term_edit' ), 10, 3 );

			if ($current_edit_taxonomy['taxonomy']) {
				add_action(
					'create_' . $current_edit_taxonomy['taxonomy'],
					array($this, '_action_save_taxonomy_fields')
				);
			}
		}
	}

	/**
	 * Save meta from $_POST to fw options (post meta)
	 * @param int $post_id
	 * @param WP_Post $post
	 * @param bool $update
	 */
	public function _action_save_post( $post_id, $post, $update ) {
		if (
			isset($_POST['post_ID'])
			&&
			intval($_POST['post_ID']) === intval($post_id)
			&&
			!empty($_POST[ $this->get_options_name_attr_prefix() ]) // this happens on Quick Edit
		) {
			/**
			 * This happens on regular post form submit
			 * All data from $_POST belongs this $post
			 * so we save them in its post meta
			 */

			static $post_options_save_happened = false;
			if ($post_options_save_happened) {
				/**
				 * Prevent multiple options save for same post
				 * It can happen from a recursion or wp_update_post() for same post id
				 */
				return;
			} else {
				$post_options_save_happened = true;
			}

			$old_values = (array)fw_get_db_post_option($post_id);

			fw_set_db_post_option(
				$post_id,
				null,
				fw_get_options_values_from_input(
					fw()->theme->get_post_options($post->post_type)
				)
			);

			/**
			 * @deprecated
			 * Use the 'fw_post_options_update' action
			 */
			do_action( 'fw_save_post_options', $post_id, $post, $old_values );
		} elseif ($original_post_id = wp_is_post_autosave( $post_id )) {
			do {
				$parent = get_post($post->post_parent);

				if ( ! $parent instanceof WP_Post ) {
					break;
				}

				if (
					isset($_POST['post_ID'])
					&&
					intval($_POST['post_ID']) === intval($parent->ID)
				) {} else {
					break;
				}

				if (empty($_POST[ $this->get_options_name_attr_prefix() ])) {
					// this happens on Quick Edit
					break;
				}

				fw_set_db_post_option(
					$post->ID,
					null,
					fw_get_options_values_from_input(
						fw()->theme->get_post_options($parent->post_type)
					)
				);
			} while(false);
		} elseif ($original_post_id = wp_is_post_revision( $post_id )) {
			/**
			 * Do nothing, the
			 * - '_wp_put_post_revision'
			 * - 'wp_restore_post_revision'
			 * actions will handle this
			 */
		}  else {
			/**
			 * This happens on:
			 * - post add (auto-draft): do nothing
			 * - revision restore: do nothing, that is handled by the 'wp_restore_post_revision' action
			 */
		}
	}

	/**
	 * @param $post_id
	 * @param $revision_id
	 */
	public function _action_restore_post_revision($post_id, $revision_id)
	{
		/**
		 * Copy options meta from revision to post
		 */
		fw_set_db_post_option(
			$post_id,
			null,
			(array)fw_get_db_post_option($revision_id, null, array())
		);
	}

	/**
	 * @param $revision_id
	 */
	public function _action__wp_put_post_revision($revision_id)
	{
		/**
		 * Copy options meta from post to revision
		 */
		fw_set_db_post_option(
			$revision_id,
			null,
			(array)fw_get_db_post_option(
				wp_is_post_revision($revision_id),
				null,
				array()
			)
		);
	}

	/**
	 * Update all post meta `fw_option:<option-id>` with values from post options that has the 'save-in-separate-meta' parameter
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 * @deprecated since 2.5.0
	 */
	public function _sync_post_separate_meta( $post_id ) {
		if ( ! ( $post_type = get_post_type( $post_id ) ) ) {
			return false;
		}

		$meta_prefix = 'fw_option:';
		$only_options = fw_extract_only_options( fw()->theme->get_post_options( $post_type ) );
		$separate_meta_options = array();

		// Collect all options that needs to be saved in separate meta
		{
			$options_values = fw_get_db_post_option( $post_id );

			foreach ($only_options as $option_id => $option) {
				if (
					isset( $option['save-in-separate-meta'] )
					&&
					$option['save-in-separate-meta']
					&&
					array_key_exists( $option_id, $options_values )
				) {
					if (defined('WP_DEBUG') && WP_DEBUG) {
						FW_Flash_Messages::add(
							'save-in-separate-meta:deprecated',
							'<p>The <code>save-in-separate-meta</code> option parameter is <strong>deprecated</strong>.</p>'
							.'<p>Please replace</p>'
							.'<pre>\'save-in-separate-meta\' => true</pre>'
							.'<p>with</p>'
							.'<pre>\'fw-storage\' => array('
							."\n	'type' => 'post-meta',"
							."\n	'post-meta' => 'fw_option:{your-option-id}',"
							."\n)</pre>"
							.'<p>in <code>{theme}'. fw_get_framework_customizations_dir_rel_path('/theme/options/posts/'. $post_type .'.php') .'</code></p>'
							.'<p><a href="'. esc_attr('http://manual.unyson.io/en/latest/options/storage.html#content') .'" target="_blank">'. esc_html__('Info about fw-storage', 'fw') .'</a></p>',
							'warning'
						);
					}

					$separate_meta_options[ $meta_prefix . $option_id ] = $options_values[ $option_id ];
				}
			}

			unset( $options_values );
		}

		// Delete meta that starts with $meta_prefix
		{
			/** @var wpdb $wpdb */
			global $wpdb;

			foreach (
				$wpdb->get_results(
					$wpdb->prepare(
						"SELECT meta_key " .
						"FROM {$wpdb->postmeta} " .
						"WHERE meta_key LIKE %s AND post_id = %d",
						$wpdb->esc_like( $meta_prefix ) . '%',
						$post_id
					)
				) as $row
			) {
				if (
					array_key_exists( $row->meta_key, $separate_meta_options )
					||
					( // skip options containing 'fw-storage'
						($option_id = substr($row->meta_key, 10))
						&&
						isset($only_options[$option_id]['fw-storage'])
					)
				) {
					/**
					 * This meta exists and will be updated below.
					 * Do not delete for performance reasons, instead of delete->insert will be performed only update
					 */
					continue;
				} else {
					// this option does not exist anymore
					delete_post_meta( $post_id, $row->meta_key );
				}
			}
		}

		foreach ( $separate_meta_options as $meta_key => $option_value ) {
			fw_update_post_meta($post_id, $meta_key, $option_value );
		}

		return true;
	}

	/**
	 * @param int $term_id
	 */
	public function _action_save_taxonomy_fields( $term_id ) {
		if (
			isset( $_POST['action'] )
			&&
			'add-tag' === $_POST['action']
			&&
			isset( $_POST['taxonomy'] )
			&&
			($taxonomy = get_taxonomy( $_POST['taxonomy'] ))
			&&
			current_user_can($taxonomy->cap->edit_terms)
		) { /* ok */ } else { return; }

		$options = fw()->theme->get_taxonomy_options( $taxonomy->name );
		if ( empty( $options ) ) {
			return;
		}

		fw_set_db_term_option(
			$term_id,
			$taxonomy->name,
			null,
			fw_get_options_values_from_input($options)
		);

		do_action( 'fw_save_term_options', $term_id, $taxonomy->name, array() );
	}

	public function _action_term_edit( $term_id, $tt_id, $taxonomy ) {
		if (
			isset( $_POST['action'] )
			&&
			'editedtag' === $_POST['action']
			&&
			isset( $_POST['taxonomy'] )
			&&
			($taxonomy = get_taxonomy( $_POST['taxonomy'] ))
			&&
			current_user_can($taxonomy->cap->edit_terms)
		) { /* ok */ } else { return; }

		if (intval(FW_Request::POST('tag_ID')) != $term_id) {
			// the $_POST values belongs to another term, do not save them into this one
			return;
		}

		$options = fw()->theme->get_taxonomy_options( $taxonomy->name );
		if ( empty( $options ) ) {
			return;
		}

		$old_values = (array) fw_get_db_term_option( $term_id, $taxonomy->name );

		fw_set_db_term_option(
			$term_id,
			$taxonomy->name,
			null,
			fw_get_options_values_from_input($options)
		);

		do_action( 'fw_save_term_options', $term_id, $taxonomy->name, $old_values );
	}

	public function _action_admin_register_scripts() {
		$this->register_static();
	}

	public function _action_admin_enqueue_scripts() {
		/**
		 * Enqueue settings options static in <head>
		 * @see FW_Settings_Form_Theme::_action_admin_enqueue_scripts()
		 */

		/**
		 * Enqueue post options static in <head>
		 */
		{
			if ( 'post' === get_current_screen()->base && get_the_ID() ) {
				fw()->backend->enqueue_options_static(
					fw()->theme->get_post_options( get_post_type() )
				);

				do_action( 'fw_admin_enqueue_scripts:post', get_post() );
			}
		}

		/**
		 * Enqueue term options static in <head>
		 */
		{
			if (
				in_array(get_current_screen()->base, array('edit-tags', 'term'), true)
				&&
				get_current_screen()->taxonomy
			) {
				fw()->backend->enqueue_options_static(
					fw()->theme->get_taxonomy_options( get_current_screen()->taxonomy )
				);

				do_action( 'fw_admin_enqueue_scripts:term', get_current_screen()->taxonomy );
			}
		}
	}

	/**
	 * Render options html from input json
	 *
	 * POST vars:
	 * - options: '[{option_id: {...}}, {option_id: {...}}, ...]'                  // Required // String JSON
	 * - values:  {option_id: value, option_id: {...}, ...}                        // Optional // Object
	 * - data:    {id_prefix: 'fw_options-a-b-', name_prefix: 'fw_options[a][b]'}  // Optional // Object
	 */
	public function _action_ajax_options_render() {
		// options
		{
			if ( ! isset( $_POST['options'] ) ) {
				wp_send_json_error( array(
					'message' => 'No options'
				) );
			}

			$options = json_decode( FW_Request::POST( 'options' ), true );

			if ( ! $options ) {
				wp_send_json_error( array(
					'message' => 'Wrong options'
				) );
			}
		}

		// values
		{
			if ( isset( $_POST['values'] ) ) {
				$values = FW_Request::POST( 'values' );

				if (is_string($values)) {
					$values = json_decode($values, true);
				}
			} else {
				$values = array();
			}

			$values = array_intersect_key($values, fw_extract_only_options($options));
		}

		// data
		{
			if ( isset( $_POST['data'] ) ) {
				$data = FW_Request::POST( 'data' );
			} else {
				$data = array();
			}
		}

		wp_send_json_success( array(
			'html' => fw()->backend->render_options( $options, $values, $data ),
			/** @since 2.6.1 */
			'default_values' => fw_get_options_values_from_input($options, array()),
		) );
	}

	/**
	 * Get options values from html generated with 'fw_backend_options_render' ajax action
	 *
	 * POST vars:
	 * - options: '[{option_id: {...}}, {option_id: {...}}, ...]' // Required // String JSON
	 * - fw_options... // Use a jQuery "ajax form submit" to emulate real form submit
	 *
	 * Tip: Inside form html, add: <input type="hidden" name="options" value="[...json...]">
	 */
	public function _action_ajax_options_get_values() {
		// options
		{
			if ( ! isset( $_POST['options'] ) ) {
				wp_send_json_error( array(
					'message' => 'No options'
				) );
			}

			$options = FW_Request::POST( 'options' );

			if (is_string( $options )) {
				$options = json_decode( FW_Request::POST( 'options' ), true );
			}

			if ( ! $options ) {
				wp_send_json_error( array(
					'message' => 'Wrong options'
				) );
			}
		}

		// name_prefix
		{
			if ( isset( $_POST['name_prefix'] ) ) {
				$name_prefix = FW_Request::POST( 'name_prefix' );
			} else {
				$name_prefix = $this->get_options_name_attr_prefix();
			}
		}

		wp_send_json_success( array(
			'values' => fw_get_options_values_from_input(
				$options,
				FW_Request::POST( fw_html_attr_name_to_array_multi_key( $name_prefix ), array() )
			)
		) );
	}

	/**
	 * Get options values from html generated with 'fw_backend_options_render' ajax action
	 *
	 * POST vars:
	 * - options: '[{option_id: {...}}, {option_id: {...}}, ...]' // Required // String JSON
	 * - values: {option_id: {...}}
	 *
	 * Tip: Inside form html, add: <input type="hidden" name="options" value="[...json...]">
	 */
	public function _action_ajax_options_get_values_json() {
		// options
		{
			if ( ! isset( $_POST['options'] ) ) {
				wp_send_json_error( array(
					'message' => 'No options'
				) );
			}

			$options = FW_Request::POST( 'options' );

			if (is_string( $options )) {
				$options = json_decode( FW_Request::POST( 'options' ), true );
			}

			if ( ! $options ) {
				wp_send_json_error( array(
					'message' => 'Wrong options'
				) );
			}
		}

		// values
		{
			if ( ! isset( $_POST['values'] ) ) {
				wp_send_json_error( array(
					'message' => 'No values'
				) );
			}

			$values = FW_Request::POST( 'values' );

			if (is_string( $values )) {
				$values = json_decode( FW_Request::POST( 'values' ), true );
			}

			if (! is_array($values)) {
				if ( ! $values ) {
					wp_send_json_error(array(
						'message' => 'Wrong values'
					));
				}
			}
		}

		wp_send_json_success( array(
			'values' => fw_get_options_values_from_input(
				$options,
				$values
			)
		) );
	}

	/**
	 * Render options array and return the generated HTML
	 *
	 * @param array $options
	 * @param array $values Correct values returned by fw_get_options_values_from_input()
	 * @param array $options_data {id_prefix => ..., name_prefix => ...}
	 * @param string $design
	 *
	 * @return string HTML
	 */
	public function render_options( $options, $values = array(), $options_data = array(), $design = null ) {
		if (empty($design)) {
			$design = $this->default_render_design;
		}

		if (
			!doing_action('admin_enqueue_scripts')
			&&
			!did_action('admin_enqueue_scripts')
		) {
			/**
			 * Do not wp_enqueue/register_...() because at this point not all handles has been registered
			 * and maybe they are used in dependencies in handles that are going to be enqueued.
			 * So as a result some handles will not be equeued because of not registered dependecies.
			 */
		} else {
			/**
			 * register scripts and styles
			 * in case if this method is called before enqueue_scripts action
			 * and option types has some of these in their dependencies
			 */
			$this->register_static();

			wp_enqueue_media();
			wp_enqueue_style( 'fw-backend-options' );
			wp_enqueue_script( 'fw-backend-options' );
		}

		$collected = array();

		fw_collect_options( $collected, $options, array(
			'limit_option_types' => false,
			'limit_container_types' => false,
			'limit_level' => 1,
			'info_wrapper' => true,
		) );

		if ( empty( $collected ) ) {
			return false;
		}

		$html = '';

		$option = reset( $collected );

		$collected_type = array(
			'group' => $option['group'],
			'type'  => $option['option']['type'],
		);
		$collected_type_options = array(
			$option['id'] => &$option['option']
		);

		while ( $collected_type_options ) {
			$option = next( $collected );

			if ( $option ) {
				if (
					$option['group'] === $collected_type['group']
					&&
					$option['option']['type'] === $collected_type['type']
				) {
					$collected_type_options[ $option['id'] ] = &$option['option'];
					continue;
				}
			}

			switch ( $collected_type['group'] ) {
				case 'container':
					if ($design === 'taxonomy') {
						$html .= fw_render_view(
							fw_get_framework_directory('/views/backend-container-design-'. $design .'.php'),
							array(
								'type' => $collected_type['type'],
								'html' => $this->container_type($collected_type['type'])->render(
									$collected_type_options, $values, $options_data
								),
							)
						);
					} else {
						$html .= $this->container_type($collected_type['type'])->render(
							$collected_type_options, $values, $options_data
						);
					}
					break;
				case 'option':
					foreach ( $collected_type_options as $id => &$_option ) {
						$data = $options_data; // do not change directly to not affect next loops

						$data['value'] = isset( $values[ $id ] ) ? $values[ $id ] : null;

						$html .= $this->render_option(
							$id,
							$_option,
							$data,
							$design
						);
					}
					unset($_option);
					break;
				default:
					$html .= '<p><em>' . __( 'Unknown collected group', 'fw' ) . ': ' . $collected_type['group'] . '</em></p>';
			}

			unset( $collected_type, $collected_type_options );

			if ( $option ) {
				$collected_type = array(
					'group' => $option['group'],
					'type'  => $option['option']['type'],
				);
				$collected_type_options = array(
					$option['id'] => &$option['option']
				);
			} else {
				$collected_type_options = array();
			}
		}

		return $html;
	}

	/**
	 * Enqueue options static
	 *
	 * Useful when you have dynamic options html on the page (for e.g. options modal)
	 * and in order to initialize that html properly, the option types scripts styles must be enqueued on the page
	 *
	 * @param array $options
	 */
	public function enqueue_options_static( $options ) {
		static $static_enqueue = true;

		if (
			!doing_action('admin_enqueue_scripts')
			&&
			!did_action('admin_enqueue_scripts')
		) {
			/**
			 * Do not wp_enqueue/register_...() because at this point not all handles has been registered
			 * and maybe they are used in dependencies in handles that are going to be enqueued.
			 * So as a result some handles will not be equeued because of not registered dependecies.
			 */
			return;
		} else {
			/**
			 * register scripts and styles
			 * in case if this method is called before enqueue_scripts action
			 * and option types has some of these in their dependencies
			 */
			if ($static_enqueue) {
				$this->register_static();

				wp_enqueue_media();
				wp_enqueue_style( 'fw-backend-options' );
				wp_enqueue_script( 'fw-backend-options' );

				$static_enqueue = false;
			}
		}

		$collected = array();

		fw_collect_options( $collected, $options, array(
			'limit_option_types' => false,
			'limit_container_types' => false,
			'limit_level' => 0,
			'callback' => array(__CLASS__, '_callback_fw_collect_options_enqueue_static'),
		) );

		unset($collected);
	}

	/**
	 * @internal
	 * @param array $data
	 */
	public static function _callback_fw_collect_options_enqueue_static($data) {
		if ($data['group'] === 'option') {
			fw()->backend->option_type($data['option']['type'])->enqueue_static($data['id'], $data['option']);
		} elseif ($data['group'] === 'container') {
			fw()->backend->container_type($data['option']['type'])->enqueue_static($data['id'], $data['option']);
		}
	}

	/**
	 * Render option enclosed in backend design
	 *
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 * @param string $design default or taxonomy
	 *
	 * @return string
	 */
	public function render_option( $id, $option, $data = array(), $design = null ) {
		if (empty($design)) {
			$design = $this->default_render_design;
		}

		if (
			!doing_action('admin_enqueue_scripts')
			&&
			!did_action('admin_enqueue_scripts')
		) {
			/**
			 * Do not wp_enqueue/register_...() because at this point not all handles has been registered
			 * and maybe they are used in dependencies in handles that are going to be enqueued.
			 * So as a result some handles will not be equeued because of not registered dependecies.
			 */
		} else {
			$this->register_static();
		}


		if ( ! in_array( $design, $this->available_render_designs ) ) {
			trigger_error( 'Invalid render design specified: ' . $design, E_USER_WARNING );
			$design = 'post';
		}

		if ( ! isset( $data['id_prefix'] ) ) {
			$data['id_prefix'] = $this->get_options_id_attr_prefix();
		}

		$data = apply_filters(
			'fw:backend:option-render:data',
			$data
		);

		return fw_render_view(fw_get_framework_directory('/views/backend-option-design-'. $design .'.php'), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
		) );
	}

	/**
	 * Render a meta box
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $content HTML
	 * @param array $other Optional elements
	 *
	 * @return string Generated meta box html
	 */
	public function render_box( $id, $title, $content, $other = array() ) {
		if ( ! function_exists( 'add_meta_box' ) ) {
			trigger_error( 'Try call this method later (\'admin_init\' action), add_meta_box() function does not exists yet.',
				E_USER_WARNING );

			return '';
		}

		$other = array_merge( array(
			'html_before_title' => false,
			'html_after_title'  => false,
			'attr'              => array(),
		), $other );

		{
			$placeholders = array(
				'id'      => '{{meta_box_id}}',
				'title'   => '{{meta_box_title}}',
				'content' => '{{meta_box_content}}',
			);

			// other placeholders
			{
				$placeholders['html_before_title'] = '{{meta_box_html_before_title}}';
				$placeholders['html_after_title']  = '{{meta_box_html_after_title}}';
				$placeholders['attr']              = '{{meta_box_attr}}';
				$placeholders['attr_class']        = '{{meta_box_attr_class}}';
			}
		}

		$cache_key = 'fw_meta_box_template';

		try {
			$meta_box_template = FW_Cache::get( $cache_key );
		} catch ( FW_Cache_Not_Found_Exception $e ) {
			$temp_screen_id = 'fw-temp-meta-box-screen-id-' . fw_unique_increment();
			$context        = 'normal';

			add_meta_box(
				$placeholders['id'],
				$placeholders['title'],
				$this->print_meta_box_content_callback,
				$temp_screen_id,
				$context,
				'default',
				$placeholders['content']
			);

			ob_start();

			do_meta_boxes( $temp_screen_id, $context, null );

			$meta_box_template = ob_get_clean();

			remove_meta_box( $id, $temp_screen_id, $context );

			// remove wrapper div, leave only meta box div
			{
				// <div ...>
				{
					$meta_box_template = str_replace(
						'<div id="' . $context . '-sortables" class="meta-box-sortables">',
						'',
						$meta_box_template
					);
				}

				// </div>
				{
					$meta_box_template = explode( '</div>', $meta_box_template );
					array_pop( $meta_box_template );
					$meta_box_template = implode( '</div>', $meta_box_template );
				}
			}

			// add 'fw-postbox' class and some attr related placeholders
			$meta_box_template = str_replace(
				'class="postbox',
				$placeholders['attr'] . ' class="postbox fw-postbox' . $placeholders['attr_class'],
				$meta_box_template
			);

			// add html_before|after_title placeholders
			{
				$meta_box_template = str_replace(
					'<span>' . $placeholders['title'] . '</span>',

					/**
					 * used <small> not <span> because there is a lot of css and js
					 * that thinks inside <h2 class="hndle"> there is only one <span>
					 * so do not brake their logic
					 */
					'<small class="fw-html-before-title">' . $placeholders['html_before_title'] . '</small>' .
					'<span>' . $placeholders['title'] . '</span>' .
					'<small class="fw-html-after-title">' . $placeholders['html_after_title'] . '</small>',

					$meta_box_template
				);
			}

			FW_Cache::set( $cache_key, $meta_box_template );
		}

		// prepare attributes
		{
			$attr_class = '';
			if ( isset( $other['attr']['class'] ) ) {
				$attr_class = ' ' . $other['attr']['class'];

				unset( $other['attr']['class'] );
			}

			unset( $other['attr']['id'] );
		}

		// replace placeholders with data/content
		return str_replace(
			array(
				$placeholders['id'],
				$placeholders['title'],
				$placeholders['content'],
				$placeholders['html_before_title'],
				$placeholders['html_after_title'],
				$placeholders['attr'],
				$placeholders['attr_class'],
			),
			array(
				esc_attr( $id ),
				$title,
				$content,
				$other['html_before_title'],
				$other['html_after_title'],
				fw_attr_to_html( $other['attr'] ),
				esc_attr( $attr_class )
			),
			$meta_box_template
		);
	}

	/**
	 * @param FW_Access_Key $access_key
	 * @param string|FW_Option_Type $option_type_class
	 *
	 * @internal
	 */
	public function _register_option_type( FW_Access_Key $access_key, $option_type_class, $type= null ) {
		if ( $access_key->get_key() !== 'fw_option_type' ) {
			trigger_error( 'Call denied', E_USER_ERROR );
		}

		$this->register_option_type( $option_type_class, $type );
	}

	/**
	 * @param FW_Access_Key $access_key
	 * @param string|FW_Container_Type $container_type_class
	 *
	 * @internal
	 */
	public function _register_container_type( FW_Access_Key $access_key, $container_type_class ) {
		if ( $access_key->get_key() !== 'fw_container_type' ) {
			trigger_error( 'Call denied', E_USER_ERROR );
		}

		$this->register_container_type( $container_type_class );
	}

	/**
	 * @param string $type
	 * @return FW_Option_Type
	 */
	public function option_type( $type ) {
		static $did_options_init = false;
		if ( ! $did_options_init ) {
			$did_options_init = true;
			do_action( 'fw_option_types_init' );
		}

		if ( isset( $this->option_types[ $type ] ) ) {
			if (is_string($this->option_types[$type])) {
				$this->option_types[$type] = $this->get_instance($this->option_types[$type]);
				$this->option_types[$type]->_call_init($this->get_access_key());
			}

			return $this->option_types[$type];
		} else {
			if ( is_admin() && apply_filters('fw_backend_undefined_option_type_warn_user', true, $type) ) {
				FW_Flash_Messages::add(
					'fw-get-option-type-undefined-' . $type,
					sprintf( __( 'Undefined option type: %s', 'fw' ), $type ),
					'warning'
				);
			}

			if ( ! $this->undefined_option_type ) {
				$this->undefined_option_type = new FW_Option_Type_Undefined();
			}

			return $this->undefined_option_type;
		}
	}

	/**
	 * Return an array with all option types names
	 *
	 * @return array
	 *
	 * @since 2.6.11
	 */
	public function get_option_types() {
		$this->option_type('text'); // trigger init
		return array_keys( $this->option_types );
	}

	/**
	 * Return an array with all container types names
	 *
	 * @return array
	 *
	 * @since 2.6.11
	 */
	public function get_container_types() {
		$this->container_type('box'); // trigger init
		return array_keys( $this->container_types );
	}

	/**
	 * @param string $type
	 * @return FW_Container_Type
	 */
	public function container_type( $type ) {
		static $did_containers_init = false;
		if ( ! $did_containers_init ) {
			$did_containers_init = true;
			do_action( 'fw_container_types_init' );
		}

		if ( isset( $this->container_types[ $type ] ) ) {
			if ( is_string( $this->container_types[ $type ] ) ) {
				$this->container_types[ $type ] = $this->get_instance( $this->container_types[$type] );
				$this->container_types[ $type ]->_call_init( $this->get_access_key() );
			}

			return $this->container_types[ $type ];
		} else {
			if ( is_admin() ) {
				FW_Flash_Messages::add(
					'fw-get-container-type-undefined-' . $type,
					sprintf( __( 'Undefined container type: %s', 'fw' ), $type ),
					'warning'
				);
			}

			if ( ! $this->undefined_container_type ) {
				$this->undefined_container_type = new FW_Container_Type_Undefined();
			}

			return $this->undefined_container_type;
		}
	}

	/**
	 * @param WP_Customize_Manager $wp_customize
	 * @internal
	 */
	public function _action_customize_register($wp_customize) {
		if (is_admin()) {
			add_action('admin_enqueue_scripts', array($this, '_action_enqueue_customizer_static'));
		}

		$this->customizer_register_options(
			$wp_customize,
			fw()->theme->get_customizer_options()
		);
	}

	/**
	 * @internal
	 */
	public function _action_enqueue_customizer_static()
	{
		{
			$options_for_enqueue = array();
			$customizer_options = fw()->theme->get_customizer_options();

			/**
			 * In customizer options is allowed to have container with unspecified (or not existing) 'type'
			 * fw()->backend->enqueue_options_static() tries to enqueue both options and container static
			 * not existing container types will throw notices.
			 * To prevent that, extract and send it only options (without containers)
			 */
			fw_collect_options($options_for_enqueue, $customizer_options, array(
				'callback' => array(__CLASS__, '_callback_fw_collect_options_enqueue_static'),
			));

			unset($options_for_enqueue, $customizer_options);
		}

		wp_enqueue_script(
			'fw-backend-customizer',
			fw_get_framework_directory_uri( '/static/js/backend-customizer.js' ),
			array( 'jquery', 'fw-events', 'backbone', 'fw-backend-options' ),
			fw()->manifest->get_version(),
			true
		);
		wp_localize_script(
			'fw-backend-customizer',
			'_fw_backend_customizer_localized',
			array(
				'change_timeout' => apply_filters('fw_customizer_option_change_timeout', 333),
			)
		);

		do_action('fw_admin_enqueue_scripts:customizer');
	}

	/**
	 * @param WP_Customize_Manager $wp_customize
	 * @param array $options
	 * @param array $parent_data {'type':'...','id':'...'}
	 */
	private function customizer_register_options($wp_customize, $options, $parent_data = array()) {
		$collected = array();

		fw_collect_options( $collected, $options, array(
			'limit_option_types' => false,
			'limit_container_types' => false,
			'limit_level' => 1,
			'info_wrapper' => true,
		) );

		if ( empty( $collected ) ) {
			return;
		}

		foreach ($collected as &$opt) {
			switch ($opt['group']) {
				case 'container':
					// Check if has container options
					{
						$_collected = array();

						fw_collect_options( $_collected, $opt['option']['options'], array(
							'limit_option_types' => array(),
							'limit_container_types' => false,
							'limit_level' => 1,
							'limit' => 1,
							'info_wrapper' => false,
						) );

						$has_containers = !empty($_collected);

						unset($_collected);
					}

					$children_data = array(
						'group' => 'container',
						'id' => $opt['id']
					);

					$args = array(
						'title' => empty($opt['option']['title'])
							? fw_id_to_title($opt['id'])
							: $opt['option']['title'],
						'description' => empty($opt['option']['desc'])
							? ''
							: $opt['option']['desc'],
					);

					if (isset($opt['option']['wp-customizer-args']) && is_array($opt['option']['wp-customizer-args'])) {
						$args = array_merge($opt['option']['wp-customizer-args'], $args);
					}

					if ($has_containers) {
						if ($parent_data) {
							trigger_error($opt['id'] .' panel can\'t have a parent ('. $parent_data['id'] .')', E_USER_WARNING);
							break;
						}

						$wp_customize->add_panel($opt['id'], $args);

						$children_data['customizer_type'] = 'panel';
					} else {
						if ($parent_data) {
							if ($parent_data['customizer_type'] === 'panel') {
								$args['panel'] = $parent_data['id'];
							} else {
								trigger_error($opt['id'] .' section can have only panel parent ('. $parent_data['id'] .')', E_USER_WARNING);
								break;
							}
						}

						$wp_customize->add_section($opt['id'], $args);

						$children_data['customizer_type'] = 'section';
					}

					$this->customizer_register_options(
						$wp_customize,
						$opt['option']['options'],
						$children_data
					);

					unset($children_data);
					break;
				case 'option':
					$setting_id = $this->get_options_name_attr_prefix() .'['. $opt['id'] .']';

					{
						$args_control = array(
							'label' => empty($opt['option']['label'])
								? fw_id_to_title($opt['id'])
								: $opt['option']['label'],
							'description' => empty($opt['option']['desc'])
								? ''
								: $opt['option']['desc'],
							'settings' => $setting_id,
						);

						if (isset($opt['option']['wp-customizer-args']) && is_array($opt['option']['wp-customizer-args'])) {
							$args_control = array_merge($opt['option']['wp-customizer-args'], $args_control);
						}

						if ($parent_data) {
							if ($parent_data['customizer_type'] === 'section') {
								$args_control['section'] = $parent_data['id'];
							} else {
								trigger_error('Invalid control parent: '. $parent_data['customizer_type'], E_USER_WARNING);
								break;
							}
						} else { // the option is not placed in a section, create a section automatically
							$args_control['section'] = 'fw_option_auto_section_'. $opt['id'];

							$wp_customize->add_section($args_control['section'], array(
								'title' => empty($opt['option']['label'])
									? fw_id_to_title($opt['id'])
									: $opt['option']['label'],
							));
						}
					}

					{
						$args_setting = array(
							'default' => fw()->backend->option_type($opt['option']['type'])->get_value_from_input($opt['option'], null),
							'fw_option' => $opt['option'],
							'fw_option_id' => $opt['id'],
						);

						if (isset($opt['option']['wp-customizer-setting-args']) && is_array($opt['option']['wp-customizer-setting-args'])) {
							$args_setting = array_merge($opt['option']['wp-customizer-setting-args'], $args_setting);
						}

						$wp_customize->add_setting(
							new _FW_Customizer_Setting_Option(
								$wp_customize,
								$setting_id,
								$args_setting
							)
						);

						unset($args_setting);
					}

					// control must be registered after setting
					$wp_customize->add_control(
						new _FW_Customizer_Control_Option_Wrapper(
							$wp_customize,
							$opt['id'],
							$args_control
						)
					);
					break;
				default:
					trigger_error('Unknown group: '. $opt['group'], E_USER_WARNING);
			}
		}
	}

	/**
	 * For e.g. an option-type was rendered using 'customizer' design,
	 * but inside it uses render_options() but it doesn't know the current render design
	 * and the options will be rendered with 'default' design.
	 * This method allows to specify the default design that will be used if not specified on render_options()
	 * @param null|string $design
	 * @internal
	 */
	public function _set_default_render_design($design = null)
	{
		if (empty($design) || !in_array($design, $this->available_render_designs)) {
			$this->default_render_design = 'default';
		} else {
			$this->default_render_design = $design;
		}
	}

	/**
	 * Get markdown parser with autoloading and caching
	 *
	 * Usage:
	 *   fw()->backend->get_markdown_parser()
	 *
	 * @param bool $fresh_instance Whether to force return a fresh instance of the class
	 *
	 * @return Parsedown
	 *
	 * @since 2.6.9
	 */
	public function get_markdown_parser($fresh_instance = false) {
		if (! $this->markdown_parser || $fresh_instance) {
			$this->markdown_parser = new Parsedown();
		}

		return $this->markdown_parser;
	}
}
