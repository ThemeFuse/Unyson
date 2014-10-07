<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_FeedBack extends FW_Extension {

	/**
	 * Feature name for post type's, to activate the module for a post type, you must use add_post_type_support () in action 'init'. This module will be replace the default comments.
	 * http://codex.wordpress.org/Function_Reference/add_post_type_support
	 * @var string
	 */
	public $supports_feature_name = 'fw-feedback';

	/**
	 * If currently global $post accept reviews
	 * @var bool
	 */
	public $accept_feedback = false;

	public $feedback_on = true;

	/**
	 * @internal
	 */
	public function _init() {
		$this->add_actions();
		$this->add_filters();
	}

	public function add_actions() {

		/** Internal */
		{
			add_action( 'wp', array( $this, '_action_global_post_is_available' ) );
			add_action( 'wp_insert_comment', array( $this, '_action_wp_insert_comment' ), 9999, 2 );
			add_action( 'transition_comment_status', array( $this, '_action_transition_comment_status' ), 9999, 3 );
			add_action( 'init', array( $this, '_action_check_if_feedback_is_on' ), 9999 );
			add_action( 'admin_menu', array( $this, '_action_change_menu_label' ) );
		}

	}

	public function add_filters() {
		add_filter( 'preprocess_comment', array( $this, '_filter_pre_process_comment' ) );
		add_filter( 'admin_comment_types_dropdown', array( $this, '_filter_admin_comment_types_drop_down' ) );
	}

	public function _action_check_if_feedback_is_on() {
		$this->feedback_on = false;

		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, $this->supports_feature_name ) ) {
				add_post_type_support($post_type, 'comments');
				$this->feedback_on = true;
			}
		}
	}

	public function _action_change_menu_label() {
		if ( $this->feedback_on ) {
			global $menu;
			$menu[25][0] = str_replace( 'Comments', __( 'Feedback', 'fw' ), $menu[25][0] );
		}
	}

	public function _filter_pre_process_comment( $comment_data ) {
		if ( post_type_supports( get_post_type( $comment_data['comment_post_ID'] ), $this->supports_feature_name ) ) {
			$comment_data['comment_type'] = $this->supports_feature_name;
		}

		return $comment_data;
	}

	public function _filter_admin_comment_types_drop_down( $comment_types ) {

		if ( $this->feedback_on ) {
			$comment_types[ $this->supports_feature_name ] = __( 'Reviews', 'fw' );
		}

		return $comment_types;
	}

	/**
	 * Executed when global $post is available
	 */
	public function _action_global_post_is_available() {
		global $post;

		$this->accept_feedback = $post && post_type_supports( get_post_type( $post->ID ), $this->supports_feature_name );

		if ( ! ( $this->accept_feedback && $this->user_bought_product() ) ) {
			return;
		}
	}

	/**
	 * Check if user bought the current viewing product
	 */
	public function user_bought_product( $post_id = null, $user_id = null ) {
		return true;

	}

	/**
	 * Executed when new comment is posted
	 *
	 * @param int $comment_id
	 * @param object $comment
	 */
	public function _action_wp_insert_comment( $comment_id, $comment ) {

		if ( ! post_type_supports( get_post_type( $comment->comment_post_ID ), $this->supports_feature_name ) ) {
			return;
		}

		/** @var int $post_id */
		$post_id = $comment->comment_post_ID;
		/** @var int $user_id */
		$user_id = $comment->user_id;

		/** validate (decide if allow to create feedback) */
		do {
			$allow = true;

			if ( ! $this->user_bought_product( $post_id, $user_id ) ) {
				// cheater, does not bought product, but tries to post comment with injected form in html
				$allow = false;
				break;
			}

			/** to prevent the creation of responses to feedback */
			if($comment->comment_parent != 0) {
				$allow = false;
				break;
			}

		} while ( false );


		$allow = apply_filters( 'fw_ext_feedback_allow_create', $allow, array(
			'feedback_id' => $comment_id,
			'post_id'     => $post_id,
			'user_id'     => $user_id,
			'comment'     => $comment
		) );

		if ( ! $allow ) {
			// delete this comment
			wp_delete_comment( $comment_id, true );

			return;
		}

		/**
		 * remove previous comments by this user on this post, only last feedback is saved
		 * user is allowed to have only one feedback per post
		 */
		foreach (
			get_comments( array(
				'post_id' => $post_id,
				'author_email' => $comment->comment_author_email
			) ) as $comment
		) {
			/** @var object $comment */

			if ( $comment_id != $comment->comment_ID ) // do not delete current comment
			{
				wp_delete_comment( $comment->comment_ID, true );
			}
		};

		/** everything is ok, tell sub-modules to save other inputs values (feedback-stars, etc.) */

		$active = is_numeric( $comment->comment_approved ) && $comment->comment_approved == 1;

		do_action( 'fw_ext_feedback_insert', $comment_id, array(
			'active'     => $active,
			'post_id' => $post_id,
			'user_id'    => $user_id
		) );
	}

	/**
	 * When comments status changed
	 * Only two states:
	 ** active   - true
	 ** inactive - false
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $comment
	 */
	public function _action_transition_comment_status( $new_status, $old_status, $comment ) {
		if ( ! post_type_supports( get_post_type( $comment->comment_post_ID ), $this->supports_feature_name ) ) {
			return;
		}

		$active = $new_status === 'approved';

		do_action( 'fw_ext_feedback_status_changed', $active,
			array(
				'feedback_id' => $comment->comment_ID,
				'post_id'     => $comment->comment_post_ID,
			),
			array(
				'comment'            => $comment,
				'comment_status_new' => $new_status,
				'comment_status_old' => $old_status,
			)
		);
	}

}