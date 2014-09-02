/*
 * Dependency jQuery Sortable
 * */

(function ($) {

	$.fn.slides = function (options) {
		var extensionPath =fw.FW_URI+'/extensions/media/extensions/population-method/extensions/population-method-custom/includes/slides/static/images/';
		var defaults = {
				'mediaImg': extensionPath+'no_video.jpg',
				'noDataImg': extensionPath+'no_img.jpg',
				'imageExtensions': ['jpeg', 'jpg', 'png', 'gif', 'bmp'],
				'addSlideSelector': 'fw-add-slide',
				'editSlideSelector': '.fw-edit-slide',
				'addSlideEvent': 'fw:add:new:slide',
				'editSlideEvent': 'fw:edit:slide'
			},
			settings = $.extend({}, defaults, options);

		return this.each(function () {
			var $this = $(this),
				elements = {
					$optionWrappper: $this,
					$addButton: $this.find('.fw-add-slide'),
					$slidesWrapper: $this.find('.fw-slides-wrapper'),
					$spinner :$this.parents('.postbox').find('.fw-slide-spinner')
				},
				templates = {
					settings: {
						evaluate: /\{\{(.+?)\}\}/g,
						interpolate: /\{\{=(.+?)\}\}/g,
						escape: /\{\{-(.+?)\}\}/g
					},
					thumb: $(slides_templates).filter('.default-thumb').html(),
					slide: $(slides_templates).filter('.default-slide').html()
				},
				utils = {
					getSlideElement: function (slideNumber) {
						return elements.$optionWrappper.find('.slide-' + slideNumber);
					},
					initQtip: function ($elements) {
						$elements.each(function () {
							$(this).qtip({
								content: {
									text :'Click to edit / Drag to reorder'
								},
								position: {
									at: 'top center',
									my: 'bottom center',
									viewport: jQuery('body')
								},
								style: {
									classes: 'qtip-fw qtip-fw-slides',
									tip: {
										width: 12,
										height: 5
									}
								}
							});
						});

					},
					appendDefaultSlide: function (slideNumber) {
						var $compiled = $(_.template(
							$.trim(templates.slide),
							{i: slideNumber},
							templates.settings
						)).hide();

						$default = elements.$slidesWrapper.find('.default');

						if ($default.length > 0) {
							$default.slideUp(500, function () {
								elements.$slidesWrapper.children().removeClass('default').hide();
								elements.$slidesWrapper.append($compiled);
								fwEvents.trigger('fw:options:init', {$elements: $compiled});
							});
						} else {
							elements.$slidesWrapper.append($compiled);
							fwEvents.trigger('fw:options:init', {$elements: $compiled});
						}
					},
					revealSlide: function (slideNumber) {
						elements.$slidesWrapper.find('.fw-slide').hide();
						utils.getSlideElement(slideNumber).slideDown();//show();
					},
					replaceSlide: function (slideNumber) {
						var $cachedSlide = cache.getCachedSlide(slideNumber).clone();

						elements.$slidesWrapper.find('.slide-' + slideNumber).remove();
						elements.$slidesWrapper.append($cachedSlide);
						fwEvents.trigger('fw:options:init', {$elements: $cachedSlide});
					},
					getDefaultThumb: function (data) {
						var $defaultThumb = $(
							_.template(
								$.trim(templates.thumb),
								{src: data['src'], i: data['order_id']},
								{
									evaluate: /\{\{(.+?)\}\}/g,
									interpolate: /\{\{=(.+?)\}\}/g,
									escape: /\{\{-(.+?)\}\}/g
								}
							)
						);
						utils.initQtip($defaultThumb);
						return $defaultThumb;
					},
					cancelEditMode: function () {
						this.hideControlButtons();
						this.deselectThumbs();
						this.deselectSlides();
					},
					hideControlButtons: function () {
						elements.$optionWrappper.find('.buttons-wrapper').hide();
					},
					showControlButtons: function () {
						$buttonsWrapper = elements.$optionWrappper.find('.buttons-wrapper');
						if ($buttonsWrapper.is(':hidden')) {
							$buttonsWrapper.show();
						}
					},
					showEditButtons: function () {
						utils.showControlButtons();
						elements.$optionWrappper.find('.edit-buttons').show();
						elements.$addButton.hide();
					},
					showAddButton: function () {
						utils.showControlButtons();
						elements.$addButton.show();
						elements.$optionWrappper.find('.edit-buttons').hide();
					},
					deselectThumbs: function () {
						elements.$optionWrappper.find('li').removeClass('selected');
						elements.$optionWrappper.find('.add-new-btn').removeClass('new-selected');
					},
					deselectSlides: function () {
						elements.$optionWrappper.find('.fw-slide').hide();
					},
					getThumbSrcFromOption: function ($option) {

						var $multimedia_type = $option.find('.picker-group :radio:checked').val(),
							$group = $option.find('.choice-group.chosen'),
							$inputValue = $group.find(':input').val(),
							$thumbSrc = $group.find('.thumb[data-attid="' + $inputValue + '"] img');

						if ($thumbSrc.length === 0) {
							$thumbSrc = $inputValue;
						} else {
							$thumbSrc = $group.find('.thumb').attr('data-origsrc');
						}

						return {'src': $thumbSrc, 'multimedia_type': $multimedia_type};
					},
					initSortable: function () {
						elements.$optionWrappper.find('.thumbs-wrapper').sortable({
							items: "li:not(.sortable-false)"
						});
					},
					initSlides: function () {
						utils.hideControlButtons();
						utils.appendDefaultSlide(slidesNumber);
						utils.initSortable();

						var thumbs = elements.$optionWrappper.find('.thumbs-wrapper li:not(.sortable-false)');
						utils.initQtip(thumbs);
						$.each(thumbs, function () {
							var slideNumber = $(this).data('order');
							$innerHtml = elements.$slidesWrapper.find('.slide-' + slideNumber).data('default-html');
							$outer = $('<div class="fw-slide slide-' + slideNumber + '" data-order="' + slideNumber + '">' + $innerHtml + '</div>');
							cache.slides['slide-' + slideNumber] = $outer;
						});
					}
				},
				validator = {
					parseValidUrl: function (src) {
						//REGEX FROM https://gist.github.com/dperini/729294
						var regex = /^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/i;
						var regExp = new RegExp(regex);
						return regExp.test(src);
					},
					parseYouTubeSrc: function (src) {
						// REGEX FROM http://stackoverflow.com/a/9102270
						var regex = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
						var matches = src.match(regex);
						if (matches && matches[2].length == 11) {
							return matches[2];
						}
						return false;
					},
					getYoutubeImgSrc: function (youtubeId) {
						// http://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
						return 'http://img.youtube.com/vi/' + youtubeId + '/mqdefault.jpg';
					},
					parseImgSrc: function (src) {
						return (-1 !== $.inArray(src.split('.').pop().toLowerCase(), settings.imageExtensions));
					},
					parseVimeoSrc: function (src) {
						//REGEX FROM http://stackoverflow.com/a/2916654
						var regex = /http:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/;
						var regex = /https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/

						var matches = src.match(regex);
						if (matches) {
							return matches[3];
						}
						return false;
					},
					getVimeoImgSrc: function (eventName, data) {

						$.ajax({
							type: 'GET',
							url: 'http://vimeo.com/api/v2/video/' + data['src'] + '.json',
							jsonp: 'callback',
							dataType: 'jsonp'
						}).done(function (response) {
							data['src'] = response[0].thumbnail_medium;
							validator.triggerEvent(eventName, data);
						}).fail(function () {
							data['src'] = settings.mediaImg;
							validator.triggerEvent(eventName, data);
						});
					},
					triggerEvent: function (event, data) {
						elements.$optionWrappper.trigger(event, data);
					},
					validateSrc: function (eventName, data) {
						var isMediaImg = true;
						var src = data['src'];
						if (data['multimedia_type'] === 'image' && data['src'] !== '') {

							var thumbSize = JSON.stringify(elements.$optionWrappper.data('option').option.thumb_size);

							$.ajax({
								type: "post",
								dataType: "json",
								url: ajaxurl,
								data: {
									'action': 'resize_slide',
									'src': data['src'],
									'thumb_size': thumbSize
								}
							}).done(function (response) {
								data['src'] = response.src;
								validator.triggerEvent(eventName, data);
							}).fail(function () {
								data['src'] = settings.mediaImg;
								validator.triggerEvent(eventName, data);
							});

							return;
						}
						if (data['src'] === '' || data['src'] === undefined) {
							data['src'] = data.multimedia_type === 'image' ? settings.noDataImg : settings.mediaImg;

							this.triggerEvent(eventName, data);
							return;
						}
						if (this.parseValidUrl(src)) {
							if (this.parseYouTubeSrc(src) !== false) {
								isMediaImg = false;
								data['src'] = this.getYoutubeImgSrc(this.parseYouTubeSrc(data['src']));
								this.triggerEvent(eventName, data);
							}
							if (this.parseVimeoSrc(src) !== false) {
								isMediaImg = false;
								data['src'] = this.parseVimeoSrc(data['src']);
								this.getVimeoImgSrc(eventName, data);
							}

							if (this.parseImgSrc(src)) {
								isMediaImg = false;
								this.triggerEvent(eventName, data);
							}

							if (isMediaImg) {
								data['src'] = settings.mediaImg;
								this.triggerEvent(eventName, data);
							}

						} else {
							data['src'] = settings.noDataImg;
							this.triggerEvent(eventName, data);
						}
					}
				},
				cache = {
					slides: {},
					cacheSlide: function (slideNumber) {

						var serializedData = elements.$optionWrappper.find('.slide-' + slideNumber + ' :input').serialize();
						var optionSlides = JSON.stringify(elements.$optionWrappper.data('option'));

						$.ajax({
							type: "post",
							dataType: "json",
							url: ajaxurl,
							data: {
								'action': 'cache_slide',
								'option': optionSlides,
								'values': serializedData
							}
						}).done(function (response) {
							cache.slides['slide-' + slideNumber] = $(response);
						});
					},
					getCachedSlide: function (slideNumber) {
						return this.slides['slide-' + slideNumber];
					}
				}

			slidesNumber = elements.$optionWrappper.find('.thumbs-wrapper li').length;

			utils.initSlides();

			elements.$addButton.on('click', function (e) {
				e.preventDefault();
				cache.cacheSlide(slidesNumber);
				elements.$spinner.css({'visibility' : 'visible'});
				var mediaSrc = utils.getThumbSrcFromOption(utils.getSlideElement(slidesNumber)),
					data = {src: mediaSrc['src'], order_id: slidesNumber, 'multimedia_type': mediaSrc['multimedia_type']};

				slidesNumber++;
				utils.appendDefaultSlide(slidesNumber);
				utils.hideControlButtons();
				validator.validateSrc(settings.addSlideEvent, data);
			});

			elements.$optionWrappper.on(settings.addSlideEvent, function (e, data) {
				elements.$optionWrappper.find('.thumbs-wrapper .add-new-btn').before(utils.getDefaultThumb(data));
				utils.deselectThumbs();
				elements.$spinner.css({visibility : 'hidden'});
			});

			elements.$optionWrappper.on(settings.editSlideEvent, function (e, data) {
				elements.$optionWrappper.find('.thumbs-wrapper li[data-order="' + data['order_id'] + '"]').replaceWith(utils.getDefaultThumb(data));
				elements.$spinner.css({visibility : 'hidden'});
			});

			elements.$optionWrappper.on('click', '.thumbs-wrapper li:not(.sortable-false)', function (e) {
				e.preventDefault();
				if (!$(this).hasClass('selected')) {
					var lastSelectedThumb = elements.$optionWrappper.find('.thumbs-wrapper li.selected');

					if (lastSelectedThumb.length > 0) {
						utils.replaceSlide(lastSelectedThumb.data('order'));
					}

					utils.deselectThumbs();
					utils.revealSlide($(this).data('order'));
					$(this).addClass('selected');
					utils.showEditButtons();
				}
			});

			elements.$optionWrappper.on('click', '.fw-edit-slide', function (e) {
				e.preventDefault();
				var slideNumber = elements.$optionWrappper.find('.thumbs-wrapper li.selected').data('order'),
					mediaSrc = utils.getThumbSrcFromOption(utils.getSlideElement(slideNumber));

				cache.cacheSlide(slideNumber);
				elements.$spinner.css({'visibility' : 'visible'});
				utils.getSlideElement(slideNumber).slideUp(500, function () {
					validator.validateSrc(settings.editSlideEvent, {'src': mediaSrc['src'], 'order_id': slideNumber, 'multimedia_type': mediaSrc['multimedia_type']});
					utils.cancelEditMode();
				});

			});

			elements.$optionWrappper.on('click', '.fw-cancel-edit', function (e) {
				e.preventDefault();
				var slideNumber = elements.$optionWrappper.find('.thumbs-wrapper li.selected').data('order');

				utils.getSlideElement(slideNumber).slideUp(500, function () {
					utils.replaceSlide(slideNumber);
					utils.cancelEditMode();
					utils.hideControlButtons();
				});

			});

			elements.$optionWrappper.on('click', '.delete-btn', function (e) {
				var $selectedThumb = $(this).closest('li'),
					slideNumber = $selectedThumb.data('order');

				e.preventDefault();
				e.stopPropagation();
				utils.cancelEditMode();

				$selectedThumb.qtip('destroy');
				$selectedThumb.remove();
				utils.getSlideElement(slideNumber).remove();
			});

			elements.$optionWrappper.on('click', '.add-new-btn', function (e) {
				if (!$(this).hasClass('new-selected')) {
					utils.deselectSlides();
					elements.$optionWrappper.find('li').removeClass('selected');
					elements.$optionWrappper.find('.add-new-btn').addClass('new-selected');
					elements.$optionWrappper.find('.fw-slide.default').slideDown();
					utils.showAddButton();
				}
			})
		});
	};

})(jQuery);

jQuery(document).ready(function () {
	fwEvents.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-slides:not(.fw-option-initialized)')
			.slides()
			.addClass('fw-option-initialized');
	});
});
