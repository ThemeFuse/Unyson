<?php
/**
 * The Sidebar containing the main widget area
 */
?>
<div id="secondary">
	<?php
		$description = get_bloginfo( 'description', 'display' );
		if ( ! empty ( $description ) ) :
	?>
	<h2 class="site-description"><?php echo esc_html( $description ); ?></h2>
	<?php endif; ?>

	<?php if ( has_nav_menu( 'secondary' ) ) : ?>
	<nav role="navigation" class="navigation site-navigation secondary-navigation">
		<?php wp_nav_menu( array( 'theme_location' => 'secondary' ) ); ?>
	</nav>
	<?php endif; ?>

	<div id="primary-sidebar" class="primary-sidebar widget-area" role="complementary">
		<?php $current_position = fw_ext_sidebars_get_current_position()?>
		<?php if ( $current_position !== 'full' ) : ?>
			<?php if ($current_position === 'left' or $current_position === 'left_right' ) : ?>
				<?php echo fw_ext_sidebars_show('blue'); ?>
			<?php endif; ?>
		<?php endif; ?>
	</div><!-- #primary-sidebar -->
</div><!-- #secondary -->
