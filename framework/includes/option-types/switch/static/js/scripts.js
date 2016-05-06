jQuery(document).ready(function ($) {
	var optionTypeClass = 'fw-option-type-switch',
		customEventPrefix = 'fw:option-type:switch:';

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.'+ optionTypeClass +':not(.fw-option-initialized)')
			.addClass('fw-option-initialized')
			.find('input[type="checkbox"]')
			.on('change', function(){
				var $this = $(this),
					checked = $this.prop('checked'),
					value = $this.attr('data-switch-'+ (checked ? 'right' : 'left') +'-value-json');

				$this.val(value);

				if (checked) {
					$this.prev('input[type="hidden"]').remove();
				} else {
					/**
					 * When checkbox is not checked, it is not sent in POST so create a hidden input for that
					 */
					$('<input>').attr({
						'type': 'hidden',
						'name': $this.attr('name'),
						'value': value
					}).insertBefore($this);
				}

				$this.closest('.'+ optionTypeClass).trigger(customEventPrefix +'change', {
					value: JSON.parse(value)
				});
			})
			.on('change update:color', function(){
				var $this = $(this),
					isRight = $this.prop('checked'),
					$option = $this.closest('.'+ optionTypeClass),
					color = $option.attr('data-'+ (isRight ? 'right' : 'left') +'-color') || '';

				$option.find('.switch-dot span').css('background-color', color);
				$option.find('.switch-label-'+ (isRight ? 'right' : 'left')).css('color', color);
			})
			.adaptiveSwitch()
			.trigger('update:color');
	});
});