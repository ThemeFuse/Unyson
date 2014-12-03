(function ($) {
	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option.fw-option-type-gradient:not(.initialized)').each(function(){
			//onChange primary, set secondary with primary color
			$(this).on('fw:color:picker:changed', function (event, data) {
				if (data.$element.closest('.primary-color').length === 1) {
					data.$element.closest('.fw-option-type-gradient').find('.secondary-color input.fw-option-type-color-picker.secondary.initialized').iris('color', data.ui.color.toString());
				}
			});

			$(this).addClass('initialized');
		});
	});
})(jQuery);