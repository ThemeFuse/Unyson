(function ($, fwEvents) {
	var defaults = {
		onChange: function (data) {
			var from = (data.from_value) ? data.from_value : data.from;
			var to = (data.to_value) ? data.to_value : data.to;
			data.input.next('.fw-irs-range-slider-hidden-input').val(from + ';' + to);
			data.input.closest('.fw-option-type-range-slider').find('span.irs-slider.from').html(from);
			data.input.closest('.fw-option-type-range-slider').find('span.irs-slider.to').html(to);
		},
		onStart: function (data) {
			var from = (data.from_value) ? data.from_value : data.from;
			var to = (data.to_value) ? data.to_value : data.to;
			data.input.closest('.fw-option-type-range-slider').find('span.irs-slider.from').html(from);
			data.input.closest('.fw-option-type-range-slider').find('span.irs-slider.to').html(to);
		},
		grid: true
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option-type-range-slider').each(function () {
			var options = JSON.parse($(this).attr('data-fw-irs-options'));
			$(this).find('.fw-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));
		});
	});

})(jQuery, fwEvents);