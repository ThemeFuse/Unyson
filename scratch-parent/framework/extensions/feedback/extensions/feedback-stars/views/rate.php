<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Display the stars in the form of introduction of review.
 * @var int $stars_number
 * @var string $input_name
 */
?>
<!--Rating-->
<div class="wrap-rating in-post">
	<span class="rating-title"><?php _e( 'Rating', 'unyson' ); ?><sup>*</sup></span>

	<div class="rating">
		<?php
		for ( $i = 1; $i <= $stars_number; $i ++ ) {
			echo '<span class="fa fa-star" data-vote="' . $i . '"></span>';
		}
		?>
	</div>
	<input type="hidden" name="<?php echo $input_name; ?>" id="rate" value="">
</div>
<!--/Rating-->