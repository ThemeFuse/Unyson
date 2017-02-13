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

		private static function query_posts(array $limits) {
			$limits = array_merge(array(
				'type' => array(
					'post' => true,
					'page' => true,
				),
				'title' => '',
				'id' => array( /* 1, 7, 120 */ ),
				'limit' => 100,
			), $limits);

			$limits['limit'] = max($limits['limit'], 1);

			/** @var WPDB $wpdb */
			global $wpdb;

			$sql = "SELECT ID val, post_title title"
				." FROM $wpdb->posts"
				." WHERE post_status IN ( 'publish', 'private' )";
				//." AND NULLIF(post_password, '') IS NULL"; todo: review

			{
				$prepare = array();

				if ($limits['id']) {
					$sql .= " AND ID IN ( "
						. implode( ', ', array_fill( 1, count( $limits['id'] ), '%d' ) )
						. " ) ";
					$prepare = array_merge($prepare, $limits['id']);
				}

				if ($limits['type']) {
					$sql .= " AND post_type IN ( "
						. implode( ', ', array_fill( 1, count( $limits['type'] ), '%s' ) )
						. " ) ";
					$prepare = array_merge($prepare, array_keys($limits['type']));
				}

				if ($limits['title']) {
					$sql .= " AND post_title LIKE %s";
					$prepare[] = '%'. $wpdb->esc_like( $limits['title'] ) .'%';
				}
			}

			$sql .= " LIMIT ". intval($limits['limit']);

			return $wpdb->get_results(
				$prepare
					? $wpdb->prepare($sql, $prepare)
					: $sql,
				ARRAY_A
			);
		}

		private static function query_terms(array $limits) {
			$limits = array_merge(array(
				'taxonomy' => array(
					'category' => true,
				),
				'title' => '',
				'id' => array( /* 1, 7, 120 */ ),
				'limit' => 100,
			), $limits);

			$limits['limit'] = max($limits['limit'], 1);

			/** @var WPDB $wpdb */
			global $wpdb;

			$sql = "SELECT terms.term_id val, terms.name title"
				." FROM $wpdb->terms AS terms, $wpdb->term_taxonomy AS taxonomies"
				." WHERE terms.term_id = taxonomies.term_id AND taxonomies.term_id = taxonomies.term_taxonomy_id";

			{
				$prepare = array();

				if ($limits['id']) {
					$sql .= " AND terms.term_id IN ( "
						. implode( ', ', array_fill( 1, count( $limits['id'] ), '%d' ) )
						. " ) ";
					$prepare = array_merge($prepare, $limits['id']);
				}

				if ($limits['taxonomy']) {
					$sql .= " AND taxonomies.taxonomy IN ( "
						. implode( ', ', array_fill( 1, count( $limits['taxonomy'] ), '%s' ) )
						. " ) ";
					$prepare = array_merge($prepare, array_keys($limits['taxonomy']));
				}

				if ($limits['title']) {
					$sql .= " AND terms.name LIKE %s";
					$prepare[] = '%'. $wpdb->esc_like( $limits['title'] ) .'%';
				}
			}

			$sql .= " LIMIT ". intval($limits['limit']);

			return $wpdb->get_results(
				$prepare
					? $wpdb->prepare($sql, $prepare)
					: $sql,
				ARRAY_A
			);
		}

		private static function query_users(array $limits) {
			$limits = array_merge(array(
				'name' => '',
				'role' => array(
					'editor' => true,
				),
				'id' => array( /* 1, 7, 120 */ ),
				'limit' => 100,
			), $limits);

			$limits['limit'] = max($limits['limit'], 1);

			/** @var WPDB $wpdb */
			global $wpdb;

			$sql = "SELECT DISTINCT users.ID AS val, users.user_nicename AS title"
				." FROM $wpdb->users AS users, $wpdb->usermeta AS usermeta"
				." WHERE usermeta.user_id = users.ID";

			{
				$prepare = array();

				if ($limits['id']) {
					$sql .= " AND users.ID IN ( "
						. implode( ', ', array_fill( 1, count( $limits['id'] ), '%d' ) )
						. " ) ";
					$prepare = array_merge($prepare, $limits['id']);
				}

				if ($limits['role']) {
					$sql .= " AND usermeta.meta_key = '{$wpdb->prefix}capabilities' "
						. "AND ( "
						. implode( ' OR ',
							array_fill( 1, count( $limits['role'] ), 'usermeta.meta_value LIKE %s' ) ) .
						" ) ";

					foreach ( $limits['role'] as $name => $filter_by ) {
						$prepare[] = ( $filter_by ) ? '%' . $wpdb->esc_like( $name ) . '%' : '';
					}
				}

				if ($limits['name']) {
					$sql .= " AND users.user_nicename LIKE %s";
					$prepare[] = '%'. $wpdb->esc_like( $limits['name'] ) .'%';
				}
			}

			$sql .= " LIMIT ". intval($limits['limit']);

			return $wpdb->get_results(
				$prepare
					? $wpdb->prepare($sql, $prepare)
					: $sql,
				ARRAY_A
			);
		}

		/**
		 * @internal
		 */
		public static function _ajax_autocomplete() {
			if (!current_user_can('edit_posts')) {
				wp_send_json_error();
			}

			$type  = FW_Request::POST( 'data/type' );
			$names = ($names = json_decode( FW_Request::POST( 'data/names' ), true )) ? $names : array();
			$title = FW_Request::POST( 'data/string' );

			$items = array();

			switch ( $type ) {
				case 'posts':
					$items = self::query_posts(array(
						'type' => array_fill_keys($names, true),
						'title' => $title,
					));
					break;
				case 'taxonomy':
					$items = self::query_terms(array(
						'taxonomy' => array_fill_keys($names, true),
						'title' => $title,
					));
					break;
				case 'users':
					$items = self::query_users(array(
						'role' => array_fill_keys($names, true),
						'name' => $title,
					));
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
			$population = $option['population'];
			$source     = array();
			$items      = array();

			if ( isset( $option['population'] ) ) {
				switch ( $option['population'] ) {
					case 'array' :
						if ( isset( $option['choices'] ) && is_array( $option['choices'] ) ) {
							foreach ($option['choices'] as $c_key => $c_val) {
								$items[] = array(
									'val' => $c_key,
									'title' => $c_val,
								);
							}
						}
						break;
					case 'posts' :
						if ( isset( $option['source'] ) ) {
							$source = is_array( $option['source'] ) ? $option['source'] : array( $option['source'] );
							$items = self::query_posts(array(
								'type' => array_fill_keys($source, true),
								'id' => $data['value'],
								'limit' => $option['prepopulate'],
							));
						}
						break;
					case 'taxonomy' :
						if ( isset( $option['source'] ) ) {
							$source = is_array( $option['source'] ) ? $option['source'] : array( $option['source'] );
							$items = self::query_terms(array(
								'taxonomy' => array_fill_keys($source, true),
								'id' => $data['value'],
								'limit' => $option['prepopulate'],
							));
						}
						break;
					case 'users' :
						if ( isset( $option['source'] ) && ! empty( $option['source'] ) ) {
							$source = is_array( $option['source'] ) ? $option['source'] : array( $option['source'] );
						} else {
							$source = array();
						}

						$items = self::query_users(array(
							'role' => array_fill_keys($source, true),
							'id' => $data['value'],
							'limit' => $option['prepopulate'],
						));
						break;
					default :
						return '(Invalid <code>population</code> parameter)';
				}

				$option['attr']['data-options']    = json_encode( $items );
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
