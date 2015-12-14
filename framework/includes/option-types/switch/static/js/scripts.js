jQuery(document).ready(function ($) {
	var optionTypeClass = 'fw-option-type-switch',
		customEventPrefix = 'fw:option-type:switch:';

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.'+ optionTypeClass +':not(.fw-option-initialized)').find('input[type="checkbox"]')
			.on('change', function(){
				var $this = $(this),
					value;

				if ($this.prop('checked')) {
					value = $this.attr('data-switch-right-bool-value');

					if (value) {
						value = value == 'true';
					} else {
						value = $this.attr('data-switch-right-value')
					}

					$this
						// prevent hidden value sent in POST
						.prev('input[type="hidden"]').removeAttr('name')
						// set choice hidden json value
						.prev('input[type="hidden"]').val($this.attr('data-switch-right-value-json'));
				} else {
					value = $this.attr('data-switch-left-bool-value');

					if (value) {
						value = value == 'true';
					} else {
						value = $this.attr('data-switch-left-value');
					}

					$this
						// make hidden value sent in POST
						.prev('input[type="hidden"].js-post-key').attr('name', $this.attr('name'))
						// set choice hidden json value
						.prev('input[type="hidden"]').val($this.attr('data-switch-left-value-json'));;
				}

				$this.closest('.'+ optionTypeClass).trigger(customEventPrefix +'change', {
					value: value
				});
			})
			.adaptiveSwitch()
			.addClass('fw-option-initialized');
	});
});