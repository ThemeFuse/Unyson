jQuery(document).ready(function($){
	var helpers = {
		colorRegex: /^#[a-f0-9]{3}([a-f0-9]{3})?$/,
		/**
		 * Return true if color is dark
		 * @param {string} color Accept only correct color format, e.g. #123456
		 */
		isColorValid: function (color) {
			return this.colorRegex.test(color);
		},
		isColorDark: function (color) {
			color = color.substring(1); // remove #

			/** @link http://24ways.org/2010/calculating-color-contrast/ */
			{
				var r = parseInt(color.substr(0,2),16);
				var g = parseInt(color.substr(2,2),16);
				var b = parseInt(color.substr(4,2),16);
				var yiq = ((r*299)+(g*587)+(b*114))/1000;
			}

			return yiq < 128;
		},
		getInstance: function ($iris) {
			return $iris.data('a8cIris');
		},
		updatePreview: function ($input, color) {
			if (this.isColorValid(color)) {
				$input.css({
					'background-color': color,
					'color': helpers.isColorDark(color) ? '#FFFFFF' : '#000000'
				});
			} else {
				$input.css({
					'background-color': '',
					'color': ''
				});
			}
		},
		increment: 0
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('input.fw-option-type-color-picker:not(.initialized)').each(function(){
			var $input = $(this),
				changeTimeoutId = 0;

			/**
			 * Improvement: Initialized picker only on first focus
			 * Do not initialize all pickers on the page, for performance reasons, maybe none of them will be opened
			 */
			$input.one('focus', function(){
				$input.iris({
					hide: true,
					defaultColor: false,
					clear: function(){},
					change: function(event, ui){
						helpers.updatePreview($input, ui.color.toString());

						$input.trigger('fw:color:picker:changed', {
							$element: $input,
							event   : event,
							ui      : ui
						});

						/**
						 * If we trigger the 'change' right here, that will block the picker (I don't know why)
						 */
						clearTimeout(changeTimeoutId);
						changeTimeoutId = setTimeout(function(){
							$input.trigger('change');
						}, 12);
					},
					palettes: true
				});

				{
					var color = $input.val();

					if (helpers.isColorValid(color)) {
						$input.iris('color', color);
					}
				}

				/**
				 * Hide if clicked outside option
				 */
				{
					$input.parent().attr('id', 'fw-color-picker-r-'+ (++helpers.increment));

					var originalShowCallback = helpers.getInstance($input).picker.show;

					helpers.getInstance($input).picker.show = function () {
						$(document.body)
							.off('click.fwHideCurrentColorPicker')
							.on('click.fwHideCurrentColorPicker', function(e){
								if (!$(e.target).closest('#'+ $input.parent().attr('id')).length) {
									$(document.body).off('click.fwHideCurrentColorPicker');
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
					if (!helpers.getInstance($input).picker.is(':visible')) {
						$input.iris('show');
					}
				});

				$input.on('change keyup blur', function(){
					/**
					 * iris::change is not triggered when the input is empty or color is wrong
					 * we need to remove the preview from previous correct color
					 */
					helpers.updatePreview($input, $input.val());
				});

				$input.iris('show');
			});

			helpers.updatePreview($input, $input.val());

			$input.addClass('initialized');
		});

		/*
		// fixme: where this code is needed? why it does full page selectors instead of only specific initialized option?
		$('.fw-inner').on('click', '.fw-option-type-color-picker', function () {
			var $this = $(this);
			$('.fw-option-type-color-picker.initialized').iris('hide');

			$this.iris('show');

			var widthParent = $this.closest('.fw-backend-option').outerWidth(),
				widthPiker = $this.next('.iris-picker').outerWidth(),
				offsetPiker = ($this.next('.iris-picker').offset().left - $this.closest('.fw-backend-option').offset().left) + widthPiker;

			if (offsetPiker > widthParent) {
				$this.next('.iris-picker').css('right', '0');
			}
			return false;
		});
		*/
	});
});
