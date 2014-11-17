jQuery(document).ready(function($){
	$(document.body).click(function (e) {
		if (!$(e.target).is('.fw-option-type-color-picker, .iris-picker, .iris-picker-inner, .iris-palette')) {
			$('.fw-option-type-color-picker.initialized').iris('hide');
		}
	});

	/**
	 * Return true if color is dark
	 * @param {string} color Accept only correct color format, e.g. #123456
	 */
	function isColorDark(color) {
		color = color.substring(1); // remove #

		/** @link http://24ways.org/2010/calculating-color-contrast/ */
		{
			var r = parseInt(color.substr(0,2),16);
			var g = parseInt(color.substr(2,2),16);
			var b = parseInt(color.substr(4,2),16);
			var yiq = ((r*299)+(g*587)+(b*114))/1000;
		}

		return yiq < 128;
	}

	var colorRegex = /^#[a-f0-9]{3}([a-f0-9]{3})?$/;

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('input.fw-option-type-color-picker:not(.initialized)').each(function(){
			var $input = $(this);

			$input.iris({
				hide: false,
				defaultColor: false,
				clear: function(){},
				change: function(event, ui){
					var color = ui.color.toString();

					$input.css('background-color', color);
					$input.css('color', isColorDark(color) ? '#FFFFFF' : '#000000');

					$input.trigger('fw:color:picker:changed', {
						$element: $input,
						event   : event,
						ui      : ui
					});
				},
				palettes: true
			});

			$input.on('change keyup blur', function(){
				/**
				 * iris::change is not triggered when the input is empty or color is wrong
				 */
				if (!colorRegex.test($input.val())) {
					$input.css('background-color', '');
					$input.css('color', '');
				}
			});

			$input.iris('hide');

			var color = $input.val();

			if (colorRegex.test(color)) {
				$input.iris('color', color);
			}

			$input.addClass('initialized');
		});

		jQuery('.fw-inner').on('click', '.fw-option-type-color-picker', function () {
			$('.fw-option-type-color-picker.initialized').iris('hide');

			$(this).iris('show');

			return false;
		});

	});
});