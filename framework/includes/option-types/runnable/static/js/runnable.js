(function ($, fwe) {
	var init = function () {
		var $hidden = $(this),
			$button = $hidden.parent().find('.runnable-button'),
			$textDiv = $hidden.parent().find('.runnable-last-run');

		var showDate = function ($timestamp) {
			$textDiv.text('Last run was ' + moment($timestamp, 'X').fromNow());
		}

		if (moment($hidden.val(), 'X', true).isValid()) {
			showDate($hidden.val());
		}

		$button.on('click', function (e) {
			e.preventDefault();

			$.ajax({
				type: "post",
				dataType: "json",
				url: ajaxurl,
				data: {action: 'fw_runnable', callback: $(this).data('callback')}
			}).done(function (data) {
				if (data.success) {
					var $timestamp = moment().format('X');
					$hidden.val($timestamp);
					showDate($timestamp);
				}

				fwe.trigger('fw:option-type:runnable:change', {
					$element: $hidden,
					response: data
				});
			});
		});
	};

	fwe.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-runnable:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});
})(jQuery, fwEvents);
