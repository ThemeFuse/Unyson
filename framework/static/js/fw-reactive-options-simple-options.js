(function($) {
	var simpleInputs = [
		'text',
		'short-text',
		'hidden',
		'password',
		'textarea',
		'html',
		'html-fixed',
		'html-full',
		'select',
		'short-select',
		'gmap-key',
		'slider',
		'short-slider',
	];

	simpleInputs.map(function(optionType) {
		fw.options.register(optionType, {
			getValue: getValueForSimpleInput,
		});
	});

	function getValueForSimpleInput(optionDescriptor) {
		return {
			value: optionDescriptor.el.querySelector('input, textarea, select')
				.value,
			optionDescriptor: optionDescriptor,
		};
	}

	fw.options.register('unique', {
		getValue: function(optionDescriptor) {
			var actualValue = optionDescriptor.el.querySelector(
				'input, textarea, select'
			).value;

			return {
				value: !!actualValue.trim() ? actualValue : fw.randomMD5(),
				optionDescriptor: optionDescriptor,
			};
		},
	});

	fw.options.register('checkbox', {
		getValue: function(optionDescriptor) {
			return {
				value: optionDescriptor.el.querySelector(
					'input.fw-option-type-checkbox'
				).checked,
				optionDescriptor: optionDescriptor,
			};
		},
	});

	fw.options.register('checkboxes', {
		getValue: function(optionDescriptor) {
			var checkboxes = $(optionDescriptor.el)
				.find('[type="checkbox"]')
				.slice(1);

			var value = {};

			checkboxes.toArray().map(function(el) {
				value[$(el).attr('data-fw-checkbox-id')] = el.checked;
			});

			return {
				value: value,
				optionDescriptor: optionDescriptor,
			};
		},
	});

	fw.options.register('radio', {
		getValue: function(optionDescriptor) {
			return {
				value: $(optionDescriptor.el).find('input:checked').val(),
				optionDescriptor: optionDescriptor,
			};
		},
	});

	fw.options.register('select-multiple', {
		getValue: function(optionDescriptor) {
			return {
				value: $(optionDescriptor.el.querySelector('select')).val(),
				optionDescriptor: optionDescriptor,
			};
		},
	});

	fw.options.register('multi', {
		getValue: function(optionDescriptor) {
			var promise = $.Deferred();

			fw.options
				.getContextValue(optionDescriptor.el)
				.then(function(result) {
					promise.resolve({
						value: result.value,
						optionDescriptor: optionDescriptor,
					});
				});

			return promise;
		},
	});
})(jQuery);
