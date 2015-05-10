jQuery(function($){ // todo: delay change trigger (slider fires it too often) or make a delay after last change (when will be realt-ime update of some text to be without delay)
	var initialized = false,
		init = function(){
			if (initialized) {
				return;
			}

			$('#customize-theme-controls')
				.on('change', '.fw-backend-customizer-option > .fw-backend-customizer-option-inner', function(){
					var $input = $(this).closest('.fw-backend-customizer-option').find('> input.fw-backend-customizer-option-input');

					/**
					 * Extract all input values within option and save them to the customizer input (to trigger preview update)
					 */
					$input.val(
						JSON.stringify(
							$(this).find(':input').serializeArray()
						)
					);

					if (initialized) {
						$input.trigger('change');
					} else {
						// do not trigger when inputs are populated first time on init
					}
				})
				// update/populate all inputs first time
				.find('.fw-backend-customizer-option > .fw-backend-customizer-option-inner').trigger('change');

			initialized = true;
		};

	fwEvents.one('fw:options:init', function(){
		setTimeout(
			init,
			40 // must be later than first 'fw:options:init' on body http://bit.ly/1F1dDUZ
		);
	});
});