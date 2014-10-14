jQuery(document).ready(function ($) {
	var optionTypeClass = 'fw-option-type-switch';
	var customEventPrefix = 'fw:option-type:switch:';

	fwEvents.on('fw:options:init', function (data) {
		var $elements = data.$elements.find('.'+ optionTypeClass +':not(.fw-option-initialized)');

		$elements.find('input[type="checkbox"]')
			.on('change', function(){
				var $this = $(this);

				var value;

				if ($this.prop('checked')) {
					value = $this.attr('data-switch-right-bool-value');

					if (value) {
						value = value == 'true';
					} else {
						value = $this.attr('data-switch-right-value')
					}

					// prevent hidden value sent in POST
					$this.prev('input[type="hidden"]').removeAttr('name');
				} else {
					value = $this.attr('data-switch-left-bool-value');

					if (value) {
						value = value == 'true';
					} else {
						value = $this.attr('data-switch-left-value');
					}

					// make hidden value sent in POST
					$this.prev('input[type="hidden"]').attr('name', $this.attr('name'));
				}

				$this.closest('.'+ optionTypeClass).trigger(customEventPrefix +'change', {
					value: value
				});
			})
			.adaptiveSwitch();

		$elements.addClass('fw-option-initialized');
	});
});