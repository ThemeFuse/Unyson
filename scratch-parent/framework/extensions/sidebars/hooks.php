<?php
/**
 * filter add ability to WP_Query to find out posts by title
 */
add_filter( 'posts_where', 'title_like_posts_where',10,2);
function title_like_posts_where( $where, &$wp_query ) {
		global $wpdb;
		if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\'';
		}
		return $where;
}
