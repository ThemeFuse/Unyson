<?php
/**
 * The template used for displaying page content
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
	<?php
		// Page thumbnail and title.
		fw_theme_post_thumbnail();
		the_title( '<h1 class="entry-title">', '</h1>' );
	?>
	<?php
	if( !is_front_page() && function_exists('fw_ext_breadcrumbs_render') && is_page() ) {
		echo fw_ext_breadcrumbs_render();
	}
	?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
		the_content();
			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'unyson' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
			) );

			edit_post_link( __( 'Edit', 'unyson' ), '<span class="edit-link">', '</span>' );
		?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->
