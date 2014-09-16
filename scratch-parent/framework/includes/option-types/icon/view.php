<?php if (!defined('FW')) die('Forbidden');

/**
 * @var array $option
 * @var array $data
 * @var string $id
 */

// http://fortawesome.github.io/Font-Awesome/icons
$icon_set = array(
	array('web-app', __('Web Application Icons', 'fw'), 'fa-adjust fa-anchor fa-archive fa-asterisk fa-ban fa-bar-chart-o fa-barcode fa-bars fa-beer fa-bell fa-bell-o fa-bolt fa-book fa-bookmark fa-bookmark-o fa-briefcase fa-bug fa-building-o fa-bullhorn fa-bullseye fa-calendar fa-calendar-o fa-camera fa-camera-retro fa-certificate fa-check fa-check-circle fa-check-circle-o fa-clock-o fa-cloud fa-cloud-download fa-cloud-upload fa-code fa-code-fork fa-coffee fa-cog fa-cogs fa-comment fa-comment-o fa-comments fa-comments-o fa-compass fa-credit-card fa-crop fa-crosshairs fa-cutlery fa-desktop fa-download fa-ellipsis-h fa-ellipsis-v fa-envelope fa-envelope-o fa-exchange fa-exclamation fa-exclamation-circle fa-exclamation-triangle fa-external-link fa-external-link-square fa-eye fa-eye-slash fa-female fa-fighter-jet fa-film fa-filter fa-fire fa-fire-extinguisher fa-flag fa-flag-checkered fa-flag-o fa-flask fa-folder fa-folder-o fa-folder-open fa-folder-open-o fa-frown-o fa-gamepad fa-gavel fa-gift fa-glass fa-globe fa-hdd-o fa-headphones fa-heart fa-heart-o fa-home fa-inbox fa-info fa-info-circle fa-key fa-keyboard-o fa-laptop fa-leaf fa-lemon-o fa-level-down fa-level-up fa-lightbulb-o fa-location-arrow fa-lock fa-magic fa-magnet fa-mail-reply-all fa-male fa-map-marker fa-meh-o fa-microphone fa-microphone-slash fa-minus fa-minus-circle fa-mobile fa-moon-o fa-music fa-pencil fa-pencil-square fa-pencil-square-o fa-phone fa-phone-square fa-picture-o fa-plane fa-plus fa-plus-circle fa-power-off fa-print fa-puzzle-piece fa-qrcode fa-question fa-question-circle fa-quote-left fa-quote-right fa-random fa-refresh fa-reply fa-reply-all fa-retweet fa-road fa-rocket fa-rss fa-rss-square fa-search fa-search-minus fa-search-plus fa-share fa-share-square fa-share-square-o fa-shield fa-shopping-cart fa-sign-in fa-sign-out fa-signal fa-sitemap fa-smile-o fa-sort fa-sort-alpha-asc fa-sort-alpha-desc fa-sort-amount-asc fa-sort-amount-desc fa-sort-asc fa-sort-desc fa-sort-numeric-asc fa-sort-numeric-desc fa-spinner fa-star fa-star-half fa-star-half-o fa-star-o fa-subscript fa-suitcase fa-sun-o fa-superscript fa-tablet fa-tachometer fa-tag fa-tags fa-tasks fa-terminal fa-thumb-tack fa-thumbs-down fa-thumbs-o-down fa-thumbs-o-up fa-thumbs-up fa-ticket fa-times fa-times-circle fa-times-circle-o fa-tint fa-trash-o fa-trophy fa-truck fa-umbrella fa-unlock fa-unlock-alt fa-upload fa-user fa-users fa-video-camera fa-volume-down fa-volume-off fa-volume-up fa-wrench'),
	array('form', __('Form Control Icons', 'fw'), 'fa-check-square fa-check-square-o fa-circle fa-circle-o fa-dot-circle-o fa-minus-square fa-minus-square-o fa-plus-square fa-plus-square-o fa-square fa-square-o'),
	array('currency', __('Currency Icons', 'fw'), 'fa-btc fa-eur fa-gbp fa-inr fa-jpy fa-krw fa-money fa-rub fa-try fa-usd'),
	array('editor', __('Text Editor Icons', 'fw'), 'fa-align-center fa-align-justify fa-align-left fa-align-right fa-bold fa-chain-broken fa-clipboard fa-columns fa-eraser fa-file fa-file-o fa-file-text fa-file-text-o fa-files-o fa-floppy-o fa-font fa-indent fa-italic fa-link fa-list fa-list-alt fa-list-ol fa-list-ul fa-outdent fa-paperclip fa-repeat fa-scissors fa-strikethrough fa-table fa-text-height fa-text-width fa-th fa-th-large fa-th-list fa-underline fa-undo'),
	array('direction', __('Directional Icons', 'fw'), 'fa-angle-double-down fa-angle-double-left fa-angle-double-right fa-angle-double-up fa-angle-down fa-angle-left fa-angle-right fa-angle-up fa-arrow-circle-down fa-arrow-circle-left fa-arrow-circle-o-down fa-arrow-circle-o-left fa-arrow-circle-o-right fa-arrow-circle-o-up fa-arrow-circle-right fa-arrow-circle-up fa-arrow-down fa-arrow-left fa-arrow-right fa-arrow-up fa-arrows fa-arrows-alt fa-arrows-h fa-arrows-v fa-caret-down fa-caret-left fa-caret-right fa-caret-square-o-down fa-caret-square-o-left fa-caret-square-o-right fa-caret-square-o-up fa-caret-up fa-chevron-circle-down fa-chevron-circle-left fa-chevron-circle-right fa-chevron-circle-up fa-chevron-down fa-chevron-left fa-chevron-right fa-chevron-up fa-hand-o-down fa-hand-o-left fa-hand-o-right fa-hand-o-up fa-long-arrow-down fa-long-arrow-left fa-long-arrow-right fa-long-arrow-up'),
	array('video-player', __('Video Player Icons', 'fw'), 'fa-backward fa-compress fa-eject fa-expand fa-fast-backward fa-fast-forward fa-forward fa-pause fa-play fa-play-circle fa-play-circle-o fa-step-backward fa-step-forward fa-stop fa-youtube-play'),
	array('brand', __('Brand Icons', 'fw'), 'fa-adn fa-android fa-apple fa-bitbucket fa-bitbucket-square fa-css3 fa-dribbble fa-dropbox fa-facebook fa-facebook-square fa-flickr fa-foursquare fa-github fa-github-alt fa-github-square fa-gittip fa-google-plus fa-google-plus-square fa-html5 fa-instagram fa-linkedin fa-linkedin-square fa-linux fa-maxcdn fa-pagelines fa-pinterest fa-pinterest-square fa-renren fa-skype fa-stack-exchange fa-stack-overflow fa-trello fa-tumblr fa-tumblr-square fa-twitter fa-twitter-square fa-vimeo-square fa-vk fa-weibo fa-windows fa-xing fa-xing-square fa-youtube fa-youtube-square'),
	array('medical', __('Medical Icons', 'fw'), 'fa-ambulance fa-h-square fa-hospital-o fa-medkit fa-stethoscope fa-user-md fa-wheelchair'),
);

$wrapper_attr = array(
	'class' => $option['attr']['class'],
	'id'    => $option['attr']['id'],
);
unset($option['attr']['class'], $option['attr']['id']);

$attr = $option['attr'];
$attr['value'] = $data['value'];

?>
<div <?php echo fw_attr_to_html($wrapper_attr) ?>>
	<input <?php echo fw_attr_to_html($attr) ?> type="hidden" />

	<div class="fw-backend-option-fixed-width">
		<select data-type="dialog-icon-category">
			<option value="all"><?php echo __('All Categories', 'fw') ?></option>
			<?php foreach ($icon_set as $a): list($category, $title, $icon_string) = $a; ?>
				<option <?php if (in_array($data['value'], explode(' ', $icon_string))): ?>selected="selected"<?php endif ?> value="<?php echo esc_attr($category) ?>"><?php echo esc_html($title) ?></option>
			<?php endforeach ?>
		</select>
	</div>

	<div class="fa-lg fontawesome-icon-list">
		<?php foreach ($icon_set as $a): list ($category, $title, $icon_string) = $a; ?>
			<?php foreach (explode(' ', $icon_string) as $icon): ?>
				<i class="<?php if ($icon == $data['value']): ?>active<?php endif ?> fa <?php echo esc_attr($icon) ?> ib-if-dialog-icon-category-all ib-if-dialog-icon-category-<?php echo esc_attr($category) ?>" data-value="fa <?php echo esc_attr($icon) ?>" data-category="<?php echo esc_attr($category) ?>"></i>
			<?php endforeach ?>
		<?php endforeach ?>
	</div>
</div>
