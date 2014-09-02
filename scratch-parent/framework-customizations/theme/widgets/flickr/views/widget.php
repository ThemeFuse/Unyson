<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var $number
 * @var $before_widget
 * @var $after_widget
 * @var $title
 * @var $flickr_id
 */

echo $before_widget;
echo $title;
?>
	<div class="wrap-flickr">
		<ul>
			<script type="text/javascript"
			        src="http://www.flickr.com/badge_code_v2.gne?count=<?php echo $number; ?>&amp;display=random&amp;size=s&amp;layout=x&amp;source=user&amp;user=<?php echo $flickr_id; ?>"></script>
		</ul>
	</div>
<?php echo $after_widget ?>