(function($, fwe) {
	//jQuery.fwDatetimepicker.setLocale(jQuery('html').attr('lang').split('-').shift());

	var init = function() {
		var $container = $(this),
			$input = $container.find('.fw-option-type-text'),
			data = {
				options: $container.data('datetime-attr'),
				el: $input,
				container: $container
			};

		fwe.trigger('fw:options:datetime-picker:before-init', data);

		$input.fwDatetimepicker(data.options)
			.on('change', function (e) {
				fw.options.trigger.changeForEl(
					jQuery(e.target).closest('[data-fw-option-type="datetime-picker"]'), {
						value: e.target.value
					}
				)
			});
	};

	fw.options.register('datetime-picker', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			return {
				value: $(optionDescriptor.el).find(
					'[data-fw-option-type="text"]'
				).find('> input').val(),
				optionDescriptor: optionDescriptor
			}
		}
	})

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-datetime-picker').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);
