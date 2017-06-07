(function ($, fwEvents) {
	var defaults = {
		grid: true
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option-type-slider:not(.initialized)').each(function () {
			var options = JSON.parse($(this).attr('data-fw-irs-options'));
			var slider = $(this).find('.fw-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));
		}).addClass('initialized');
	});

})(jQuery, fwEvents);
