<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );
get_header();

global $post;
$options = fw_get_db_post_option($post->ID, fw()->extensions->get( 'events' )->get_event_option_id());

?>

	<div id="main-content" class="main-content">

		<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">
				<?php
				// Start the Loop.
				while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<?php fw_theme_post_thumbnail() ?>

						<!-- .entry-header -->
						<header class="entry-header">

							<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

							<?php
							if ( function_exists( 'fw_ext_breadcrumbs_render' ) && is_single() ) {
								echo fw_ext_breadcrumbs_render();
							}
							?><!-- .entry breadcrumbs -->

							<div class="entry-meta">
								<?php
								if ( 'post' == get_post_type() )
									fw_theme_posted_on();

								if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) :
									?>
									<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'unyson' ), __( '1 Comment', 'unyson' ), __( '% Comments', 'unyson' ) ); ?></span>
								<?php
								endif;

								edit_post_link( __( 'Edit', 'unyson' ), '<span class="edit-link">', '</span>' );
								?>
								<?php
								if( function_exists('fw_ext_feedback_stars_load_view') ) {
									fw_ext_feedback_stars_load_view();
								}
								?>
							</div><!-- .entry-meta -->

						</header>
						<!-- .entry-header -->


						<div class="entry-content">

							<!-- additional information about event -->
							<hr class="before-hr"/>
							<?php foreach($options['event_children'] as $key => $row) : ?>
								<?php if (empty($row['event_date_range']['from']) or empty($row['event_date_range']['to'])) : ?>
									<?php continue; ?>
								<?php endif; ?>

								<div class="details-event-button">
									<button data-uri="<?php echo add_query_arg( array( 'row_id' => $key, 'calendar' => 'google' ), fw_current_url() ); ?>" type="button"><?php _e('Google Calendar', 'unyson') ?></button>
									<button data-uri="<?php echo add_query_arg( array( 'row_id' => $key, 'calendar' => 'ical'   ), fw_current_url() ); ?>" type="button"><?php _e('Ical Export', 'unyson') ?></button>
								</div>
								<ul class="details-event">
									<li><b><?php _e('Start', 'unyson') ?>:</b> <?php echo $row['event_date_range']['from']; ?></li>
									<li><b><?php _e('End', 'unyson') ?>:</b> <?php echo $row['event_date_range']['to']; ?></li>

									<?php if (empty($row['event-user']) === false) : ?>
									<li>
										<b><?php _e('Speakers', 'unyson') ?>:</b>
										<?php foreach($row['event-user'] as $user_id ) : ?>
											<?php $user_info = get_userdata($user_id); ?>
											<?php echo esc_html( $user_info->display_name ); ?>
											<?php echo ($user_id !== end($row['event-user']) ? ', ' : '' ); ?>
										<?php endforeach; ?>
									</li>
									<?php endif;?>

								</ul>
								<hr class="after-hr"/>
							<?php endforeach; ?>
							<!-- .additional information about event -->

							<!-- call map shortcode -->
							<?php echo fw_ext_events_render_map() ?>
							<!-- .call map shortcode -->

							<?php the_content(); ?>
						</div>
					</article>

					<?php
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				endwhile; ?>

			</div>
		</div>
		<?php get_sidebar( 'content' ); ?>

	</div>

<?php
get_sidebar();
get_footer();
