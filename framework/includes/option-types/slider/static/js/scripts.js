(function ($, fwEvents) {
	var defaults = {
		onChange: function (data) {
			data.input.next('.fw-irs-range-slider-hidden-input').val(data.from + ';' + data.to);
			data.input.closest('.fw-option-type-slider').find('span span.irs-slider.single').html(data.from);
		},
		onStart: function (data) {
			data.input.closest('.fw-option-type-slider').find('span span.irs-slider.single').html(data.from);
			data.input.closest('.fw-option-type-slider').find('.irs-bar-edge').remove();
		},
		grid: true
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option-type-slider').each(function () {
			var options = JSON.parse($(this).attr('data-fw-irs-options'));
			var slider = $(this).find('.fw-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));
		});
	});

})(jQuery, fwEvents);