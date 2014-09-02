<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$loop_data = get_query_var( 'fw_portfolio_loop_data' );

$thumbnails_params = $loop_data['image_sizes']['featured-image'];
?>
<li class="mix category_all <?php echo ( ! empty( $loop_data['listing_classes'][ get_the_ID() ] ) ) ? $loop_data['listing_classes'][ get_the_ID() ] : ''; ?> portfolio-item">
	<div class="portfolio-img">
		<a href="<?php the_permalink() ?>">
			<?php
			$thumbnail_id = get_post_thumbnail_id();
			if( !empty( $thumbnail_id ) ) {
				$thumbnail    = get_post( $thumbnail_id );
				$image        = fw_resize( $thumbnail->ID, $thumbnails_params['width'], $thumbnails_params['height'], $thumbnails_params['crop'] );
				$thumbnail_title = $thumbnail->post_title;
			} else {
				$image = fw()->extensions->get('portfolio')->locate_URI('/static/img/no-photo.jpg');
				$thumbnail_title = $image;
			}
			?>
			<img src="<?php echo $image ?>" alt="<?php echo $thumbnail_title ?>"
			     width="<?php echo $thumbnails_params['width'] ?>" height="<?php echo $thumbnails_params['height'] ?>"/>
		</a>
		<h4 class="title-item"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h4>

	</div>
</li>