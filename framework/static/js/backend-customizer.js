jQuery(function($){
	var initialized = false,
		changeTimeoutId = 0,
		/**
		 * @type {Object} {'#options_wrapper_id':'~'}
		 */
		pendingChanges = {},
		/**
		 * Extract all input values within option and save them to the customizer input (to trigger preview update)
		 */
		processPendingChanges = function(){
			$.each(pendingChanges, function(optionsWrapperId){
				var $optionsWrapper = $('#'+ optionsWrapperId),
					$input = $optionsWrapper.closest('.fw-backend-customizer-option').find('> input.fw-backend-customizer-option-input'),
					newValue = JSON.stringify($optionsWrapper.find(':input').serializeArray());

				if ($input.val() === newValue) {
					return;
				}

				$input.val(newValue).trigger('change');
			});

			pendingChanges = {};
		},
		randomIdIncrement = 0,
		init = function(){
			if (initialized) {
				return;
			}

			/**
			 * Populate all <input class="fw-backend-customizer-option-input" ... /> with (initial) options values
			 */
			$('#customize-theme-controls .fw-backend-customizer-option').each(function(){
				$(this).find('> input.fw-backend-customizer-option-input').val(
					JSON.stringify(
						$(this).find('> .fw-backend-customizer-option-inner :input').serializeArray()
					)
				);
			});

			/**
			 * When something may be changed, removed, added; add to pending changes
			 */
			$('#customize-theme-controls').on(
				'change keyup click paste',
				'.fw-backend-customizer-option > .fw-backend-customizer-option-inner > .fw-backend-option > .fw-backend-option-input',
				function(e){
					clearTimeout(changeTimeoutId);

					{
						var optionsWrapperId = $(this).attr('id');

						if (!optionsWrapperId) {
							optionsWrapperId = 'rnid-'+ (++randomIdIncrement);
							$(this).attr('id', optionsWrapperId);
						}

						pendingChanges[optionsWrapperId] = '~';
					}

					changeTimeoutId = setTimeout(
						processPendingChanges,
						/**
						 * Let css animations finish,
						 * to prevent block/glitch in the middle of the animation when the iframe will reload.
						 * Bigger than 300, which most of the css animations are.
						 */
						333
					);
				}
			);

			initialized = true;
		};

	fwEvents.one('fw:options:init', function(){
		setTimeout(
			init,
			40 // must be later than first 'fw:options:init' on body http://bit.ly/1F1dDUZ
		);
	});
});