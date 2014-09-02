<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Sitemap XML file view
 *
 * @var $items
 */
?>

<?php foreach ( $items as $item ) : ?>
	<url>
		<?php if ( isset( $item['url'] ) ) : ?>
			<loc><?php echo $item['url'] ?></loc>
		<?php endif ?>
		<?php if ( isset( $item['priority'] ) ) : ?>
			<priority><?php echo $item['priority'] ?></priority>
		<?php endif ?>
		<?php if ( isset( $item['frequency'] ) ) : ?>
			<changefreq><?php echo $item['frequency'] ?></changefreq>
		<?php endif ?>
		<?php if ( isset( $item['modified'] ) ) : ?>
			<lastmod><?php echo $item['modified'] ?></lastmod>
		<?php endif ?>
	</url>
<?php endforeach ?>