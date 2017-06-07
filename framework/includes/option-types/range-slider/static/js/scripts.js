(function ($, fwEvents) {
	var defaults = {
		grid: true
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option-type-range-slider:not(.initialized)').each(function () {
			var options = JSON.parse($(this).attr('data-fw-irs-options'));
			$(this).find('.fw-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));

			$(this).find('.fw-irs-range-slider').on('change', _.throttle(function (e) {
				fw.options.trigger.changeForEl(e.target, {
					value: getValueForEl(e.target)
				})
			}, 300));
		}).addClass('initialized');
	});

	fw.options.register('range-slider', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			return {
				value: getValueForEl(
					$(optionDescriptor.el).find('[type="text"]')[0]
				),

				optionDescriptor: optionDescriptor
			}
		}
	});

	function getValueForEl (el) {
		var rangeArray = el.value.split(';');

		return {
			from: rangeArray[0],
			to: rangeArray[1]
		}
	}

})(jQuery, fwEvents);
