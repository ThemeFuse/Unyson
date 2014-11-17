<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Icon extends FW_Option_Type
{
	/**
	 * Prevent enqueue same font style twice, in case it is used in multiple sets
	 * @var array
	 */
	private $enqueued_font_styles = array();

	public function get_type()
	{
		return 'icon';
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'full';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-type-'. $this->get_type() .'-backend',
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/backend.css'),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-type-'. $this->get_type() .'-backend',
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/backend.js'),
			array('jquery', 'fw-events'),
			fw()->manifest->get_version()
		);

		$sets = $this->get_sets();

		if (isset($sets[ $option['set'] ])) {
			$set = $sets[ $option['set'] ];

			unset($sets);

			/**
			 * user hash as array key instead of src, because src can be a very long data-url string
			 */
			$style_hash = md5($set['font-style-src']);

			if (!isset($this->enqueued_font_styles[ $style_hash ])) {
				wp_enqueue_style(
					"fw-option-type-{$this->get_type()}-font-{$option['set']}",
					$set['font-style-src'],
					array(),
					fw()->manifest->get_version()
				);

				$this->enqueued_font_styles[ $style_hash ] = true;
			}
		}
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$sets = $this->get_sets();

		if (isset($sets[ $option['set'] ])) {
			$set = $sets[ $option['set'] ];
		} else {
			$set = $this->generate_unknown_set($data['value']);
		}

		unset($sets);

		$option['attr']['value'] = (string)$data['value'];

		return fw_render_view(dirname(__FILE__) . '/view.php', compact('id', 'option', 'data', 'set'));
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		$sets = $this->get_sets();

		if (isset($sets[ $option['set'] ])) {
			$set = $sets[ $option['set'] ];
		} else {
			$set = $this->generate_unknown_set($input_value);
		}

		unset($sets);

		if (is_null($input_value) || !isset($set['icons'][ $input_value ])) {
			$input_value = $option['value'];
		}

		return (string)$input_value;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => '',
			'set'   => 'font-awesome',
		);
	}

	private function get_sets()
	{
		$cache_key = 'fw_option_type_icon/sets';

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$sets = apply_filters('fw_option_type_icon_sets', $this->get_default_sets());

			// do not allow overwrite default sets
			$sets = array_merge($sets, $this->get_default_sets());

			FW_Cache::set($cache_key, $sets);

			return $sets;
		}
	}

	private function generate_unknown_set($icon)
	{
		return array(
			'font-style-src'  => 'data:text/css;charset=utf-8;base64,LyoqLw==',
			'container-class' => '',
			'groups' => array(
				'unknown' => __('Unknown Set', 'fw'),
			),
			'icons' => array(
				$icon => array('group' => 'unknown'),
			),
		);
	}

	private function get_default_sets()
	{
		return array(
			'font-awesome' => array( // http://fortawesome.github.io/Font-Awesome/icons
				'font-style-src' => fw_get_framework_directory_uri('/static/libs/font-awesome/css/font-awesome.min.css'),
				'container-class' => 'fa-lg', // some fonts need special wrapper class to display properly
				'groups' => array(
					'web-app' => __('Web Application Icons', 'fw'),
					'form' => __('Form Control Icons', 'fw'),
					'currency' => __('Currency Icons', 'fw'),
					'editor' => __('Text Editor Icons', 'fw'),
					'direction' => __('Directional Icons', 'fw'),
					'video-player' => __('Video Player Icons', 'fw'),
					'brand' => __('Brand Icons', 'fw'),
					'medical' => __('Medical Icons', 'fw'),
				),
				'icons' => array(
					// Web Application Icons
					'fa fa-adjust' => array('group' => 'web-app'),
					'fa fa-anchor' => array('group' => 'web-app'),
					'fa fa-archive' => array('group' => 'web-app'),
					'fa fa-asterisk' => array('group' => 'web-app'),
					'fa fa-ban' => array('group' => 'web-app'),
					'fa fa-bar-chart-o' => array('group' => 'web-app'),
					'fa fa-barcode' => array('group' => 'web-app'),
					'fa fa-bars' => array('group' => 'web-app'),
					'fa fa-beer' => array('group' => 'web-app'),
					'fa fa-bell' => array('group' => 'web-app'),
					'fa fa-bell-o' => array('group' => 'web-app'),
					'fa fa-bolt' => array('group' => 'web-app'),
					'fa fa-book' => array('group' => 'web-app'),
					'fa fa-bookmark' => array('group' => 'web-app'),
					'fa fa-bookmark-o' => array('group' => 'web-app'),
					'fa fa-briefcase' => array('group' => 'web-app'),
					'fa fa-bug' => array('group' => 'web-app'),
					'fa fa-building-o' => array('group' => 'web-app'),
					'fa fa-bullhorn' => array('group' => 'web-app'),
					'fa fa-bullseye' => array('group' => 'web-app'),
					'fa fa-calendar' => array('group' => 'web-app'),
					'fa fa-calendar-o' => array('group' => 'web-app'),
					'fa fa-camera' => array('group' => 'web-app'),
					'fa fa-camera-retro' => array('group' => 'web-app'),
					'fa fa-certificate' => array('group' => 'web-app'),
					'fa fa-check' => array('group' => 'web-app'),
					'fa fa-check-circle' => array('group' => 'web-app'),
					'fa fa-check-circle-o' => array('group' => 'web-app'),
					'fa fa-clock-o' => array('group' => 'web-app'),
					'fa fa-cloud' => array('group' => 'web-app'),
					'fa fa-cloud-download' => array('group' => 'web-app'),
					'fa fa-cloud-upload' => array('group' => 'web-app'),
					'fa fa-code' => array('group' => 'web-app'),
					'fa fa-code-fork' => array('group' => 'web-app'),
					'fa fa-coffee' => array('group' => 'web-app'),
					'fa fa-cog' => array('group' => 'web-app'),
					'fa fa-cogs' => array('group' => 'web-app'),
					'fa fa-comment' => array('group' => 'web-app'),
					'fa fa-comment-o' => array('group' => 'web-app'),
					'fa fa-comments' => array('group' => 'web-app'),
					'fa fa-comments-o' => array('group' => 'web-app'),
					'fa fa-compass' => array('group' => 'web-app'),
					'fa fa-credit-card' => array('group' => 'web-app'),
					'fa fa-crop' => array('group' => 'web-app'),
					'fa fa-crosshairs' => array('group' => 'web-app'),
					'fa fa-cutlery' => array('group' => 'web-app'),
					'fa fa-desktop' => array('group' => 'web-app'),
					'fa fa-download' => array('group' => 'web-app'),
					'fa fa-ellipsis-h' => array('group' => 'web-app'),
					'fa fa-ellipsis-v' => array('group' => 'web-app'),
					'fa fa-envelope' => array('group' => 'web-app'),
					'fa fa-envelope-o' => array('group' => 'web-app'),
					'fa fa-exchange' => array('group' => 'web-app'),
					'fa fa-exclamation' => array('group' => 'web-app'),
					'fa fa-exclamation-circle' => array('group' => 'web-app'),
					'fa fa-exclamation-triangle' => array('group' => 'web-app'),
					'fa fa-external-link' => array('group' => 'web-app'),
					'fa fa-external-link-square' => array('group' => 'web-app'),
					'fa fa-eye' => array('group' => 'web-app'),
					'fa fa-eye-slash' => array('group' => 'web-app'),
					'fa fa-female' => array('group' => 'web-app'),
					'fa fa-fighter-jet' => array('group' => 'web-app'),
					'fa fa-film' => array('group' => 'web-app'),
					'fa fa-filter' => array('group' => 'web-app'),
					'fa fa-fire' => array('group' => 'web-app'),
					'fa fa-fire-extinguisher' => array('group' => 'web-app'),
					'fa fa-flag' => array('group' => 'web-app'),
					'fa fa-flag-checkered' => array('group' => 'web-app'),
					'fa fa-flag-o' => array('group' => 'web-app'),
					'fa fa-flask' => array('group' => 'web-app'),
					'fa fa-folder' => array('group' => 'web-app'),
					'fa fa-folder-o' => array('group' => 'web-app'),
					'fa fa-folder-open' => array('group' => 'web-app'),
					'fa fa-folder-open-o' => array('group' => 'web-app'),
					'fa fa-frown-o' => array('group' => 'web-app'),
					'fa fa-gamepad' => array('group' => 'web-app'),
					'fa fa-gavel' => array('group' => 'web-app'),
					'fa fa-gift' => array('group' => 'web-app'),
					'fa fa-glass' => array('group' => 'web-app'),
					'fa fa-globe' => array('group' => 'web-app'),
					'fa fa-hdd-o' => array('group' => 'web-app'),
					'fa fa-headphones' => array('group' => 'web-app'),
					'fa fa-heart' => array('group' => 'web-app'),
					'fa fa-heart-o' => array('group' => 'web-app'),
					'fa fa-home' => array('group' => 'web-app'),
					'fa fa-inbox' => array('group' => 'web-app'),
					'fa fa-info' => array('group' => 'web-app'),
					'fa fa-info-circle' => array('group' => 'web-app'),
					'fa fa-key' => array('group' => 'web-app'),
					'fa fa-keyboard-o' => array('group' => 'web-app'),
					'fa fa-laptop' => array('group' => 'web-app'),
					'fa fa-leaf' => array('group' => 'web-app'),
					'fa fa-lemon-o' => array('group' => 'web-app'),
					'fa fa-level-down' => array('group' => 'web-app'),
					'fa fa-level-up' => array('group' => 'web-app'),
					'fa fa-lightbulb-o' => array('group' => 'web-app'),
					'fa fa-location-arrow' => array('group' => 'web-app'),
					'fa fa-lock' => array('group' => 'web-app'),
					'fa fa-magic' => array('group' => 'web-app'),
					'fa fa-magnet' => array('group' => 'web-app'),
					'fa fa-mail-reply-all' => array('group' => 'web-app'),
					'fa fa-male' => array('group' => 'web-app'),
					'fa fa-map-marker' => array('group' => 'web-app'),
					'fa fa-meh-o' => array('group' => 'web-app'),
					'fa fa-microphone' => array('group' => 'web-app'),
					'fa fa-microphone-slash' => array('group' => 'web-app'),
					'fa fa-minus' => array('group' => 'web-app'),
					'fa fa-minus-circle' => array('group' => 'web-app'),
					'fa fa-mobile' => array('group' => 'web-app'),
					'fa fa-moon-o' => array('group' => 'web-app'),
					'fa fa-music' => array('group' => 'web-app'),
					'fa fa-pencil' => array('group' => 'web-app'),
					'fa fa-pencil-square' => array('group' => 'web-app'),
					'fa fa-pencil-square-o' => array('group' => 'web-app'),
					'fa fa-phone' => array('group' => 'web-app'),
					'fa fa-phone-square' => array('group' => 'web-app'),
					'fa fa-picture-o' => array('group' => 'web-app'),
					'fa fa-plane' => array('group' => 'web-app'),
					'fa fa-plus' => array('group' => 'web-app'),
					'fa fa-plus-circle' => array('group' => 'web-app'),
					'fa fa-power-off' => array('group' => 'web-app'),
					'fa fa-print' => array('group' => 'web-app'),
					'fa fa-puzzle-piece' => array('group' => 'web-app'),
					'fa fa-qrcode' => array('group' => 'web-app'),
					'fa fa-question' => array('group' => 'web-app'),
					'fa fa-question-circle' => array('group' => 'web-app'),
					'fa fa-quote-left' => array('group' => 'web-app'),
					'fa fa-quote-right' => array('group' => 'web-app'),
					'fa fa-random' => array('group' => 'web-app'),
					'fa fa-refresh' => array('group' => 'web-app'),
					'fa fa-reply' => array('group' => 'web-app'),
					'fa fa-reply-all' => array('group' => 'web-app'),
					'fa fa-retweet' => array('group' => 'web-app'),
					'fa fa-road' => array('group' => 'web-app'),
					'fa fa-rocket' => array('group' => 'web-app'),
					'fa fa-rss' => array('group' => 'web-app'),
					'fa fa-rss-square' => array('group' => 'web-app'),
					'fa fa-search' => array('group' => 'web-app'),
					'fa fa-search-minus' => array('group' => 'web-app'),
					'fa fa-search-plus' => array('group' => 'web-app'),
					'fa fa-share' => array('group' => 'web-app'),
					'fa fa-share-square' => array('group' => 'web-app'),
					'fa fa-share-square-o' => array('group' => 'web-app'),
					'fa fa-shield' => array('group' => 'web-app'),
					'fa fa-shopping-cart' => array('group' => 'web-app'),
					'fa fa-sign-in' => array('group' => 'web-app'),
					'fa fa-sign-out' => array('group' => 'web-app'),
					'fa fa-signal' => array('group' => 'web-app'),
					'fa fa-sitemap' => array('group' => 'web-app'),
					'fa fa-smile-o' => array('group' => 'web-app'),
					'fa fa-sort' => array('group' => 'web-app'),
					'fa fa-sort-alpha-asc' => array('group' => 'web-app'),
					'fa fa-sort-alpha-desc' => array('group' => 'web-app'),
					'fa fa-sort-amount-asc' => array('group' => 'web-app'),
					'fa fa-sort-amount-desc' => array('group' => 'web-app'),
					'fa fa-sort-asc' => array('group' => 'web-app'),
					'fa fa-sort-desc' => array('group' => 'web-app'),
					'fa fa-sort-numeric-asc' => array('group' => 'web-app'),
					'fa fa-sort-numeric-desc' => array('group' => 'web-app'),
					'fa fa-spinner' => array('group' => 'web-app'),
					'fa fa-star' => array('group' => 'web-app'),
					'fa fa-star-half' => array('group' => 'web-app'),
					'fa fa-star-half-o' => array('group' => 'web-app'),
					'fa fa-star-o' => array('group' => 'web-app'),
					'fa fa-subscript' => array('group' => 'web-app'),
					'fa fa-suitcase' => array('group' => 'web-app'),
					'fa fa-sun-o' => array('group' => 'web-app'),
					'fa fa-superscript' => array('group' => 'web-app'),
					'fa fa-tablet' => array('group' => 'web-app'),
					'fa fa-tachometer' => array('group' => 'web-app'),
					'fa fa-tag' => array('group' => 'web-app'),
					'fa fa-tags' => array('group' => 'web-app'),
					'fa fa-tasks' => array('group' => 'web-app'),
					'fa fa-terminal' => array('group' => 'web-app'),
					'fa fa-thumb-tack' => array('group' => 'web-app'),
					'fa fa-thumbs-down' => array('group' => 'web-app'),
					'fa fa-thumbs-o-down' => array('group' => 'web-app'),
					'fa fa-thumbs-o-up' => array('group' => 'web-app'),
					'fa fa-thumbs-up' => array('group' => 'web-app'),
					'fa fa-ticket' => array('group' => 'web-app'),
					'fa fa-times' => array('group' => 'web-app'),
					'fa fa-times-circle' => array('group' => 'web-app'),
					'fa fa-times-circle-o' => array('group' => 'web-app'),
					'fa fa-tint' => array('group' => 'web-app'),
					'fa fa-trash-o' => array('group' => 'web-app'),
					'fa fa-trophy' => array('group' => 'web-app'),
					'fa fa-truck' => array('group' => 'web-app'),
					'fa fa-umbrella' => array('group' => 'web-app'),
					'fa fa-unlock' => array('group' => 'web-app'),
					'fa fa-unlock-alt' => array('group' => 'web-app'),
					'fa fa-upload' => array('group' => 'web-app'),
					'fa fa-user' => array('group' => 'web-app'),
					'fa fa-users' => array('group' => 'web-app'),
					'fa fa-video-camera' => array('group' => 'web-app'),
					'fa fa-volume-down' => array('group' => 'web-app'),
					'fa fa-volume-off' => array('group' => 'web-app'),
					'fa fa-volume-up' => array('group' => 'web-app'),
					'fa fa-wrench' => array('group' => 'web-app'),

					// Form Control Icons
					'fa fa-check-square' => array('group' => 'form'),
					'fa fa-check-square-o' => array('group' => 'form'),
					'fa fa-circle' => array('group' => 'form'),
					'fa fa-circle-o' => array('group' => 'form'),
					'fa fa-dot-circle-o' => array('group' => 'form'),
					'fa fa-minus-square' => array('group' => 'form'),
					'fa fa-minus-square-o' => array('group' => 'form'),
					'fa fa-plus-square' => array('group' => 'form'),
					'fa fa-plus-square-o' => array('group' => 'form'),
					'fa fa-square' => array('group' => 'form'),
					'fa fa-square-o' => array('group' => 'form'),

					// Currency Icons
					'fa fa-btc' => array('group' => 'currency'),
					'fa fa-eur' => array('group' => 'currency'),
					'fa fa-gbp' => array('group' => 'currency'),
					'fa fa-inr' => array('group' => 'currency'),
					'fa fa-jpy' => array('group' => 'currency'),
					'fa fa-krw' => array('group' => 'currency'),
					'fa fa-money' => array('group' => 'currency'),
					'fa fa-rub' => array('group' => 'currency'),
					'fa fa-try' => array('group' => 'currency'),
					'fa fa-usd' => array('group' => 'currency'),

					// Text Editor Icons
					'fa fa-align-center' => array('group' => 'editor'),
					'fa fa-align-justify' => array('group' => 'editor'),
					'fa fa-align-left' => array('group' => 'editor'),
					'fa fa-align-right' => array('group' => 'editor'),
					'fa fa-bold' => array('group' => 'editor'),
					'fa fa-chain-broken' => array('group' => 'editor'),
					'fa fa-clipboard' => array('group' => 'editor'),
					'fa fa-columns' => array('group' => 'editor'),
					'fa fa-eraser' => array('group' => 'editor'),
					'fa fa-file' => array('group' => 'editor'),
					'fa fa-file-o' => array('group' => 'editor'),
					'fa fa-file-text' => array('group' => 'editor'),
					'fa fa-file-text-o' => array('group' => 'editor'),
					'fa fa-files-o' => array('group' => 'editor'),
					'fa fa-floppy-o' => array('group' => 'editor'),
					'fa fa-font' => array('group' => 'editor'),
					'fa fa-indent' => array('group' => 'editor'),
					'fa fa-italic' => array('group' => 'editor'),
					'fa fa-link' => array('group' => 'editor'),
					'fa fa-list' => array('group' => 'editor'),
					'fa fa-list-alt' => array('group' => 'editor'),
					'fa fa-list-ol' => array('group' => 'editor'),
					'fa fa-list-ul' => array('group' => 'editor'),
					'fa fa-outdent' => array('group' => 'editor'),
					'fa fa-paperclip' => array('group' => 'editor'),
					'fa fa-repeat' => array('group' => 'editor'),
					'fa fa-scissors' => array('group' => 'editor'),
					'fa fa-strikethrough' => array('group' => 'editor'),
					'fa fa-table' => array('group' => 'editor'),
					'fa fa-text-height' => array('group' => 'editor'),
					'fa fa-text-width' => array('group' => 'editor'),
					'fa fa-th' => array('group' => 'editor'),
					'fa fa-th-large' => array('group' => 'editor'),
					'fa fa-th-list' => array('group' => 'editor'),
					'fa fa-underline' => array('group' => 'editor'),
					'fa fa-undo' => array('group' => 'editor'),

					// Directional Icons
					'fa fa-angle-double-down' => array('group' => 'direction'),
					'fa fa-angle-double-left' => array('group' => 'direction'),
					'fa fa-angle-double-right' => array('group' => 'direction'),
					'fa fa-angle-double-up' => array('group' => 'direction'),
					'fa fa-angle-down' => array('group' => 'direction'),
					'fa fa-angle-left' => array('group' => 'direction'),
					'fa fa-angle-right' => array('group' => 'direction'),
					'fa fa-angle-up' => array('group' => 'direction'),
					'fa fa-arrow-circle-down' => array('group' => 'direction'),
					'fa fa-arrow-circle-left' => array('group' => 'direction'),
					'fa fa-arrow-circle-o-down' => array('group' => 'direction'),
					'fa fa-arrow-circle-o-left' => array('group' => 'direction'),
					'fa fa-arrow-circle-o-right' => array('group' => 'direction'),
					'fa fa-arrow-circle-o-up' => array('group' => 'direction'),
					'fa fa-arrow-circle-right' => array('group' => 'direction'),
					'fa fa-arrow-circle-up' => array('group' => 'direction'),
					'fa fa-arrow-down' => array('group' => 'direction'),
					'fa fa-arrow-left' => array('group' => 'direction'),
					'fa fa-arrow-right' => array('group' => 'direction'),
					'fa fa-arrow-up' => array('group' => 'direction'),
					'fa fa-arrows' => array('group' => 'direction'),
					'fa fa-arrows-alt' => array('group' => 'direction'),
					'fa fa-arrows-h' => array('group' => 'direction'),
					'fa fa-arrows-v' => array('group' => 'direction'),
					'fa fa-caret-down' => array('group' => 'direction'),
					'fa fa-caret-left' => array('group' => 'direction'),
					'fa fa-caret-right' => array('group' => 'direction'),
					'fa fa-caret-square-o-down' => array('group' => 'direction'),
					'fa fa-caret-square-o-left' => array('group' => 'direction'),
					'fa fa-caret-square-o-right' => array('group' => 'direction'),
					'fa fa-caret-square-o-up' => array('group' => 'direction'),
					'fa fa-caret-up' => array('group' => 'direction'),
					'fa fa-chevron-circle-down' => array('group' => 'direction'),
					'fa fa-chevron-circle-left' => array('group' => 'direction'),
					'fa fa-chevron-circle-right' => array('group' => 'direction'),
					'fa fa-chevron-circle-up' => array('group' => 'direction'),
					'fa fa-chevron-down' => array('group' => 'direction'),
					'fa fa-chevron-left' => array('group' => 'direction'),
					'fa fa-chevron-right' => array('group' => 'direction'),
					'fa fa-chevron-up' => array('group' => 'direction'),
					'fa fa-hand-o-down' => array('group' => 'direction'),
					'fa fa-hand-o-left' => array('group' => 'direction'),
					'fa fa-hand-o-right' => array('group' => 'direction'),
					'fa fa-hand-o-up' => array('group' => 'direction'),
					'fa fa-long-arrow-down' => array('group' => 'direction'),
					'fa fa-long-arrow-left' => array('group' => 'direction'),
					'fa fa-long-arrow-right' => array('group' => 'direction'),
					'fa fa-long-arrow-up' => array('group' => 'direction'),

					// Video Player Icons
					'fa fa-backward' => array('group' => 'video-player'),
					'fa fa-compress' => array('group' => 'video-player'),
					'fa fa-eject' => array('group' => 'video-player'),
					'fa fa-expand' => array('group' => 'video-player'),
					'fa fa-fast-backward' => array('group' => 'video-player'),
					'fa fa-fast-forward' => array('group' => 'video-player'),
					'fa fa-forward' => array('group' => 'video-player'),
					'fa fa-pause' => array('group' => 'video-player'),
					'fa fa-play' => array('group' => 'video-player'),
					'fa fa-play-circle' => array('group' => 'video-player'),
					'fa fa-play-circle-o' => array('group' => 'video-player'),
					'fa fa-step-backward' => array('group' => 'video-player'),
					'fa fa-step-forward' => array('group' => 'video-player'),
					'fa fa-stop' => array('group' => 'video-player'),
					'fa fa-youtube-play' => array('group' => 'video-player'),

					// Brand Icons
					'fa fa-adn' => array('group' => 'brand'),
					'fa fa-android' => array('group' => 'brand'),
					'fa fa-apple' => array('group' => 'brand'),
					'fa fa-bitbucket' => array('group' => 'brand'),
					'fa fa-bitbucket-square' => array('group' => 'brand'),
					'fa fa-css3' => array('group' => 'brand'),
					'fa fa-dribbble' => array('group' => 'brand'),
					'fa fa-dropbox' => array('group' => 'brand'),
					'fa fa-facebook' => array('group' => 'brand'),
					'fa fa-facebook-square' => array('group' => 'brand'),
					'fa fa-flickr' => array('group' => 'brand'),
					'fa fa-foursquare' => array('group' => 'brand'),
					'fa fa-github' => array('group' => 'brand'),
					'fa fa-github-alt' => array('group' => 'brand'),
					'fa fa-github-square' => array('group' => 'brand'),
					'fa fa-gittip' => array('group' => 'brand'),
					'fa fa-google-plus' => array('group' => 'brand'),
					'fa fa-google-plus-square' => array('group' => 'brand'),
					'fa fa-html5' => array('group' => 'brand'),
					'fa fa-instagram' => array('group' => 'brand'),
					'fa fa-linkedin' => array('group' => 'brand'),
					'fa fa-linkedin-square' => array('group' => 'brand'),
					'fa fa-linux' => array('group' => 'brand'),
					'fa fa-maxcdn' => array('group' => 'brand'),
					'fa fa-pagelines' => array('group' => 'brand'),
					'fa fa-pinterest' => array('group' => 'brand'),
					'fa fa-pinterest-square' => array('group' => 'brand'),
					'fa fa-renren' => array('group' => 'brand'),
					'fa fa-skype' => array('group' => 'brand'),
					'fa fa-stack-exchange' => array('group' => 'brand'),
					'fa fa-stack-overflow' => array('group' => 'brand'),
					'fa fa-trello' => array('group' => 'brand'),
					'fa fa-tumblr' => array('group' => 'brand'),
					'fa fa-tumblr-square' => array('group' => 'brand'),
					'fa fa-twitter' => array('group' => 'brand'),
					'fa fa-twitter-square' => array('group' => 'brand'),
					'fa fa-vimeo-square' => array('group' => 'brand'),
					'fa fa-vk' => array('group' => 'brand'),
					'fa fa-weibo' => array('group' => 'brand'),
					'fa fa-windows' => array('group' => 'brand'),
					'fa fa-xing' => array('group' => 'brand'),
					'fa fa-xing-square' => array('group' => 'brand'),
					'fa fa-youtube' => array('group' => 'brand'),
					'fa fa-youtube-square' => array('group' => 'brand'),

					// Medical Icons
					'fa fa-ambulance' => array('group' => 'medical'),
					'fa fa-h-square' => array('group' => 'medical'),
					'fa fa-hospital-o' => array('group' => 'medical'),
					'fa fa-medkit' => array('group' => 'medical'),
					'fa fa-stethoscope' => array('group' => 'medical'),
					'fa fa-user-md' => array('group' => 'medical'),
					'fa fa-wheelchair' => array('group' => 'medical'),
				),
			),
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Icon');
