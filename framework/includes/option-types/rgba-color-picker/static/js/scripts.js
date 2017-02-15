jQuery(function($){
	/**
	 * fixme: maybe make these simple functions and do not clog Color.prototype
	 */
	{
		Color.prototype.toString = function (remove_alpha) {
			if (remove_alpha == 'no-alpha') {
				return this.toCSS('rgba', '1').replace(/\s+/g, '');
			}
			if (this._alpha < 1) {
				return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
			}
			var hex = parseInt(this._color, 10).toString(16);
			if (this.error) return '';
			if (hex.length < 6) {
				for (var i = 6 - hex.length - 1; i >= 0; i--) {
					hex = '0' + hex;
				}
			}
			return '#' + hex;
		};

		Color.prototype.toHex = function () {
			var hex = parseInt(this._color, 10).toString(16);
			if (this.error) return '';
			if (hex.length < 6) {
				for (var i = 6 - hex.length - 1; i >= 0; i--) {
					hex = '0' + hex;
				}
			}
			return '#' + hex;
		};
	}

	var helpers = {
		optionClass: 'fw-option-type-rgba-color-picker',
		eventNamespace: '.fwOptionTypeRgbaColorPicker',
		hexColorRegex: /^#([a-f0-9]{3}){1,2}$/i,
		localized: window._fw_option_type_rgba_color_picker_localized,
		increment: 0,
		isColorDark: function(rgbaColor) {
			var r, g, b, o = 1;

			if (this.hexColorRegex.test(rgbaColor)) {
				var color = rgbaColor.substring(1); // remove #

				r = parseInt(color.substr(0,2),16);
				g = parseInt(color.substr(2,2),16);
				b = parseInt(color.substr(4,2),16);
			} else {
				var rgba = rgbaColor
						.replace(/^(rgb|rgba)\(/, '')
						.replace(/\)$/, '')
						.replace(/\s/g, '')
						.split(',');

				r = rgba[0];
				g = rgba[1];
				b = rgba[2];
				o = rgba[3];
			}

			var yiq = ((r*299)+(g*587)+(b*114))/1000;

			return yiq < 128 && o > 0.4;
		},
		isColorValid: function(rgbaColor) {
			return !Color(rgbaColor).error;
		},
		getInstance: function ($iris) {
			return $iris.data('a8cIris');
		},
		updatePreview: function ($input, color) {
			if (this.isColorValid(color)) {
				$input.css({
					'background-color': color,
					'color': this.isColorDark(color) ? '#FFFFFF' : '#000000'
				});
			} else {
				$input.css({
					'background-color': '',
					'color': ''
				});
			}
		}
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('input.'+ helpers.optionClass +':not(.initialized)').each(function () {
			var $input = $(this),
				changeTimeoutId = 0,
				eventNamespace = helpers.eventNamespace +'_'+ (++helpers.increment);

			/**
			 * Improvement: Initialized picker only on first focus
			 * Do not initialize all pickers on the page, for performance reasons, maybe none of them will be opened
			 */
			$input.one('focus', function(){
				if (!$.trim($input.val()).length) { // If the input value is empty, there a glitches with opacity slider
					$input.val('rgba(255,255,255,1)');
				}

				$input.iris({
					palettes: JSON.parse($input.attr('data-palettes')),
					defaultColor: false,
					change: function (event, ui) {
						var $transparency = $input.next('.iris-picker').find('.transparency');
						$transparency.css('backgroundColor', ui.color.toString('no-alpha'));

						$alphaSlider.slider("option", "value", ui.color._alpha * 100);

						clearTimeout(changeTimeoutId);
						changeTimeoutId = setTimeout(function(){
							$input.trigger('fw:option-type:rgba-color-picker:change', {
								$element: $input,
								iris: $input.data('a8cIris'),
								alphaSlider: $alphaSlider.data('uiSlider')
							});
							$input.trigger('change');
						}, 12);
					}
				});

				var $picker = helpers.getInstance($input).picker;

				$picker.addClass(helpers.optionClass +'-iris');

				/**
				 * Hide if clicked outside option
				 */
				{
					$input.parent().attr('id', 'fw-rgba-color-picker-r-'+ (++helpers.increment));

					var originalShowCallback = helpers.getInstance($input).show;

					helpers.getInstance($input).show = function () {
						$(document.body)
							.off('click'+ eventNamespace)
							.on('click'+ eventNamespace, function(e){
								if (!$(e.target).closest('#'+ $input.parent().attr('id')).length) {
									$(document.body).off('click'+ eventNamespace);
									$input.iris('hide');
								}
							});

						originalShowCallback.apply(this);
					};
				}

				/**
				 * After the second hide the picker is not showing on the next focus (I don't know why)
				 * Show it manually
				 */
				$input.on('focus', function(){
					if (!$picker.is(':visible')) {
						$input.iris('show');
					}
				});

				$input.on('change keyup blur', function () {
					// iris::change is not triggered when the input is empty or color is wrong
					helpers.updatePreview($input, $input.val());
				});

				$(''
					+ '<div class="fw-alpha-container">' 
					+ /**/'<div class="slider-alpha"></div>'
					+ /**/'<div class="transparency"></div>'
					+ '</div>'
				).appendTo($input.next('.iris-picker'));

				var $alphaSlider = $input.next('.iris-picker:first').find('.slider-alpha');

				$alphaSlider.slider({
					value: Color($input.val())._alpha * 100,
					range: "max",
					step: 1,
					min: 0,
					max: 100,
					slide: function (event, ui) {
						$(this).find('.ui-slider-handle').text(ui.value);

						$input.data('a8cIris')._color._alpha = parseFloat(ui.value) / 100.0;

						var color = $input.iris('color', true),
							cssColor = (
								(ui.value < 100) ? color.toCSS('rgba', ui.value / 100) : color.toHex()
							).replace(/\s/g, '');

						$input.val(cssColor);

						clearTimeout(changeTimeoutId);
						changeTimeoutId = setTimeout(function(){
							$input.trigger('fw:option-type:rgba-color-picker:change', {
								$element: $input,
								iris: $input.data('a8cIris'),
								alphaSlider: $alphaSlider.data('uiSlider')
							});
							$input.trigger('change');
						}, 12);
					},
					create: function (event, ui) {
						var v = $(this).slider('value'),
							$transparency = $input.next('.iris-picker:first').find('.transparency');

						$(this).find('.ui-slider-handle').text(v);

						$transparency.css('backgroundColor', Color($input.val()).toCSS('rgb', 1));
					},
					change: function (event, ui) {
						$(this).find('.ui-slider-handle').text(ui.value);

						$input.data('a8cIris')._color._alpha = parseFloat(ui.value) / 100.0;

						var color = $input.iris('color', true),
							cssColor = (
								(ui.value < 100) ? color.toCSS('rgba', ui.value / 100) : color.toHex()
							).replace(/\s/g, '');

						$input.val(cssColor);

						clearTimeout(changeTimeoutId);
						changeTimeoutId = setTimeout(function(){
							$input.trigger('fw:option-type:rgba-color-picker:change', {
								$element: $input,
								iris: $input.data('a8cIris'),
								alphaSlider: $alphaSlider.data('uiSlider')
							});
							$input.trigger('change');
						}, 12);
					}
				});

				var $firstPalette = $picker.find('.iris-palette-container > .iris-palette:first-child');

				/**
				 * "Reset" color button
				 */
				$.each([{
					color: $input.attr('data-default'),
					text: helpers.localized.l10n.reset_to_default
				},{
					color: $input.val(),
					text: helpers.localized.l10n.reset_to_initial
				}], function(i, data){
					if (data.color && helpers.isColorValid(data.color)) {
						$picker.find('> .iris-picker-inner').append(''
							+ '<div class="' + helpers.optionClass + '-reset-default fw-pull-left">'
							+ /**/'<a class="iris-palette" style="'
							+ /**//**/'background-color:'+ data.color +';'
							+ /**//**/'height:' + $firstPalette.css('height') + ';'
							+ /**//**/'width:' + $firstPalette.css('width') + ';'
							+ /**//**/'"></a>'
							+ /**/'<span>' + data.text + '</span>'
							+ '</div>'
						);

						$picker
							.on(
								'click',
								'.' + helpers.optionClass + '-reset-default',
								function(){
									$input.iris('color', $(this).find('.iris-palette').css('background-color'));
								}
							)
							.on('remove', function(){
								$(document.body).off(eventNamespace);
							})
							.addClass(helpers.optionClass + '-with-reset-default')
							.css('height', parseFloat($picker.css('height')) + 17);

						return false;
					}
				});

				$input.iris('show');
			});

			helpers.updatePreview($input, $input.val());

			$input.addClass('initialized');
		});
	});

});
