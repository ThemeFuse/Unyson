<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Output a review in the HTML5 format.
 *
 * @var object $comment Comment to display.
 * @var int $depth Depth of comment.
 * @var array $args An array of arguments.
 * @var bool $has_children
 * @var int $stars_number
 * @var int $rate
 */
$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
?>
<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $has_children ? 'parent' : '' ); ?>>
<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
	<footer class="comment-meta">
		<div class="comment-author vcard">
			<?php if ( 0 != $args['avatar_size'] ) {
				echo get_avatar( $comment, $args['avatar_size'] );
			} ?>
			<?php printf( __( '%s <span class="says">says:</span>' ), sprintf( '<b class="fn">%s</b>', get_comment_author_link() ) ); ?>
		</div>
		<!-- .comment-author -->

		<div class="comment-metadata">
			<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID, $args ) ); ?>">
				<time datetime="<?php comment_time( 'c' ); ?>">
					<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'fw'), get_comment_date(), get_comment_time() ); ?>
				</time>
			</a>
			<?php edit_comment_link( __( 'Edit', 'fw' ), '<span class="edit-link">', '</span>' ); ?>
			<!--Rating-->
			<div class="wrap-rating listing">
				<div class="rating">
					<?php
					for ( $i = 1; $i <= $stars_number; $i ++ ) {
						$voted = ( $i <= round( $rate ) ) ? ' voted' : '';
						echo '<span class="fa fa-star' . $voted . '" data-vote="' . $i . '"></span>';
					}
					?>
				</div>
			</div>
			<!--/Rating-->
		</div>
		<!-- .comment-metadata -->

		<?php if ( '0' == $comment->comment_approved ) : ?>
			<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'fw' ); ?></p>
		<?php endif; ?>
	</footer>
	<!-- .comment-meta -->

	<div class="comment-content">
		<?php comment_text(); ?>
	</div>
	<!-- .comment-content -->

</article><!-- .comment-body -->