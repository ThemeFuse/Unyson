(function ($) {
	$(document.body).click(function (e) {
		if (!$(e.target).is('.fw-option-type-rgba-color-picker, .iris-picker, .iris-picker-inner, .iris-palette, .fw-alpha-container')) {
			$('.fw-option-type-rgba-color-picker.initialized').iris('hide');
		}
	});

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

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('input.fw-option-type-rgba-color-picker:not(.initialized)').each(function () {

			var $input = $(this);

			$input.iris({
				palettes: true,
				defaultColor: false,
				change: function (event, ui) {
					var $transparency = $input.next('.iris-picker').find('.transparency');
					$transparency.css('backgroundColor', ui.color.toString('no-alpha'));

					$alpha_slider.slider( "option", "value", ui.color._alpha * 100 );

					$input.css('background-color', ui.color.toCSS());
					$input.css('color', ($alpha_slider.slider("value") > 40) ? ui.color.getMaxContrastColor().toCSS() : '#000000');
				}
			});

			$input.on('change keyup blur', function () {
				/**
				 * iris::change is not triggered when the input is empty or color is wrong
				 */
				if (Color($input.val()).error) {
					$input.css('background-color', '');
					$input.css('color', '');
				}
			});

			$('<div class="fw-alpha-container"><div class="slider-alpha"></div><div class="transparency"></div></div>').appendTo($input.next('.iris-picker'));

			var $alpha_slider = $input.next('.iris-picker:first').find('.slider-alpha');

			$alpha_slider.slider({
				value: Color($input.val())._alpha * 100,
				range: "max",
				step: 1,
				min: 1,
				max: 100,
				slide: function (event, ui) {
					$(this).find('.ui-slider-handle').text(ui.value);

					var color = $input.iris('color', true);
					var cssColor = (ui.value < 100) ? color.toCSS('rgba', ui.value / 100) : color.toHex();

					$input.css('background-color', cssColor).val(cssColor);
					$input.css('color', (ui.value > 40) ? color.getMaxContrastColor().toCSS() : '#000000');

					var new_alpha_val = parseFloat(ui.value),
						iris = $input.data('a8cIris');
					iris._color._alpha = new_alpha_val / 100.0;
				},
				create: function (event, ui) {
					var v = $(this).slider('value');
					$(this).find('.ui-slider-handle').text(v);
					var $transparency = $input.next('.iris-picker:first').find('.transparency');
					$transparency.css('backgroundColor', Color($input.val()).toCSS('rgb', 1));
				},
				change: function (event, ui) {
					$(this).find('.ui-slider-handle').text(ui.value);

					var color = $input.iris('color', true);
					var cssColor = (ui.value < 100) ? color.toCSS('rgba', ui.value / 100) : color.toHex();

					$input.css('background-color', cssColor).val(cssColor);
					$input.css('color', (ui.value > 40) ? color.getMaxContrastColor().toCSS() : '#000000');

					var new_alpha_val = parseFloat(ui.value),
						iris = $input.data('a8cIris');
					iris._color._alpha = new_alpha_val / 100.0;
				}
			});

			$input.iris('hide');

			if (!Color($input.val()).error) {
				$input.iris('color', $input.val());
			}

			$input.addClass('initialized');

			$input.show();

			$('.fw-inner').on('click', '.fw-option-type-rgba-color-picker', function () {
				$('.fw-option-type-rgba-color-picker.initialized').iris('hide');

				$(this).iris('show');

				return false;
			});
		});
	});

})(jQuery);

