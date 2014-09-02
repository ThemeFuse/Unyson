jQuery(document).ready(function ($) {
	var optionTypeClass = 'fw-option-type-background-image';
	var eventNamePrefix = 'fw:option-type:background-image:';

	fwEvents.on('fw:options:init', function (data) {
		var $options = data.$elements.find('.'+ optionTypeClass +':not(.initialized)');

		$options.find('.fw-option-type-radio').on('change', function (e) {
			var $predefined = jQuery(this).closest('.fw-inner').find('.predefined');
			var $custom = jQuery(this).closest('.fw-inner').find('.custom');

			if (e.target.value === 'custom') {
				$predefined.hide();
				$custom.show();
			} else {
				$predefined.show();
				$custom.hide();
			}
		});

		// route inner image-picker events as this option events
		{
			$options.on('fw:option-type:image-picker:clicked', '.fw-option-type-image-picker', function(e, data) {
				jQuery(this).trigger(eventNamePrefix +'clicked', data);
			});

			$options.on('fw:option-type:image-picker:changed', '.fw-option-type-image-picker', function(e, data) {
				jQuery(this).trigger(eventNamePrefix +'changed', data);
			});
		}

		$options.addClass('initialized');
	});

});