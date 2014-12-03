<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Select multiple choices from different sources: posts, taxonomies, users or custom array
 */
class FW_Option_Type_Multi_Select extends FW_Option_Type
{
	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => array(),
			/**
			 * Available options: array, posts, taxonomy, users
			 */
			'population' => 'array',
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
			'source' => '',
			/**
			 * An array with the available choices
			 * Used only when 'population' => 'array'
			 */
			'choices' => array( /* 'value' => 'Title' */ ),
			/**
			 * Set maximum items number that can be selected
			 */
			'limit' => 100,
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
	public static function _admin_action_get_ajax_response() {
		/**
		 * @var WPDB $wpdb
		 */
		global $wpdb;

		$type  = FW_Request::POST('data/type');
		$names = json_decode( FW_Request::POST('data/names'), true );
		$title = FW_Request::POST('data/string');

		$items = array();

		switch ($type) {
			case 'posts':
				$items = $wpdb->get_results(
					call_user_func_array(
						array($wpdb, 'prepare'),
						array_merge(
							array(
								"SELECT ID val, post_title title " .
								"FROM $wpdb->posts " .
								"WHERE post_title LIKE %s " .
								"AND post_status = 'publish' " .
								"AND NULLIF(post_password, '') IS NULL " .
								"AND post_type IN ( ".
								implode(', ', array_fill(1, count($names), '%s')) .
								" ) " .
								"LIMIT 100",
								'%'. $wpdb->esc_like($title) .'%'
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
						array($wpdb, 'prepare'),
						array_merge(
							array(
								"SELECT terms.term_id val, terms.name title " .
								"FROM $wpdb->terms as terms, $wpdb->term_taxonomy as taxonomies " .
								"WHERE terms.name LIKE %s AND taxonomies.taxonomy IN ( ".
								implode(', ', array_fill(1, count($names), '%s')) .
								" ) " .
								"AND terms.term_id = taxonomies.term_id " .
								"AND taxonomies.term_id = taxonomies.term_taxonomy_id " .
								"LIMIT 100",
								'%'. $wpdb->esc_like($title) .'%'
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
							"SELECT users.id val, users.display_name title " .
							"FROM $wpdb->users as users " .
							"WHERE users.display_name LIKE %s " .
							"LIMIT 100",
							'%'. $wpdb->esc_like($title) .'%'
						)
					);
				} else {
					$like_user_meta = array();
					foreach ($names as $name) {
						$like_user_meta[] = '%'. $wpdb->esc_like($name) .'%';
					}

					$items = $wpdb->get_results(
						call_user_func_array(
							array($wpdb, 'prepare'),
							array_merge(
								array(
									"SELECT users.id val, users.display_name title " .
									"FROM $wpdb->users as users, $wpdb->usermeta as usermeta " .
									"WHERE users.display_name LIKE %s AND usermeta.meta_key = 'wp_capabilities' " .
									"AND ( ".
									implode(' OR ', array_fill(1, count($like_user_meta), 'usermeta.meta_value LIKE %s')) .
									" ) " .
									"AND usermeta.user_id = users.ID",
									'%'. $wpdb->esc_like($title) .'%'
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
		$items      = '';
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
						/**
						 * @var WPDB $wpdb
						 */
						global $wpdb;

						$source     = is_array($option['source']) ? $option['source'] : array($option['source']);
						$population = 'posts';

						if ( empty( $data['value'] ) ) {
							break;
						}

						$ids = $data['value'];
						foreach ( $ids as $post_id ) {
							$ids[] = intval( $post_id );
						}
						$ids = implode( ', ', array_unique( $ids ) );

						//$query = new WP_Query( array( 'post__in' => $ids ) );

						$query = $wpdb->get_results(
							"SELECT posts.ID, posts.post_title " .
							"FROM $wpdb->posts as posts " .
							"WHERE posts.ID IN ( $ids )"
						);

						if ( is_wp_error( $query ) ) {
							break;
						}

						$items = array();

						foreach ( $query as $post ) {
							$items[ $post->ID ] = $post->post_title;
						}
					}
					break;
				case 'taxonomy' :
					if ( isset( $option['source'] ) ) {
						$population = 'taxonomy';
						$source     = is_array($option['source']) ? $option['source'] : array($option['source']);

						if ( empty( $data['value'] ) ) {
							break;
						}

						/**
						 * @var WPDB $wpdb
						 */
						global $wpdb;

						$ids = $data['value'];
						foreach ( $ids as $post_id ) {
							$ids[] = intval( $post_id );
						}
						$ids = implode( ', ', array_unique( $ids ) );

						$in_sources = array();
						foreach ($source as $_source) {
							$in_sources[] = $wpdb->prepare('%s', $_source);
						}
						$in_sources = implode(', ', $in_sources);

						$query = $wpdb->get_results(
							"SELECT terms.term_id id, terms.name title " .
							"FROM $wpdb->terms as terms, $wpdb->term_taxonomy as taxonomies " .
							"WHERE terms.term_id IN ( $ids ) AND taxonomies.taxonomy IN ( $in_sources ) " .
							"AND terms.term_id = taxonomies.term_id " .
							"AND taxonomies.term_id = taxonomies.term_taxonomy_id"
						);

						if ( is_wp_error( $query ) || empty( $query ) ) {
							break;
						}

						$items = array();

						foreach ( $query as $term ) {
							$items[ $term->id ] = $term->title;
						}
					}
					break;
				case 'users' :
					/**
					 * @var WPDB $wpdb
					 */
					global $wpdb;
					$population = 'users';

					if ( isset( $option['source'] ) && ! empty( $option['source'] ) ) {
						$source = is_array($option['source']) ? $option['source'] : array($option['source']);

						if ( empty( $data['value'] ) ) {
							break;
						}

						$ids = $data['value'];
						foreach ( $ids as $post_id ) {
							$ids[] = intval( $post_id );
						}
						$ids = implode( ', ', array_unique( $ids ) );

						$in_sources = array();
						foreach ($source as $_source) {
							$in_sources[] = $wpdb->prepare('usermeta.meta_value LIKE %s', '%'. $wpdb->esc_like($_source) .'%');
						}
						$in_sources = implode(' OR ', $in_sources);

						$query = $wpdb->get_results(
							"SELECT users.id, users.display_name title " .
							"FROM $wpdb->users as users, $wpdb->usermeta usermeta " .
							"WHERE users.ID IN ($ids) AND usermeta.meta_key = 'wp_capabilities' AND ( $in_sources ) " .
							"AND usermeta.user_id = users.ID"
						);

					} else {
						$source = array();

						if ( empty( $data['value'] ) ) {
							break;
						}

						$ids = $data['value'];
						foreach ( $ids as $post_id ) {
							$ids[] = intval( $post_id );
						}
						$ids = implode( ', ', array_unique( $ids ) );

						$query = $wpdb->get_results(
							"SELECT users.id, users.display_name title " .
							"FROM $wpdb->users as users " .
							"WHERE users.ID IN ($ids)"
						);
					}

					if ( is_wp_error( $query ) || empty( $query ) ) {
						break;
					}

					$items = array();

					foreach ( $query as $term ) {
						$items[ $term->id ] = $term->title;
					}

					break;
				default :
					$items = '';
			}

			$option['attr']['data-options']    = json_encode( $this->convert_array( $items ) );
			$option['attr']['data-population'] = $population;
			$option['attr']['data-source']     = json_encode( $source );
			$option['attr']['data-limit']      = ( intval( $option['limit'] ) > 0 ) ? $option['limit'] : 0;
		} else {
			return '';
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
	protected function _enqueue_static($id, $option, $data) {
		wp_enqueue_style(
			$this->get_type() . '-styles',
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/style.css'),
			array('fw-selectize'),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			$this->get_type() . '-styles',
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
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
				'title' => $item,
			);
		}

		return $return;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		$value = explode( '/*/', $input_value );

		return empty( $input_value ) ? array() : $value;
	}
}

FW_Option_Type::register( 'FW_Option_Type_Multi_Select' );

add_action( 'wp_ajax_admin_action_get_ajax_response', array( "FW_Option_Type_Multi_Select", '_admin_action_get_ajax_response' ) );
