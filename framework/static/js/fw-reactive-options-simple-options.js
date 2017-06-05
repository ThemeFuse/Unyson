(function ($) {
	var simpleInputs = [
		'text',
		'short-text',
		'hidden',
		'password',
		'textarea',
		'html',
		'html-fixed',
		'html-full',
		'unique',
		'select',
		'short-select',
		'gmap-key'
	]

	simpleInputs.map(function (optionType) {
		fw.options.register(optionType, {
			getValue: getValueForSimpleInput
		});
	});

	function getValueForSimpleInput (optionDescriptor) {
		return {
			value: optionDescriptor.el.querySelector(
				'input, textarea, select'
			).value,
			optionDescriptor: optionDescriptor
		};
	}

	fw.options.register('checkbox', {
		getValue: function (optionDescriptor) {
			return {
				value: optionDescriptor.el.querySelector(
					'input.fw-option-type-checkbox'
				).checked,
				optionDescriptor: optionDescriptor
			};
		}
	});

	fw.options.register('checkboxes', {
		getValue: function (optionDescriptor) {
			var checkboxes = $(optionDescriptor.el).find(
				'[type="checkbox"]'
			).slice(1);

			var value = {};

			checkboxes.toArray().map(function (el) {
				value[$(el).attr('data-fw-checkbox-id')] = el.checked;
			});

			return {
				value: value,
				optionDescriptor: optionDescriptor
			};
		}
	});

	fw.options.register('radio', {
		getValue: function (optionDescriptor) {
			return {
				value: $(optionDescriptor.el).find('input:checked').val(),
				optionDescriptor: optionDescriptor
			};
		}
	});

	fw.options.register('select-multiple', {
		getValue: function (optionDescriptor) {
			return {
				value: $(optionDescriptor.el.querySelector(
					'select'
				)).val(),
				optionDescriptor: optionDescriptor
			};
		}
	});
})(jQuery);
