<?php

class FW_Feedback_Stars_Walker extends Walker_Comment {
	/**
	 * Output a comment in the HTML5 format.
	 *
	 * @access protected
	 * @since 3.6.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param object $comment Comment to display.
	 * @param int $depth Depth of comment.
	 * @param array $args An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		/** @var $ext_instance FW_Extension_FeedBack_Stars */
		$ext_instance = fw()->extensions->get( 'feedback-stars' );
		if ( file_exists( $ext_instance->locate_view_path( 'listing-review-html5' ) ) ) {
			echo fw_render_view( $ext_instance->locate_view_path( 'listing-review-html5' ), array(
				'comment'      => $comment,
				'depth'        => $depth,
				'args'         => $args,
				'has_children' => $this->has_children,
				'stars_number' => $ext_instance->get_config( 'stars_number' ),
				'rate'         => get_comment_meta( $comment->comment_ID, $ext_instance->field_name, true )
			) );

			return;
		};
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
		?>
		<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '' ); ?>>
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
							<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'fw' ), get_comment_date(), get_comment_time() ); ?>
						</time>
					</a>
					<?php edit_comment_link( __( 'Edit', 'fw' ), '<span class="edit-link">', '</span>' ); ?>

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

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array(
					'add_below' => 'div-comment',
					'depth'     => $depth,
					'max_depth' => $args['max_depth']
				) ) ); ?>
			</div>
			<!-- .reply -->
		</article><!-- .comment-body -->
	<?php
	}
}