(function ($) {

	fw.options.register('fw-undefined', {
		startListeningForChanges: defaultStartListeningForChanges,
		getValue: function (optionDescriptor) {
			// will trigger AJAX requests by default
		}
	});

	// By default, for unknown option types do listening only once
	function defaultStartListeningForChanges (optionDescriptor) {
		if (optionDescriptor.el.classList.contains('fw-listening-started')) {
			return;
		}

		optionDescriptor.el.classList.add('fw-listening-started');

		listenToChangesForCurrentOptionAndPreserveScoping(
			optionDescriptor.el,
			function (e) {
				fw.options.trigger.changeForEl(e.target);
			}
		);

		if (optionDescriptor.hasNestedOptions) {
			fw.options.on.changeByContext(optionDescriptor.el, function (nestedDescriptor) {
				fw.options.trigger.changeForEl(optionDescriptor.el);
			});
		}
	}

	function listenToChangesForCurrentOptionAndPreserveScoping (el, callback) {
		jQuery(el).find(
			'input, select, textarea'
		).not(
			jQuery(el).find(
				'.fw-backend-option-descriptor input'
			).add(
				jQuery(el).find(
					'.fw-backend-option-descriptor select'
				)
			).add(
				jQuery(el).find(
					'.fw-backend-option-descriptor textarea'
				)
			).add(
				jQuery(el).find(
					'.fw-backend-options-virtual-context input'
				)
			).add(
				jQuery(el).find(
					'.fw-backend-options-virtual-context select'
				)
			).add(
				jQuery(el).find(
					'.fw-backend-options-virtual-context textarea'
				)
			)
		).on('change', callback);
	}


})(jQuery);
