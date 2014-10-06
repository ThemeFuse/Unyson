<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Get shortcode [map] html string
 * @param int $post_id
 * @return null | string
 */
if ( ! function_exists( 'fw_ext_events_render_map' ) ) {
	function fw_ext_events_render_map( $post_id = 0 ) {
		if ( 0 === $post_id && null === ( $post_id = get_the_ID() ) ) {
			return null;
		}

		$fw_ext_events = fw()->extensions->get( 'events' );
		if (empty($fw_ext_events)) {
			return null;
		}

		$post = get_post($post_id);
		if ($post->post_type !== $fw_ext_events->get_post_type_name() ) {
			return null;
		}

		$shortcode_map = fw()->extensions->get('shortcodes')->get_shortcode('map');
		if (empty($shortcode_map)) {
			return null;
		}

		$options = fw_get_db_post_option($post->ID, $fw_ext_events->get_event_option_id());

		if (empty($options['event_location']['location']) or empty($options['event_location']['coordinates']['lat']) or empty($options['event_location']['coordinates']['lng']) ) {
			return null;
		}

		return $shortcode_map->render_custom(
			array(
				array(
					'title' =>  $post->post_title,
					'url'   =>  get_permalink($post->ID),
					'description' => $options['event_location']['location'],
					'thumb'       => array('attachment_id' => get_post_thumbnail_id( $post->ID ) ),
					'location' => array(
						'coordinates' => array(
							'lat' => $options['event_location']['coordinates']['lat'],
							'lng' => $options['event_location']['coordinates']['lng']
						)
					)
				)
			),
			array(
				'map_height' => false,
				'map_type'   => false
			)
		);
	}
}