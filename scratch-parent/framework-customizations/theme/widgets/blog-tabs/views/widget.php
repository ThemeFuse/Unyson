<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var $before_widget
 * @var $after_widget
 * @var $recent_posts
 * @var $popular_posts
 */

echo $before_widget
?>
	<div class="tabs">
		<ul>
			<li><a href="#popular_posts"><?php _e( 'Posts', 'fw' ); ?></a></li>
			<span class="separator">/</span>
			<li><a href="#most_commented"><?php _e( 'Most Commented', 'fw' ); ?></a></li>
		</ul>
	</div>
	<div id="popular_posts" class="widget_popular_posts">
		<ul>
			<?php foreach ( $recent_posts as $post ): ?>
				<li>
					<a href="<?php echo $post['post_link']; ?>"><?php echo $post['post_title']; ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div id="most_commented" class="widget_most_commented">
		<ul>
			<?php foreach ( $popular_posts as $post ): ?>
				<li>
					<a href="<?php echo $post['post_link']; ?>"><?php echo $post['post_title']; ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php echo $after_widget ?>
