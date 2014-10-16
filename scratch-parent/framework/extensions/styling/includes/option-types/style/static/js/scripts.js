/*global googleFonts */

(function ($) {

	var fwOptionTypeStyle, fwOptionTypeStylePreview;

	fwOptionTypeStyle = {
		deselectPredefined: function ($options) {
			$(document).find('.fw-option-type-style-option.predefined_styles .fw-option-type-image-picker ul li .thumbnail.selected').trigger('click');
		},

		initialize: function () {

			fwEvents.on('fw:options:init', function (data) {
				var $options = data.$elements.find('.fw-option-type-style:not(.fw-option-initialized)');

				$options.find('.settings .tabs').tabs();

				$options.addClass('fw-option-initialized');

				$options.find('.fw-option-type-style-option.predefined_styles .fw-option-type-image-picker')
					.on('fw:option-type:image-picker:clicked', function (e, data) {
						if (_.isBoolean(data.data) || !_.isObject(data.data['settings']['blocks'])) {
							return;
						}

						var $style = $(this).closest('.fw-option-type-style');
						$options.off('fw:color:picker:changed', 'input.fw-option-type-color-picker', fwOptionTypeStylePreview.fireColorChange);
						_.each(data.data['settings']['blocks'], function (blockSettings, blockId) {

							var $blockContainer = $style.find('.fw-options-tabs-wrapper.fw-option-type-style-settings .fw-options-tab[data-block="' + blockId + '"]');

							fwOptionTypeStyle.setBlockOptions($blockContainer, blockSettings);
						});
						$options.on('fw:color:picker:changed', 'input.fw-option-type-color-picker', fwOptionTypeStylePreview.fireColorChange);
					});

				if ($options.attr('data-preview')) {
					setTimeout(function () {
						fwOptionTypeStylePreview.initialize($options);
					}, 0);
				}
			});
		},

		setBlockOptions: function ($container, data) {

			fwEvents.off('fw:options:style:setBlockPreview', fwOptionTypeStylePreview.setBlockPreview);

			var typography = {
				h1: '.h1',
				h2: '.h2',
				h3: '.h3',
				h4: '.h4',
				h5: '.heading_5',
				h6: '.heading_6',
				p: 'p.block-paragraph'
			};

			var links = {
				links: '.links',
				links_hover: '.links_hover'
			};

			_.each(data, function (item, index) {
				var $element = $container.find('.fw-option-type-style-option.' + index);

				if (index === 'background') {
					fwOptionTypeStyle.setBackgroundOption($container, item);
				}
				else if ($element.length == 1) {
					if (typography.hasOwnProperty(index)) {
						fwOptionTypeStyle.setTypographyOptions($element, item);
					}
					else if (links.hasOwnProperty(index)) {
						$element.find('input.fw-option-type-color-picker.initialized').iris('color', item)
					}
				}

			});

			fwEvents.on('fw:options:style:setBlockPreview', fwOptionTypeStylePreview.setBlockPreview);

			fwEvents.trigger('fw:options:style:setBlockPreview', {
				$block: $container,
				needTimeOut: true
			});
		},

		setBackgroundOption: function ($container, data) {

			//Color
			$container.find('.fw-option-type-style-option.background-color .primary-color input.fw-option-type-color-picker.initialized').iris('color',
				data['background-color']['primary']);
			$container.find('.fw-option-type-style-option.background-color .secondary-color input.fw-option-type-color-picker.initialized').iris('color',
				data['background-color']['secondary']);

			//Image
			if (data['background-image'].hasOwnProperty('choices')) {

				var settings, smallImgAttr, selectHtml = '';
				_.each(data['background-image']['choices'], function (item, index) {
					settings = {css: item['css']};
					smallImgAttr = {src: item['icon'], height: 50};
					selectHtml += '<option data-small-img-attr=\'' + JSON.stringify(smallImgAttr) + '\' data-img-src="' + item['icon'] + '" data-extra-data=\'' + JSON.stringify(settings) + '\' value="' + index + '"></option>';
				});
				$container.find('.fw-option-type-style-option.background.background-image .fw-option-type-image-picker select:first').html(selectHtml).val(data['background-image']['value']);
				$container.find('input.background-image-data[type="hidden"]').val(JSON.stringify(data['background-image']));

				$container.find('.fw-option-type-style-option.background.background-image').find('.fw-option.fw-option-type-background-image').removeClass('initialized');
				$container.find('.fw-option-type-style-option.background.background-image').find('.fw-option.fw-option-type-image-picker').removeClass('fw-option-initialized');
				$container.find('.fw-option-type-style-option.background.background-image').find('.fw-option.fw-option-type-image-picker ul.thumbnails').remove();
				$container.find('.fw-option-type-style-option.background.background-image').find('.fw-option.fw-option-type-image-picker select:first').removeAttr('style');

				fwEvents.trigger('fw:options:init', {$elements: $container.find('.fw-option-type-style-option.background.background-image')});

				$container.find('.fw-option-type-style-option.background.background-image .type input[value="predefined"]').attr('checked', 'checked');

				$container.find('.fw-option-type-style-option.background.background-image .type').show();
				$container.find('.fw-option-type-style-option.background.background-image .custom').hide();
				$container.find('.fw-option-type-style-option.background.background-image .predefined').show();

			} else {
				$container.find('.fw-option-type-style-option.background.background-image .type input[value="custom"]').attr('checked', 'checked');

				$container.find('.fw-option-type-style-option.background.background-image .type').hide();
				$container.find('.fw-option-type-style-option.background.background-image .predefined').hide();
			}
		},

		setTypographyOptions: function ($element, data) {
			var $typography = $('.fw-option-type-style .fw-option-type-style-option.typo');
			var $sizeElement = $element.find('.fw-option-typography-option-size select[data-type="size"]');
			var $familyElement = $element.find('.fw-option-typography-option-family select[data-type="family"]');
			var $styleElement = $element.find('.fw-option-typography-option-style select[data-type="style"]');
			var $colorElement = $element.find('.fw-option-typography-option-color .fw-option-type-color-picker.initialized');

			//Size
			$sizeElement.val(data.size);

			//Family
			$typography.off('change', 'select[data-type="family"]', fwOptionTypeStylePreview.fireTypographyChange);
			$familyElement[0].selectize.setValue(data.family);
			$typography.on('change', 'select[data-type="family"]', fwOptionTypeStylePreview.fireTypographyChange);

			var html = '';
			if (googleFonts.hasOwnProperty(data.family)) {
				var font = googleFonts[data.family];
				_.each(font['variants'], function (variant) {
					html += '<option value="' + variant + '">' + fw.capitalizeFirstLetter(variant) + '</option>';
				});
			}
			else {
				html += '<option value="300">Thin</option><option value="300italic">Thin/Italic</option><option value="400" selected="selected">Normal</option><option value="italic">Italic</option><option value="700">Bold</option><option value="700italic">Bold/Italic</option>';
			}
			$styleElement.html(html).val(data.style);

			//Color
			$colorElement.iris('color', data.color);
		}
	};

	fwOptionTypeStylePreview = {

		loadedGoogleFonts: [],

		initialize: function ($options) {

			$options.find('.style-preview').each(function () {
				var $this = $(this);
				var $scrollpane = $this.find('.inner:first');

				$scrollpane.jScrollPane();
				$this.show();

				setTimeout(function () {

					$scrollpane.trigger('fw:option:style:fix-preview');
				}, 100);

				var scrollAPI = $scrollpane.data('jsp');
				var isRtl = $(document.body).hasClass('rtl');

				// fix preview position and size
				$(window).on('resize scroll fw:option:style:fix-preview', function () {
					if (!$this.is(':visible')) {
						return;
					}

					// select any input with fixed size (here we chose textarea)
					var $leftProvider = $('.fw-backend-option-type-textarea:first textarea:first');
					var $topProvider = $this.closest('.fw-postbox');

					if (!$leftProvider.length || !$topProvider.length) {
						return;
					}

					var adminBarHeight = $('#wpadminbar').height();
					var previewWidth = $this.width();
					var contentHeight = $this.find('.inner .jspPane:first').height();

					var left;
					if (isRtl) {
						left = $leftProvider.offset().left - previewWidth - 40;
					} else {
						left = $leftProvider.offset().left + $leftProvider.width() + 40;
					}

					var top = $topProvider.offset().top - adminBarHeight + 12;
					top -= $(window).scrollTop();
					top = top < 0 ? 0 : top;

					var height = $(window).height() - top - parseInt($('#wpbody-content').css('padding-bottom')) * 2;
					height = height < previewWidth ? previewWidth : height;
					height = height > contentHeight ? contentHeight : height;

					$this.css({
						'left': left + 'px',
						'top': top + 'px',
						'height': height + 'px'
					});

					scrollAPI.reinitialise();
				});

				$this.addClass('fw-hidden-xs');
			});

			$(window).trigger('fw:option:style:fix-preview');

			$options.on('fw:option-type:upload:change fw:option-type:upload:clear', function (event) {
				fwEvents.trigger('fw:options:style:setBlockPreview', {
					$block: $(event.target).closest('.fw-options-tab'),
					needTimeOut: false
				});
			});

			$options.find('.fw-option-type-style-option.background-image .fw-option-type-background-image')
				.on('fw:option-type:background-image:clicked', function (e, data) {
					fwEvents.trigger('fw:options:style:setBlockPreview', {
						$block: $(this).closest('.fw-options-tab'),
						needTimeOut: true
					});
					fwOptionTypeStyle.deselectPredefined();
				});

			//Typography changed
			$('.fw-option-type-style .fw-option-type-style-option.typo').on('change',
				'select[data-type="size"], select[data-type="style"]', function () {
					fwEvents.trigger('fw:options:style:setBlockPreview', {
						$block: $(this).closest('.fw-options-tab'),
						needTimeOut: false
					});
					fwOptionTypeStyle.deselectPredefined();
				}).on('change', 'select[data-type="family"]', fwOptionTypeStylePreview.fireTypographyChange);

			//Background Image changed
			$('.fw-option-type-style .fw-option-type-style-settings .fw-option-type-style-option.background-image').on('click',
				'.type div label', fwOptionTypeStylePreview.fireBackgroundImageTypeChange);

			//Color Picker changed
			$options.on('fw:color:picker:changed', 'input.fw-option-type-color-picker', fwOptionTypeStylePreview.fireColorChange);

			fwEvents.on('fw:options:style:setBlockPreview', fwOptionTypeStylePreview.setBlockPreview);

			$options.find('.fw-options-tab').each(function () {
				fwEvents.trigger('fw:options:style:setBlockPreview', {
					$block: $(this),
					needTimeOut: false
				});
			});
		},

		setBlockPreview: function (data) {

			var $block = data.$block,
				typoSettings,
				css = '';

			//Typography
			$block.find('.fw-option-type-style-option.typo').each(function () {
				typoSettings = fwOptionTypeStylePreview.collectTypographyOptionSettings($(this));
				css += fwOptionTypeStylePreview.generateTypographyOptionCss('.preview-block .' + $block.data('block') + ' ' + $(this).data('css-selector'),
					typoSettings);
			});

			//Links
			$block.find('.fw-option-type-style-option.link').each(function () {
				css += fwOptionTypeStylePreview.generateLinkOptionCss('.preview-block .' + $block.data('block') + ' .bl-links a.' + $(this).data('css-selector'),
					fwOptionTypeStylePreview.collectLinkOptionSettings($(this)));
			});

			//Background
			css += fwOptionTypeStylePreview.generateBackgroundCss('.preview-block .' + $block.data('block'),
				fwOptionTypeStylePreview.collectBackgroundSettings($block));

			//Apply Changes
			if (data.needTimeOut) {
				setTimeout(function () {
					$('.fw-option-type-style style[data-block-id="' + $block.data('block') + '"]').replaceWith('<style type="text/css" data-block-id="' + $block.data('block') + '">' + css + '</style>');
				}, 600);
				setTimeout(function () {
					$(window).trigger('fw:option:style:fix-preview');
				}, 800);
			}
			else {
				$('.fw-option-type-style style[data-block-id="' + $block.data('block') + '"]').replaceWith('<style type="text/css" data-block-id="' + $block.data('block') + '">' + css + '</style>');
				$(window).trigger('fw:option:style:fix-preview');
			}
			return true;
		},

		collectBackgroundSettings: function ($block) {
			var type = $block.find('.fw-option-type-style-option.background-image .fw-option-type-radio input:checked:first').val(),
				imageData = {};

			if (type !== 'predefined') {
				if ($block.find('.fw-option-type-style-option.background-image .thumb:first').attr('data-origsrc')) {
					imageData['background-image'] = 'url("' + $block.find('.fw-option-type-style-option.background-image .thumb:first').attr('data-origsrc') + '")';
				}
			}
			else {
				imageData = JSON.parse($block.find('.fw-option-type-style-option.background-image select:first option:selected').attr('data-extra-data'));
				imageData = imageData['css'];
			}
			var bgImage = {
				type: type,
				css: (typeof imageData === 'object') ? imageData : {}
			};

			var bgColor = {
				primary: ($block.find('.fw-option-type-style-option.background-color .primary-color input.fw-option-type-color-picker.initialized').length === 1) ? $block.find('.fw-option-type-style-option.background-color .primary-color input.fw-option-type-color-picker.initialized').iris('color') : $block.find('.primary-color input.fw-option-type-color-picker').val(),
				secondary: ($block.find('.fw-option-type-style-option.background-color .secondary-color input.fw-option-type-color-picker.initialized').length === 1) ? $block.find('.fw-option-type-style-option.background-color .secondary-color input.fw-option-type-color-picker.initialized').iris('color') : $block.find('.secondary-color input.fw-option-type-color-picker').val()
			};

			return {
				bgColor: bgColor,
				bgImage: bgImage
			}
		},

		generateBackgroundCss: function (cssSelector, settings) {
			var css, fallback = '', bgImageCss = '';

			css = cssSelector + '{';

			if (settings.bgImage.css.hasOwnProperty('background-image')) {
				bgImageCss += settings.bgImage.css['background-image'];
				fallback += 'background-image: ' + settings.bgImage.css['background-image'] + ';';
				if (settings.bgImage.css.hasOwnProperty('background-repeat')) {
					bgImageCss += ' ' + settings.bgImage.css['background-repeat'];
					fallback += 'background-repeat: ' + settings.bgImage.css['background-repeat'] + ';';
				}
				bgImageCss += ', ';
			}

			css += 'background-color: ' + settings.bgColor.primary + ';' + fallback;
			css += 'background: ' + bgImageCss + '-webkit-gradient(linear, left top, right top, from(' + settings.bgColor.primary + '), to(' + settings.bgColor.secondary + '));';
			css += 'background: ' + bgImageCss + '-webkit-linear-gradient(left, ' + settings.bgColor.primary + ', ' + settings.bgColor.secondary + '); ';
			css += 'background: ' + bgImageCss + '-moz-linear-gradient(left, ' + settings.bgColor.primary + ', ' + settings.bgColor.secondary + ');';
			css += 'background: ' + bgImageCss + '-ms-linear-gradient(left, ' + settings.bgColor.primary + ', ' + settings.bgColor.secondary + ');';
			css += 'background: ' + bgImageCss + '-o-linear-gradient(left, ' + settings.bgColor.primary + ', ' + settings.bgColor.secondary + ');';
			_.each(settings.bgImage.css, function (item, index) {
				if (index != 'background-image' && index != 'background-repeat') {
					css += index + ': ' + item + ';';
				}
			});
			css += '}';
			return css;
		},

		collectLinkOptionSettings: function ($element) {
			return {
				color: ($element.find('input.fw-option-type-color-picker.initialized').length === 1) ? $element.find('input.fw-option-type-color-picker.initialized').iris('color') : $element.find('input.fw-option-type-color-picker').val()
			}
		},

		generateLinkOptionCss: function (cssSelector, settings) {
			var css;

			css = cssSelector + '{color: ' + settings.color + '}';

			return css;
		},

		collectTypographyOptionSettings: function ($element) {
			var style = $element.find('select[data-type="style"]').val();

			var settings = {
				size: $element.find('select[data-type="size"]').val(),
				family: ($element.find('.fw-option-typography-option select[data-type="family"]').val()) ? $element.find('.fw-option-typography-option select[data-type="family"]').val() : $element.find('.fw-option-typography-option select[data-type="family"]').attr('data-value'),
				style: (/italic/i.test(style)) ? 'italic' : 'normal',
				weight: (parseInt(style)) ? parseInt(style) : 400,
				color: ($element.find('input.fw-option-type-color-picker.initialized').length === 1) ? $element.find('input.fw-option-type-color-picker.initialized').iris('color') : $element.find('input.fw-option-type-color-picker').val()
			};

			if (googleFonts.hasOwnProperty(settings.family) && $.inArray(settings.family,
					fwOptionTypeStylePreview.loadedGoogleFonts) === -1) {
				var variants = googleFonts[settings.family]['variants'].join(',');
				$('head').append('<link href="http://fonts.googleapis.com/css?family=' + settings.family.split(' ').join('+') + ':' + variants + '" rel="stylesheet" type="text/css">');
				fwOptionTypeStylePreview.loadedGoogleFonts.push(settings.family);
			}

			return settings;
		},

		generateTypographyOptionCss: function (cssSelector, settings) {
			var css, cssProperties = {
				size: {property: 'font-size', unit: 'px'},
				family: {property: 'font-family', unit: ''},
				style: {property: 'font-style', unit: ''},
				weight: {property: 'font-weight', unit: ''},
				color: {property: 'color', unit: ''}
			};

			css = cssSelector + '{';
			_.each(settings, function (item, index) {
				css += cssProperties[index].property + ': ' + item + cssProperties[index].unit + ';';
			});
			css += '}';

			return css;
		},

		fireTypographyChange: function (e) {
			fwEvents.trigger('fw:options:style:setBlockPreview', {
				$block: $(this).closest('.fw-options-tab'),
				needTimeOut: true
			});
			fwOptionTypeStyle.deselectPredefined();
		},

		fireBackgroundImageTypeChange: function () {
			fwEvents.trigger('fw:options:style:setBlockPreview', {
				$block: $(this).closest('.fw-options-tab'),
				needTimeOut: false
			});
			fwOptionTypeStyle.deselectPredefined();
		},

		fireColorChange: function (event, data) {
			if (data.$element.closest('.before-options').length === 1 || data.$element.closest('.after-options').length === 1) return false;
			fwEvents.trigger('fw:options:style:setBlockPreview', {
				$block: data.$element.closest('.fw-options-tab'),
				needTimeOut: false
			});
			fwOptionTypeStyle.deselectPredefined();
			return true;
		}
	};

	$(document).ready(function () {
		fwOptionTypeStyle.initialize();
	});

})(jQuery);