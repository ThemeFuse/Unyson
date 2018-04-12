jQuery(function($) {
	var optionTypeClass = '.fw-option-type-radio-text';
	var customRadioSelector =
		'.predefined .fw-option-type-radio > div:last-child input[type="radio"]';

	fwEvents.on('fw:options:init', function(data) {
		var $options = data.$elements.find(
			optionTypeClass + ':not(.initialized)'
		);

		$options.find('.fw-option-type-text').on('focus', function() {
			// check "custom" radio box
			$(this)
				.closest(optionTypeClass)
				.find(customRadioSelector)
				.prop('checked', true);
		});

		$options.find(customRadioSelector).on('focus', function() {
			$(this).closest(optionTypeClass).find('.custom input').focus();
		});

		$options.addClass('initialized');
	});

	fw.options.register('radio-text', {
		getValue: function(optionDescriptor) {
			var checked = $(optionDescriptor.el).find('input:checked');

			var value = checked.val();

			if (checked.closest('div').is(':last-child')) {
				value = $(optionDescriptor.el).find('[type="text"]').val();
			}

			return {
				value: value,
				optionDescriptor: optionDescriptor,
			};
		},
	});
});
