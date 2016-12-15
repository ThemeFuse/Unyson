<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( ! class_exists( 'FW_Option_Type_Multi_Select' ) ):

	/**
	 * Select multiple choices from different sources: posts, taxonomies, users or custom array
	 */
	class FW_Option_Type_Multi_Select extends FW_Option_Type {
		/**
		 * @internal
		 */
		protected function _get_defaults() {
			return array(
				'value'       => array(),
				/**
				 * Available options: array, posts, taxonomy, users
				 */
				'population'  => 'array',
				/**
				 * Set post types, taxonomies, user roles to search for
				 *
				 * 'population' => 'posts'
				 * 'source' => 'page',
				 *
				 * 'population' => 'taxonomy'
				 * 'source' => 'category',
				 *
				 * 'population' => 'users'
				 * 'source' => array( 'editor', 'subscriber', 'author' ),
				 *
				 * 'population' => 'array'
				 * 'source' => '' // will populate with 'choices' array
				 */
				'source'      => '',
				/**
				 * Set the number of posts/users/taxonomies that multi-select will be prepopulated
				 * Or set the value to false in order to disable this functionality.
				 */
				'prepopulate' => 10,
				/**
				 * An array with the available choices
				 * Used only when 'population' => 'array'
				 */
				'choices'     => array( /* 'value' => 'Title' */ ),
				/**
				 * Set maximum items number that can be selected
				 */
				'limit'       => 100,
			);
		}

		private $internal_options = array();

		public function get_type() {
			return 'multi-select';
		}

		/**
		 * @internal
		 */
		public function _init() {
			$this->internal_options = array(
				'label' => false,
				'type'  => 'text',
				'value' => '',
			);
		}

		/**
		 * @internal
		 */
		public static function _ajax_autocomplete() {
			if (!current_user_can('edit_posts')) {
				wp_send_json_error();
			}

			/**
			 * @var WPDB $wpdb
			 */
			global $wpdb;

			$type  = FW_Request::POST( 'data/type' );
			$names = json_decode( FW_Request::POST( 'data/names' ), true );
			$title = FW_Request::POST( 'data/string' );

			$items = array();

			switch ( $type ) {
				case 'posts':
					$items = $wpdb->get_results(
						call_user_func_array(
							array( $wpdb, 'prepare' ),
							array_merge(
								array(
									"SELECT ID val, post_title title " .
									"FROM $wpdb->posts " .
									"WHERE post_title LIKE %s " .
									"AND post_status IN ( 'publish', 'private' ) " .
									//"AND NULLIF(post_password, '') IS NULL " . todo: review
									"AND post_type IN ( " .
									implode( ', ', array_fill( 1, count( $names ), '%s' ) ) .
									" ) " .
									"LIMIT 100",
									'%' . $wpdb->esc_like( $title ) . '%'
								),
								/**
								 * These strings may contain '%abc'
								 * so we cannot use them directly in sql
								 * because $wpdb->prepare() will return false
								 * also you can test that with sprintf()
								 */
								$names
							)
						)
					);
					break;
				case 'taxonomy':
					$items = $wpdb->get_results(
						call_user_func_array(
							array( $wpdb, 'prepare' ),
							array_merge(
								array(
									"SELECT terms.term_id val, terms.name title " .
									"FROM $wpdb->terms as terms, $wpdb->term_taxonomy as taxonomies " .
									"WHERE terms.name LIKE %s AND taxonomies.taxonomy IN ( " .
									implode( ', ', array_fill( 1, count( $names ), '%s' ) ) .
									" ) " .
									"AND terms.term_id = taxonomies.term_id " .
									"AND taxonomies.term_id = taxonomies.term_taxonomy_id " .
									"LIMIT 100",
									'%' . $wpdb->esc_like( $title ) . '%'
								),
								/**
								 * These strings may contain '%abc'
								 * so we cannot use them directly in sql
								 * because $wpdb->prepare() will return false
								 * also you can test that with sprintf()
								 */
								$names
							)
						)
					);
					break;
				case 'users':
					if ( empty( $names ) ) {
						$items = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT users.id val, users.user_nicename title " .
								"FROM $wpdb->users as users " .
								"WHERE users.user_nicename LIKE %s " .
								"LIMIT 100",
								'%' . $wpdb->esc_like( $title ) . '%'
							)
						);
					} else {
						$like_user_meta = array();
						foreach ( $names as $name ) {
							$like_user_meta[] = '%' . $wpdb->esc_like( $name ) . '%';
						}

						$items = $wpdb->get_results(
							call_user_func_array(
								array( $wpdb, 'prepare' ),
								array_merge(
									array(
										"SELECT users.id val, users.user_nicename title " .
										"FROM $wpdb->users as users, $wpdb->usermeta as usermeta " .
										"WHERE users.user_nicename LIKE %s AND usermeta.meta_key = 'wp_capabilities' " .
										"AND ( " .
										implode( ' OR ',
											array_fill( 1, count( $like_user_meta ), 'usermeta.meta_value LIKE %s' ) ) .
										" ) " .
										"AND usermeta.user_id = users.ID",
										'%' . $wpdb->esc_like( $title ) . '%'
									),
									/**
									 * These strings may contain '%abc'
									 * so we cannot use them directly in sql
									 * because $wpdb->prepare() will return false
									 * also you can test that with sprintf()
									 */
									$like_user_meta
								)
							)
						);
					}
					break;
			}

			wp_send_json_success( $items );
		}

		/**
		 * @internal
		 */
		public function _get_backend_width_type() {
			return 'fixed';
		}

		/**
		 * @internal
		 */
		protected function _render( $id, $option, $data ) {
			$items      = array();
			$population = 'array';
			$source     = array();

			if ( isset( $option['population'] ) ) {
				switch ( $option['population'] ) {
					case 'array' :
						if ( isset( $option['choices'] ) && is_array( $option['choices'] ) ) {
							$items = $option['choices'];
						}
						break;
					case 'posts' :
						if ( isset( $option['source'] ) ) {
							$source     = is_array( $option['source'] ) ? $option['source'] : array( $option['source'] );
							$population = 'posts';
						}
						break;
					case 'taxonomy' :
						if ( isset( $option['source'] ) ) {
							$population = 'taxonomy';
							$source     = is_array( $option['source'] ) ? $option['source'] : array( $option['source'] );
						}
						break;
					case 'users' :
						$population = 'users';

						if ( isset( $option['source'] ) && ! empty( $option['source'] ) ) {
							$source = is_array( $option['source'] ) ? $option['source'] : array( $option['source'] );
						} else {
							$source = array();
						}
						break;
					default :
						return '(Invalid <code>population</code> parameter)';
				}

				$option['attr']['data-options']    = json_encode( $this->convert_array( $items ) );
				$option['attr']['data-population'] = $population;
				$option['attr']['data-source']     = json_encode( $source );
				$option['attr']['data-limit']      = ( intval( $option['limit'] ) > 0 ) ? $option['limit'] : 0;
			} else {
				return '(The <code>population</code> parameter is required)';
			}

			if ( ! empty( $data['value'] ) ) {
				$data['value'] = implode( '/*/', $data['value'] );
			} else {
				$data['value'] = '';
			}

			return fw()->backend->option_type( 'text' )->render( $id, $option, $data );
		}

		/**
		 * @internal
		 * {@inheritdoc}
		 */
		protected function _enqueue_static( $id, $option, $data ) {
			wp_enqueue_style(
				$this->get_type() . '-styles',
				fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/css/style.css' ),
				array( 'fw-selectize' ),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				$this->get_type() . '-styles',
				fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js' ),
				array( 'jquery', 'fw-events', 'fw-selectize' ),
				fw()->manifest->get_version(),
				true
			);

			fw()->backend->option_type( 'text' )->enqueue_static();
		}

		/**
		 * @internal
		 */
		private function convert_array( $array = array() ) {
			if ( ! is_array( $array ) || empty( $array ) ) {
				return array();
			}

			$return = array();
			foreach ( $array as $key => $item ) {
				$return[] = array(
					'val'   => $key,
					'title' => ( $item ) ? $item : $key . ' (' . __( 'No title', 'fw' ) . ')',
				);
			}

			return $return;
		}

		/**
		 * @internal
		 */
		protected function _get_value_from_input( $option, $input_value ) {
			if ( is_null( $input_value ) ) {
				return $option['value'];
			}

			$value = explode( '/*/', $input_value );

			return empty( $input_value ) ? array() : $value;
		}
	}

	add_action( 'wp_ajax_fw_option_type_multi_select_autocomplete',
		array( "FW_Option_Type_Multi_Select", '_ajax_autocomplete' ) );
endif;
