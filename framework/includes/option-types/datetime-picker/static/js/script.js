(function($, fwe) {

		var init = function() {
			var $container = $(this),
				$input = $container.find('.fw-option-type-text'),
				dateTimePickerAttr = $container.data('datetime-attr'),
				data = {options: dateTimePickerAttr, el: $input, container: $container };

			fwe.trigger('fw:options:datetime-picker:before-init', data);
			$input.datetimepicker(data.options);
		}

		fwe.on('fw:options:init', function(data) {
			data.$elements
				.find('.fw-option-type-datetime-picker').each(init)
				.addClass('fw-option-initialized');
		});

})(jQuery, fwEvents);
