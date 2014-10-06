<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Returns brief information about the votes on a product.
 * @param null $post
 *
 * @return mixed
 */
function fw_ext_feedback_stars_get_product_rating( $post = null ) {
	/** @var $instance FW_Extension_FeedBack_Stars */
	$instance = fw()->extensions->get( 'feedback-stars' );

	return $instance->get_product_rating( $post );
}

/**
 * Returns detailed information about the votes on a product.
 * @param null $post
 *
 * @return mixed
 */
function fw_ext_feedback_stars_get_product_detailed_rating( $post = null ) {
	/** @var $instance FW_Extension_FeedBack_Stars */
	$instance = fw()->extensions->get( 'feedback-stars' );

	return $instance->get_product_detailed_rating( $post );
}

/**
 * Loading a view that displays information about the votes allocated to a product.
 * @param null $post
 */
function fw_ext_feedback_stars_load_view( $post = null ) {
	if ( null === $post ) {
		$post = get_the_ID();
	}

	if ( ! is_numeric( $post ) ) {
		return;
	}

	/** @var $instance FW_Extension_FeedBack_Stars */
	$instance = fw()->extensions->get( 'feedback-stars' );

	$data = array(
		'stars_number' => $instance->max_rating,
		'rating'        => fw_ext_feedback_stars_get_product_detailed_rating( $post ),
	);

	echo fw_render_view( $instance->locate_view_path( 'view-rates' ), $data );
}