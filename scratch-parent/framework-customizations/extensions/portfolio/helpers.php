<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

function fw_ext_portfolio_get_gallery_images( $post_id = 0 ) {
	if ( 0 === $post_id && null === ( $post_id = get_the_ID() ) ) {
		return array();
	}

	$options = get_post_meta( $post_id, 'fw_options', true );

	return isset( $options['project-gallery'] ) ? $options['project-gallery'] : array();
}

/*
 * @return array of terms
 */
function fw_ext_portfolio_get_listing_categories( $term_ids, $taxonomy ) {

	$args = array(
		'hide_empty'    => false
	);

	if ( is_numeric( $term_ids ) ) {
		$args['parent'] = $term_ids;
	} elseif ( is_array( $term_ids ) ) {
		$args['include'] = $term_ids;
	}

	$categories = get_terms( $taxonomy, $args );

	if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {

		if ( count( $categories ) === 1 ) {
			$categories = array_values( $categories );
			$categories = get_terms( $taxonomy, array( 'parent' => $categories[0]->term_id, 'hide_empty' => false ) );
		}

		foreach ( $categories as $key => $category ) {
			$children = get_term_children( $category->term_id, $taxonomy );
			$categories[ $key ]->children = $children;

			//remove empty categories
			if(($category->count == 0) && (is_wp_error($children) || empty($children))) {
				unset($categories[$key]);
			}
		}

		return $categories;
	}

	return  array();
}

function fw_ext_portfolio_get_sort_classes( $items, $taxonomy, $categories, $prefix = 'category_' ) {

	$classes            = array();
	$categories_classes = array();
	foreach ( $items as $key => $item ) {
		$class_name = '';
		$terms      = wp_get_post_terms( $item->ID, $taxonomy );

		foreach ( $terms as $term ) {
			foreach ( $categories as $category ) {
				if ( $term->term_id == $category->term_id ) {
					$class_name .= $prefix . $category->term_id . ' ';
					$categories_classes[ $term->term_id ] = true;
				} else {
					if ( in_array( $term->term_id, $category->children, true ) ) {
						$class_name .= $prefix . $category->term_id . ' ';
						$categories_classes[ $term->term_id ] = true;
					}
				}
				$classes[ $item->ID ] = $class_name;
			}
		}
	}

	return $classes;
}
