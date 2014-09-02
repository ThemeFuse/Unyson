<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other 'pages' on your WordPress site will use a different template.
 */

$fw_ext_projects_gallery_image = fw()->extensions->get( 'portfolio' )->get_config( 'image_sizes' );
$fw_ext_projects_gallery_image = $fw_ext_projects_gallery_image['gallery-image'];

get_header(); ?>

	<div id="main-content" class="main-content">

		<?php
		if ( is_front_page() && fw_theme_has_featured_posts() ) {
			// Include the featured content template.
			get_template_part( 'featured-content' );
		}
		?>
		<div id="primary" class="content-area">
			<div id="content" class="site-content project-content" role="main">

				<?php
				// Start the Loop.
				while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<header class="entry-header">
							<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
							<?php
							if ( function_exists( 'fw_ext_breadcrumbs_render' ) && is_single() ) {
								echo fw_ext_breadcrumbs_render();
							}
							?>
						</header>
						<!-- .entry-header -->

						<div class="entry-content">
							<?php
							$thumbnails = fw_ext_portfolio_get_gallery_images();

							$captions = array();
							if ( ! empty( $thumbnails ) ) :
								?>
								<section class="wrap-nivoslider theme-default">
									<div id="slider" class="nivoslider">
										<?php foreach ( $thumbnails as $thumbnail ) :
											$attachment = get_post( $thumbnail['attachment_id'] );

											$captions[ $thumbnail['attachment_id'] ] = $attachment->post_title;

											$image = fw_resize( $thumbnail['attachment_id'], $fw_ext_projects_gallery_image['width'], $fw_ext_projects_gallery_image['height'], $fw_ext_projects_gallery_image['crop'] );
											?>
											<img src="<?php echo $image ?>"
											     class="nivoslider-image"
											     alt="<?php echo $attachment->post_title ?>"
											     title="#nivoslider-caption-<?php echo $attachment->ID ?>"
											     width="<?php echo $fw_ext_projects_gallery_image['width'] ?>"
											     height="<?php echo $fw_ext_projects_gallery_image['height'] ?>"
												/>
										<?php endforeach ?>
									</div>
									<div class="nivo-html-caption">
										<?php foreach ( $captions as $attachment_id => $post_title ) : ?>
											<div
												id="nivoslider-caption-<?php echo $attachment_id ?>"><?php echo $post_title ?></div>
										<?php endforeach ?>
									</div>
								</section>
							<?php endif ?>
							<div class="entry-content">
								<?php
								the_content();
								?>
							</div>
							<!-- .entry-content -->
						</div>
						<!-- .entry-content -->
					</article><!-- #post-## -->

				<?php endwhile; ?>

			</div>
			<!-- #content -->
		</div>
		<!-- #primary -->
		<?php get_sidebar( 'content' ); ?>
	</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
