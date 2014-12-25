(function ($) {
	var defaults = {
		onChange: function (data) {
			data.input.next('.fw-irs-range-slider-hidden-input').val(data.from + ';' + data.to);
		}
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option-type-range-slider').each(function () {
			var options = JSON.parse($(this).attr('data-fw-irs-options'));
			$(this).find('.fw-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));
		});
	});

})(jQuery);