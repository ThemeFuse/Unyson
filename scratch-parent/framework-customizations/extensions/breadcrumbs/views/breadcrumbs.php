<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Breadcrumbs default view
 * Parameters:
 *
 * @var array $items , array with pages ordered hierarchical
 * $items = array(
 *      0 => array(
 *          'name'  => 'Item name',
 *          'url'   => 'Item URL'
 *      )
 * )
 * Each $items array will contain additional information about item, e.g.:
 * 'items' => array (
 *        0 => array (
 *            'name' => 'Homepage',
 *          'type' => 'front_page',
 *            'url' => 'http://yourdomain.com/',
 *        ),
 *        1 => array (
 *            'type' => 'taxonomy',
 *            'name' => 'Uncategorized',
 *            'id' => 1,
 *            'url' => 'http://yourdomain.com/category/uncategorized/',
 *            'taxonomy' => 'category',
 *        ),
 *        2 => array (
 *            'name' => 'Post Article',
 *            'id' => 4781,
 *            'post_type' => 'post',
 *            'type' => 'post',
 *            'url' => 'http://yourdomain.com/post-article/',
 *        ),
 *    ),
 * @var string $separator , the separator symbol
 */
?>

<?php if ( ! empty( $items ) ) : ?>
	<div class="breadcrumbs">
		<?php for ( $i = 0; $i < count( $items ); $i ++ ) : ?>
			<?php if ( $i == ( count( $items ) - 1 ) ) : ?>
				<span class="last-item"><?php echo $items[ $i ]['name'] ?></span>
			<?php elseif ( $i == 0 ) : ?>
				<span class="first-item">
				<?php if( isset( $items[ $i ]['url'] ) ) : ?>
					<a href="<?php echo $items[ $i ]['url'] ?>"><?php echo $items[ $i ]['name'] ?></a></span>
				<?php else : echo $items[ $i ]['name']; endif ?>
				<span class="separator"><?php echo $separator ?></span>
			<?php
			else : ?>
				<span class="<?php echo( $i - 1 ) ?>-item">
					<?php if( isset( $items[ $i ]['url'] ) ) : ?>
						<a href="<?php echo $items[ $i ]['url'] ?>"><?php echo $items[ $i ]['name'] ?></a></span>
					<?php else : echo $items[ $i ]['name']; endif ?>
				<span class="separator"><?php echo $separator ?></span>
			<?php endif ?>
		<?php endfor ?>
	</div>
<?php endif ?>