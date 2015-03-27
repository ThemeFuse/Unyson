(function ($, fwEvents) {
	var defaults = {
		onChange: function (data) {
			var from = (data.from_value) ? data.from_value : data.from;
			var to = (data.to_value) ? data.to_value : data.to;
			data.input.next('.fw-irs-range-slider-hidden-input').val(from + ';' + to);
			data.input.closest('.fw-option-font-metrics-slider-option').find('span span.irs-slider.single').html(from);
		},
		onStart: function (data) {
			var from = (data.from_value) ? data.from_value : data.from;
			data.input.closest('.fw-option-font-metrics-slider-option').find('span span.irs-slider.single').html(from);
			data.input.closest('.fw-option-font-metrics-slider-option').find('.irs-bar-edge').remove();
		},
		grid: false
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option-font-metrics-slider-option').each(function () {
			var options = JSON.parse($(this).attr('data-fw-irs-options'));
			var slider = $(this).find('.fw-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));
		});
	});

})(jQuery, fwEvents);