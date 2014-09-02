<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var $instance
 * @var $before_widget
 * @var $after_widget
 * @var $title
 */


?>
<?php if ( ! empty( $instance ) ) : ?>
	<?php echo $before_widget ?>
		<div class="wrap-social">
			<?php echo $title; ?>
			<ul>
				<?php foreach ( $instance as $key => $value ) :
					if ( empty( $value ) ) {
						continue;
					}
					?>
					<li>
						<a href="<?php echo esc_attr( $value ); ?>" class="btn-share" target="_blank">
							<i class="fa-<?php echo $key ?>"></i>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php echo $after_widget ?>
<?php endif; ?>