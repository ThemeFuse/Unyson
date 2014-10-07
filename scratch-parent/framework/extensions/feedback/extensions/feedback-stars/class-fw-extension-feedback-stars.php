<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_FeedBack_Stars extends FW_Extension {

	public $field_name = 'fw-feedback-stars';

	public $max_rating;

	/**
	 * @internal
	 */
	public function _init() {
		$this->max_rating = ( is_numeric( $this->get_config( 'stars_number' ) ) ) ? $this->get_config( 'stars_number' ) : 5;

		if(is_admin()) {
			$this->add_admin_actions();
			$this->add_admin_filters();
		}else{
			$this->add_actions();
			$this->add_filters();
		}

	}

	private function add_actions() {

		add_action( 'comment_form_logged_in_after', array( $this, '_action_additional_fields' ) );
		add_action( 'comment_form_after_fields', array( $this, '_action_additional_fields' ) );

		add_action( 'comment_post', array( $this, '_action_save_comment_meta_data' ) );

		add_action( 'wp_footer', array( $this, '_action_show_schema' ) );
	}

	private function add_admin_actions() {
		add_action( 'admin_enqueue_scripts', array( $this, '_action_admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes_comment', array( $this, '_action_add_meta_box_edit_feedback' ) );
		add_action( 'edit_comment', array( $this, '_action_save_meta_box_edit_feedback' ) );
	}

	private function add_filters() {

		add_filter( 'comment_form_default_fields', array( $this, '_action_add_custom_fields' ) );

		// Add the filter to check if the comment meta data has been filled or not
		add_filter( 'preprocess_comment', array( $this, '_filter_verify_comment_meta_data' ) );
	}

	private function add_admin_filters() {
		add_filter( 'comment_text', array( $this, '_filter_backend_listing_add_rating' ), 9999, 3 );
	}

	/**
	 * @param $text
	 * @param $comment
	 * @param $args
	 *
	 * @return string
	 */
	public function _filter_backend_listing_add_rating( $text, $comment, $args ) {

		do {
			if ( ! $this->accept_stars( $comment->comment_post_ID ) || !is_admin()) {
				break;
			}

			if ( $rating = intval(get_comment_meta( get_comment_ID(), $this->field_name, true )) ) {
				$html = '<div class="wrap-rating back-end-listing"><span class="rating-title">' . __('Rating:', 'fw') . '</span><div class="fw-stars-rating">';
                for($i=1; $i<=$this->max_rating; $i++) {
	                $voted = ( $i <= $rating ) ? ' voted' : '';
	                $html .= '<span class="fa fa-star' . $voted . '" data-vote="' . $i . '"></span>';
                }
				$html .= '</div></div>';
				$text = $html . $text;
			}

		} while ( false );

		return $text;
	}


	public function _action_show_schema() {
		$rating = $this->get_post_rating();
		$html   = '';
		if ( intval( $rating['count'] ) > 0 ) {
			$html .= '<div itemscope itemtype="http://schema.org/Product">';
			$html .= '<meta itemprop="name" content="' . get_the_title() . '"/>';
			$html .= '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
			$html .= '<meta itemprop="ratingValue" content="' . $rating['average'] . '" />';
			$html .= '<meta itemprop="bestRating" content="' . $this->max_rating . '" />';
			$html .= '<meta itemprop="reviewCount" content="' . $rating['count'] . '" />';
			$html .= '</div>';
			$html .= '</div>';
		}

		echo $html;
	}

	/**
	 * Returns the count of votes and their average.
	 *
	 * @param null|int $post_id
	 *
	 * @return mixed
	 */
	public function get_post_rating( $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! is_numeric( $post_id ) ) {
			return false;
		}

		global $wpdb;
		$sql = "SELECT COUNT($wpdb->commentmeta.meta_value) AS count, AVG($wpdb->commentmeta.meta_value) AS average FROM $wpdb->comments INNER JOIN $wpdb->commentmeta ON $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id WHERE $wpdb->comments.comment_post_ID = %d AND $wpdb->commentmeta.meta_key = '%s' AND $wpdb->comments.comment_approved = 1";

		$result = $wpdb->get_results( $wpdb->prepare( $sql, $post_id, $this->field_name ), ARRAY_A );

		return $result[0];
	}

	public function _action_admin_enqueue_scripts( $hook ) {
		if ( $hook === 'edit-comments.php') {
			wp_enqueue_style('fw-font-awesome');
			wp_enqueue_style(
				'fw-extension-' . $this->get_name() . '-styles',
				$this->get_declared_URI( '/static/css/listing-backend-styles.css' ),
				array('fw-font-awesome', 'qtip'),
				fw()->manifest->get_version()
			);
			wp_enqueue_script( 'fw-extension-' . $this->get_name() . '-scripts', $this->locate_js_URI( 'scripts' ), array(
					'jquery',
					'qtip'
				),
				$this->manifest->get_version() );
		}elseif($hook === 'comment.php') {
			wp_enqueue_style('fw-font-awesome');
			wp_enqueue_style(
				'fw-extension-' . $this->get_name() . '-styles',
				$this->get_declared_URI( '/static/css/edit-backend-styles.css' ),
				array('fw-font-awesome', 'qtip'),
				fw()->manifest->get_version()
			);
			wp_enqueue_script( 'fw-extension-' . $this->get_name() . 'edit-scripts', $this->locate_js_URI( 'edit-feedback' ), array(
					'jquery'
				),
				$this->manifest->get_version() );
		}
	}

	public function _action_add_custom_fields( $fields ) {
		return $fields;
	}

	public function _action_additional_fields() {

		if ( ! $this->get_parent()->accept_feedback ) {
			return;
		}
		echo fw_render_view( $this->locate_view_path( 'rate' ), array(
			'stars_number' => $this->max_rating,
			'input_name'   => $this->field_name
		) );
	}

	public function _action_add_meta_box_edit_feedback( $comment ) {
		if ( $this->accept_stars( $comment->comment_post_ID ) ) {
			add_meta_box( 'title', __( 'Feedback Stars', 'fw' ), array(
				$this,
				'extend_comment_meta_box'
			), 'comment', 'normal', 'high' );
		}
	}

	/**
	 * @param object|int $comment
	 *
	 * @return bool
	 */
	private function accept_stars( $post_id ) {
		if ( post_type_supports( get_post_type( $post_id ), $this->get_parent()->supports_feature_name ) ) {
			return true;
		}

		return false;
	}

	public function extend_comment_meta_box( $comment ) {
		$rating = get_comment_meta( $comment->comment_ID, $this->field_name, true );
		wp_nonce_field( 'fw_ext_feedback_stars', 'fw_ext_feedback_stars', false );
		?>
		<!--Rating-->
		<div class="wrap-rating edit-backend">
			<span class="rating-title"><?php _e('Rating', 'fw'); ?></span>
			<div class="fw-stars-rating">
				<?php
					for($i=1; $i<=$this->max_rating; $i++) {
						$voted = ( $i <= $rating ) ? ' voted' : '';
						echo '<span class="fa fa-star' . $voted . '" data-vote="' . $i . '"></span>';
					}
				?>
			</div>
			<input type="hidden" name="<?php echo $this->field_name; ?>" value="<?php echo $rating; ?>">
		</div>
	<?php
	}

	/**
	 * Save the comment meta data along with comment
	 *
	 * @param $comment_id
	 */
	public function _action_save_meta_box_edit_feedback( $comment_id ) {
		if ( ! isset( $_POST['fw_ext_feedback_stars'] ) || ! wp_verify_nonce( $_POST['fw_ext_feedback_stars'], 'fw_ext_feedback_stars' ) ) {
			return;
		}

		if ( ( isset( $_POST[ $this->field_name ] ) ) && ( $_POST[ $this->field_name ] != '' ) ):
			$rating = wp_filter_nohtml_kses( $_POST[ $this->field_name ] );
			update_comment_meta( $comment_id, $this->field_name, $rating );
		else :
			delete_comment_meta( $comment_id, $this->field_name );
		endif;

	}

	public function _action_save_comment_meta_data( $comment_id ) {
		if ( ( isset( $_POST[ $this->field_name ] ) ) && ( $_POST[ $this->field_name ] != '' ) ) {
			$stars = wp_filter_nohtml_kses( $_POST[ $this->field_name ] );
			add_comment_meta( $comment_id, $this->field_name, $stars );
		}
	}

	public function _filter_verify_comment_meta_data( $comment_data ) {

		if ( $this->accept_stars( $comment_data['comment_post_ID'] ) && (intval( FW_Request::POST($this->field_name)) < 1) ) {
			wp_die( __( '<strong>ERROR</strong>: please rate the post.', 'fw' ) );
		}

		return $comment_data;
	}

	/**
	 * Returns the number of votes for each star.
	 *
	 * @param null|int $post_id
	 *
	 * @return mixed
	 */
	public function get_post_detailed_rating( $post_id = null ) {

		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! is_numeric( $post_id ) ) {
			return false;
		}

		global $wpdb;

		$sql = "SELECT $wpdb->commentmeta.meta_value, COUNT(*) AS total FROM $wpdb->comments INNER JOIN $wpdb->commentmeta ON $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id WHERE $wpdb->comments.comment_post_ID = %d AND $wpdb->commentmeta.meta_key = '%s' AND $wpdb->comments.comment_approved = 1 GROUP BY $wpdb->commentmeta.meta_value ORDER BY $wpdb->commentmeta.meta_value ASC";

		$result = $wpdb->get_results( $wpdb->prepare( $sql, $post_id, $this->field_name ), ARRAY_A );

		$return = $this->get_post_rating($post_id);
		$stars = array();
		for($i=$this->max_rating; $i>=1; $i--){
			$stars[$i] = array(
				'count'     => 0,
				'as_percentage'          => 0
			);
		}

		foreach($result as $star_info){
			$star = intval($star_info['meta_value']);
			$stars[$star]['count'] = intval($star_info['total']);
			$stars[$star]['as_percentage'] = ($stars[$star]['count'] * 100) / $return['count'];
		}
		$return['stars'] = $stars;
		return $return;
	}

}