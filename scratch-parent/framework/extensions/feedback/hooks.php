<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * The path to the file for listing of reviews.
 * @param $theme_template
 * @return string
 */
function fw_ext_feedback_filter_change_comments_template( $theme_template ) {
	global $post;
	/** @var FW_Extension_FeedBack $ext_instance */
	$ext_instance = fw()->extensions->get( 'feedback' );

	if ( post_type_supports( $post->post_type, $ext_instance->supports_feature_name ) ) {
		$view = $ext_instance->locate_view_path('reviews');
		return ($view) ? $view : $theme_template;
	}

	return $theme_template;
}

add_filter( 'comments_template', 'fw_ext_feedback_filter_change_comments_template' );