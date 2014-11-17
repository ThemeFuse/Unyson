(function($, fwe) {
	var init = function() {
		var $this = $(this),
			elements = {
				$pickerGroup: $this.find('> .picker-group'),
				$choicesGroups: $this.find('> .choice-group')
			},
			chooseGroup = function(groupId) {
				var $choicesToReveal = elements.$choicesGroups.filter('.choice-' + groupId);
				elements.$choicesGroups.removeClass('chosen');
				$choicesToReveal.addClass('chosen');

				if ($choicesToReveal.length) {
					$this.addClass('has-choice');
				} else {
					$this.removeClass('has-choice');
				}
			},
			pickerType = elements.$pickerGroup.attr('class').match(/picker-type-(\S+)/)[1],
			flows = {
				'switch': function() {
					elements.$pickerGroup.find(':checkbox').on('change', function() {
						var $this = $(this),
							checked = $(this).is(':checked'),
							value = checked
										? $this.attr('data-switch-right-value')
										: $this.attr('data-switch-left-value');

						chooseGroup(value);
					}).trigger('change');
				},
				'select': function() {
					elements.$pickerGroup.find('select').on('change', function() {
						chooseGroup(this.value);
					}).trigger('change');
				},
				'radio': function() {
					elements.$pickerGroup.find(':radio').on('change', function() {
						chooseGroup(this.value);
					}).filter(':checked').trigger('change');
				},
				'image-picker': function() {
					elements.$pickerGroup.find('select').on('change', function() {
						chooseGroup(this.value);
					}).trigger('change');
				}
			};

		if (!pickerType || !flows[pickerType]) {
			console.error('unknown multi-picker type:', pickerType);
		} else {
			flows[pickerType]();
		}
	};

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-multi-picker:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});
})(jQuery, fwEvents);
