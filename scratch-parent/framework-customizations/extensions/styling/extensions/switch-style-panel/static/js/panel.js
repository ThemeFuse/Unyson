/*global fwGoogleFonts, fwSwitchStylePanel*/
(function ($) {

	$(".open-close-panel").click(function () {
		if ($(this).parent('.wrap-style-panel').hasClass('close')) {
			$(this).parent('.wrap-style-panel').removeClass('close').addClass('open');
		}
		else {
			$(this).parent('.wrap-style-panel').removeClass('open').addClass('close');
		}
		return false;
	});

	var switchPanel = function ($panel) {

		var blocks = JSON.parse($panel.find('ul.list-style').attr('data-blocks')),
			loadedGoogleFonts = [];

		(function init() {
			$panel.on('click', 'ul.list-style li a', applyStyle);
		})();

		function applyStyle(event) {
			var settings = $(event.target).data('settings'),
				selectors,
				css = '',
				blockSettings,
				tags = {
					typography: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'],
					links: ['links', 'links_hover']
				};

			for (var blockId in settings['blocks']) {
				if (settings['blocks'].hasOwnProperty(blockId)) {
					blockSettings = settings['blocks'][blockId];
					if (typeof blocks[blockId] === 'undefined' || typeof blocks[blockId]['elements'] !== 'object' || typeof blocks[blockId]['css_selector'] === 'undefined') {
						continue;
					}
					$.each(blockSettings, function (tag, tagSettings) {
						selectors = checkSelector(blocks[blockId]['css_selector']);
						if ($.inArray(tag, blocks[blockId]['elements']) !== -1) {
							if ($.inArray(tag, tags['typography']) !== -1) {
								css += generateTypographyCss(selectors, tag, tagSettings);
							}
							else if ($.inArray(tag, tags.links) !== -1) {
								css += generateLinksCss(selectors, tag, tagSettings);
							}
							else if (tag === 'background') {
								css += generateBackgroundCss(selectors, tagSettings);
							}
						}
					});
				}
			}
			$('style[data-rel="' + fwSwitchStylePanel['cache_key'] + '"]').remove();
			$panel.after('<style data-rel="' + fwSwitchStylePanel['cache_key'] + '" type="text/css">' + css + '</style>');
			setCookie(fwSwitchStylePanel['cache_key'], $(event.target).attr('data-key'));
			return false;
		}

		function generateTypographyCss(selectors, tag, options) {
			var css = '', style, weight, variants;

			$.each(selectors, function (index, selector) {
				css += selector + ' ' + tag + '{';
				if (typeof options['size'] === 'number') {
					css += 'font-size: ' + options['size'] + 'px;'
				}
				if (typeof options['color'] === 'string' && isValidColor(options['color'])) {
					css += 'color: ' + options['color'] + ';'
				}
				if (typeof options['style'] === 'string') {
					style = (/italic/i.test(options['style'])) ? 'italic' : 'normal';
					weight = (parseInt(options['style'])) ? parseInt(options['style']) : '400';
					css += 'font-style: ' + style + ';' + 'font-weight: ' + weight + ';';
				}
				if (typeof options['family'] === 'string') {
					if (fwGoogleFonts.hasOwnProperty(options['family']) && $.inArray(options['family'],
							loadedGoogleFonts) === -1) {
						variants = fwGoogleFonts[options['family']]['variants'].join(',');
						$('head').append('<link href="http://fonts.googleapis.com/css?family=' + options['family'].split(' ').join('+') + ':' + variants + '" rel="stylesheet" type="text/css">');
						loadedGoogleFonts.push(options['family']);
					}
					css += 'font-family: ' + options['family'];
				}
				css += '}';
			});

			return css;
		}

		function generateLinksCss(selectors, tag, color) {
			var css = '';

			tag = (tag === 'links') ? 'a' : 'a:hover';

			$.each(selectors, function (index, selector) {
				if (typeof color === 'string' && isValidColor(color)) {
					css += selector + ' ' + tag + '{';
					css += 'color: ' + color + ';';
					css += '}';
				}
			});

			return css;
		}

		function generateBackgroundCss(selectors, options) {
			var css = '', fallback = '', bgImageCss = '';
			$.each(selectors, function (index, selector) {
				css += selector + '{';
				if (options['background-image']['choices'][options['background-image']['value']]['css'].hasOwnProperty('background-image')) {
					bgImageCss += options['background-image']['choices'][options['background-image']['value']]['css']['background-image'];
					fallback += 'background-image: ' + options['background-image']['choices'][options['background-image']['value']]['css']['background-image'] + ';';
					if (options['background-image']['choices'][options['background-image']['value']]['css'].hasOwnProperty('background-repeat')) {
						bgImageCss += ' ' + options['background-image']['choices'][options['background-image']['value']]['css']['background-repeat'];
						fallback += 'background-repeat: ' + options['background-image']['choices'][options['background-image']['value']]['css']['background-repeat'] + ';';
					}
					bgImageCss += ', ';
				}

				css += 'background-color: ' + options['background-color']['primary'] + ';' + fallback;
				css += 'background: ' + bgImageCss + '-webkit-gradient(linear, left top, right top, from(' + options['background-color']['primary'] + '), to(' + options['background-color']['secondary'] + '));';
				css += 'background: ' + bgImageCss + '-webkit-linear-gradient(left, ' + options['background-color']['primary'] + ', ' + options['background-color']['secondary'] + '); ';
				css += 'background: ' + bgImageCss + '-moz-linear-gradient(left, ' + options['background-color']['primary'] + ', ' + options['background-color']['secondary'] + ');';
				css += 'background: ' + bgImageCss + '-ms-linear-gradient(left, ' + options['background-color']['primary'] + ', ' + options['background-color']['secondary'] + ');';
				css += 'background: ' + bgImageCss + '-o-linear-gradient(left, ' + options['background-color']['primary'] + ', ' + options['background-color']['secondary'] + ');';

				for (var i in options['background-image']['choices'][options['background-image']['value']]['css']) {
					if (!options['background-image']['choices'][options['background-image']['value']]['css'].hasOwnProperty(i)) {
						continue;
					}
					if (i !== 'background-image' && index !== 'background-repeat') {
						css += i + ': ' + options['background-image']['choices'][options['background-image']['value']]['css'][i] + ';';
					}
				}
				css += '}';
			});

			return css;
		}

		function setCookie(c_name, value) {
			var exdays = 365, exdate = new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value = value + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString() + "; path=/;");
			document.cookie = c_name + "=" + c_value;
		}

		function checkSelector(selector) {
			if (typeof selector === 'string') {
				selector = [ selector ]
			}
			return selector;
		}

		function isValidColor(str) {
			return str.match(/^#[a-f0-9]{6}$/i) !== null;
		}
	};

	switchPanel($('.wrap-style-panel'));

})(jQuery);
