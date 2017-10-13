jQuery(document).ready(function($){
	var helpers = {
		optionClass: 'fw-option-type-color-picker',
		eventNamespace: '.fwOptionTypeColorPicker',
		colorRegex: /^#([a-f0-9]{3}){1,2}$/i,
		localized: window._fw_option_type_color_picker_localized,
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
				var r = parseInt(color.substr(0,2),16),
					g = parseInt(color.substr(2,2),16),
					b = parseInt(color.substr(4,2),16),
					yiq = ((r*299)+(g*587)+(b*114))/1000;
			}

			return yiq < 128;
		},
		getInstance: function ($iris) {
			return $iris.data('a8cIris');
		},
		updatePreview: function ( $input, color ) {
			if ( this.isColorValid( color ) ) {
				$input.attr( 'style', 'background-color:' + color + ' !important; color:' + ( this.isColorDark( color ) ? '#FFFFFF' : '#000000' ) + ' !important;' );
			} else {
				$input.css( {'background-color': '', 'color': ''} );
			}
		},
		increment: 0
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('input.'+ helpers.optionClass +':not(.initialized)').each(function(){
			var $input = $(this),
				changeTimeoutId = 0,
				eventNamespace = helpers.eventNamespace +'_'+ (++helpers.increment);

			/**
			 * Improvement: Initialized picker only on first focus
			 * Do not initialize all pickers on the page, for performance reasons, maybe none of them will be opened
			 */
			$input.one('focus', function(){
				var initialValue = $input.val();

				$input.iris({
					hide: true,
					defaultColor: false,
					clear: function(){},
					change: function(event, ui){
						/**
						 * If we trigger the 'change' right here, that will block the picker (I don't know why)
						 */
						clearTimeout(changeTimeoutId);
						changeTimeoutId = setTimeout(function(){
							// prevent useless 'change' event when nothing has changed (happens right after init)
							if (initialValue !== null && $input.val() === initialValue) {
								initialValue = null;
								return;
							} else {
								initialValue = null; // make sure the above `if` is executed only once
							}

							$input.trigger('fw:color:picker:changed', { // should be 'fw:option-type:color-picker:change'
								$element: $input,
								event   : event,
								ui      : ui
							});
							$input.trigger('change');
						}, 12);
					},
					palettes: JSON.parse($input.attr('data-palettes'))
				});

				$input.addClass('iris-initialized');

				var $picker = helpers.getInstance($input).picker;

				$picker.addClass(helpers.optionClass +'-iris');

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

				$input.on('change keyup blur', function(){
					/**
					 * iris::change is not triggered when the input is empty or color is wrong
					 * we need to remove the preview from previous correct color
					 */
					helpers.updatePreview($input, $input.val());
				});

				var $firstPalette = $picker.find('.iris-palette-container > .iris-palette:first-child');

				/**
				 * Fix style
				 */
				$firstPalette.css('margin-left', '');

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
