(function($, fwe) {
	var init = function() {
		var $this = $(this),
			elements = {
				$pickerGroup: $this.find('> .picker-group'),
				$choicesGroups: $this.find('> .choice-group')
			},
			chooseGroup = function(groupId) {
				var $choicesToReveal = elements.$choicesGroups.filter('.choice-group[data-choice-key="'+ groupId +'"]');

				/**
				 * The group options html was rendered in an attribute to make page load faster.
				 * Move the html from attribute in group and init options with js.
				 */
				if ($choicesToReveal.attr('data-options-template')) {
					$choicesToReveal.html(
						$choicesToReveal.attr('data-options-template')
					);

					$choicesToReveal.removeAttr('data-options-template');

					fwEvents.trigger('fw:options:init', {
						$elements: $choicesToReveal
					});
				}

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
							value = JSON.parse($this.attr('data-switch-'+ (checked ? 'right' : 'left') +'-value-json'));

						chooseGroup(value);
					}).trigger('change');
				},
				'select': function() {
					elements.$pickerGroup.find('select').on('change', function() {
						chooseGroup(this.value);
					}).trigger('change');
				},
				'short-select': function() {
					this.select();
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
				},
				'icon-v2': function () {
					var iconV2Selector = '.fw-option-type-icon-v2 > input';

					elements.$pickerGroup.find(iconV2Selector).on('change', function() {
						var type = JSON.parse(this.value)['type'];
						chooseGroup(type);
					}).trigger('change');
				}
			};

		if (!pickerType) {
			console.error('unknown multi-picker type:', pickerType);
		} else {
			if (flows[pickerType]) {
				flows[pickerType]();
			} else {
				var eventName = 'fw:option-type:multi-picker:init:'+ pickerType;

				if (fwe.hasListeners(eventName)) {
					fwe.trigger(eventName, {
						'$pickerGroup': elements.$pickerGroup,
						'chooseGroup': chooseGroup
					});
				} else {
					console.error('uninitialized multi-picker type:', pickerType);
				}
			}
		}
	};

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-multi-picker:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});
})(jQuery, fwEvents);
