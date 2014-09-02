<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 */
?>
<div class="style-preview">
	<div class="inner">
		<?php foreach ( $option['blocks'] as $key => $block ) :
			if ( empty( $block['elements'] ) ) {
				continue;
			} ?>
			<div class="preview-block">
				<div class="<?php echo $key; ?>">
					<?php for ( $i = 1; $i <= 6; $i ++ ) :
						if ( ! in_array( 'h' . $i, $block['elements'] ) ) {
							continue;
						}
						echo '<h' . $i . '>' . $block['title'] . '(H' . $i . ')' . '</h' . $i . '>';
					endfor; ?>
					<?php if ( in_array( 'p', $block['elements'] ) ) : ?>
						<p>Mauris iaculis portititor posuef Praesent id metus massa, ut.</p>
					<?php endif; ?>
					<div class="bl-links">
						<?php if ( in_array( 'links', $block['elements'] ) ): ?>
							<a href="#" class="links">Link</a>
						<?php endif; ?>
						<?php if ( in_array( 'links_hover', $block['elements'] ) ): ?>
							<a href="#" class="links_hover">Hover Link</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="description">
		<p><?php _e( 'This is a simplified preview, not changes are reflected.', 'fw' ); ?></p>
	</div>
</div>
<?php
foreach ( $option['blocks'] as $key => $block ) {
	echo '<style type="text/css" data-block-id="' . $key . '"></style>';
}
?>
