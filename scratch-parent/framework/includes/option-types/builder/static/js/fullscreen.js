(function ($, fwe, _) {

	fwe.one('fw:option-type:builder:init', function (data) {
		if (!data.$elements.length && !$('#post_ID').length) {
			return;
		}

		var elements = {
			$builders: data.$elements,
			$saveButton: $('#publish'),
			$previewButton: $('#post-preview'),
			$backdrop: $('#builder-backdrop'),
			$spinner: $('#builder-backdrop').find('.spinner')
		};

		var utils = {
			toogleFullscreen: function ($builder) {
				if (!$builder.hasClass('builder-fullscreen')) {
					utils.fullscreenOn.call($builder);
				} else {
					utils.fullscreenOff.call($builder);
					utils.unsetStorageItem();
				}
			},
			getFullscreenHeight: function () {
				var $diffHeight = parseInt($('.builder-items-types').height() + 80);
				return parseInt($('body').height() - $diffHeight);
			},
			fullscreenOn: function () {
				var $builder = $(this);
				elements.$backdrop.removeClass('fw-hidden');
				$builder.addClass('builder-fullscreen');
				$builder.find('.fullscreen-btn .text').text('Exit Full Screen');
				$builder.find('.fullscreen-btn .icon').removeClass('icon-fullscreen-on').addClass('icon-fullscreen-off');
				$builder.find('.builder-root-items').css({maxHeight: utils.getFullscreenHeight() + 'px'});

			},
			fullscreenOff: function () {
				var $builder = $(this);
				elements.$backdrop.addClass('fw-hidden');
				$builder.removeClass('builder-fullscreen');
				$builder.find('.fullscreen-btn .text').text('Full Screen');
				$builder.find('.fullscreen-btn .icon').removeClass('icon-fullscreen-off').addClass('icon-fullscreen-on');
				$builder.find('.builder-root-items').css({maxHeight: ''});

			},
			getPostId: function () {
				return $('#post_ID').val();
			},
			setStorageItem: function () {
				return $.ajax(
					{
						type: "post",
						dataType: "json",
						url: ajaxurl,
						data: {
							'action': 'fw_builder_fullscreen_set_storage_item',
							'post_id': utils.getPostId()
						}
					});
			},
			unsetStorageItem: function () {
				return $.ajax(
					{
						type: "post",
						dataType: "json",
						url: ajaxurl,
						data: {
							'action': 'fw_builder_fullscreen_unset_storage_item',
							'post_id': utils.getPostId()
						}
					});
			}
		};

		elements.$backdrop.on('click', '.preview', function (e) {
			e.preventDefault();
			utils.setStorageItem();
			elements.$previewButton.trigger('click');

		});

		elements.$backdrop.one('click', '.button-primary', function (e) {
			e.preventDefault();
			elements.$spinner.show();
			utils.setStorageItem().done(function () {
				elements.$saveButton.trigger('click');
			});
		});

		elements.$builders.each(function () {
			var $builder = $(this);

			$builder.find('.fw-options-tabs-list ul').after('<div class="fullscreen-btn"><div class="icon icon-fullscreen-on"></div><div class="text">Full Screen</div></div>');

			if ($builder.hasClass('builder-fullscreen')) {
				$builder.find('.fullscreen-btn .text').text('Exit Full Screen');
				$builder.find('.fullscreen-btn .icon').removeClass('icon-fullscreen-on').addClass('icon-fullscreen-off');
				$builder.find('.builder-root-items').css({maxHeight: utils.getFullscreenHeight() + 'px'});
			}

			$builder.find('.fullscreen-btn').on('click', function (e) {
				e.preventDefault();
				utils.toogleFullscreen($builder);
			});
		});
	});
})(jQuery, fwEvents, _);

