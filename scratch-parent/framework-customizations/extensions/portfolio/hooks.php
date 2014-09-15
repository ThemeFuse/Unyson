<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Display all existing portfolio posts on the page (without pagination)
 * Because in this theme we use the https://mixitup.kunkalabs.com/ plugin to display portfolio posts
 * If your theme displays portfolio posts in a different way, feel free to change or remove this function
 * @internal
 * @param WP_Query $query
 */
function _fw_ext_portfolio_theme_action_set_posts_per_page( $query ) {
	if (!$query->is_main_query()) {
		return;
	}

	/**
	 * @var FW_Extension_Portfolio $portfolio
	 */
	$portfolio = fw()->extensions->get('portfolio');

	$is_portfolio_taxonomy = $query->is_tax( $portfolio->get_taxonomy_name() );
	$is_portfolio_archive  = $query->is_archive()
		&& isset( $query->query['post_type'] )
		&& $query->query['post_type'] == $portfolio->get_post_type_name();

	if ($is_portfolio_taxonomy || $is_portfolio_archive) {
		$query->set( 'posts_per_page', -1 );
	}
}
add_action( 'pre_get_posts', '_fw_ext_portfolio_theme_action_set_posts_per_page' );