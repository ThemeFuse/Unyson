(function($, fwe) {


	var init = function() {
		var $eventOptionEl = $(this),
			$allDaySwitch = $eventOptionEl.find('[id="fw-option-general-event-all_day"] input'),
			$eventsPopup = $eventOptionEl.find('.fw-backend-option-input-type-addable-popup');


		/**
		 * Update date_time format consider checkbox value
		 */
		fwe.on('fw:options:datetime-range:before-init', function(data){
			var $dateTimeFirstWrapper = data.el.find('.fw-option-type-datetime-picker:first'),
				$dateTimeLastWrapper = data.el.find('.fw-option-type-datetime-picker:last'),
				$dateTimeFirstInput = data.el.find('input'),
				$dateTimeLastInput = data.el.find('input'),
				dateTimeFirstAttrs = $dateTimeFirstWrapper.data('datetime-attr'),
				dateTimeLastAttrs = $dateTimeLastWrapper.data('datetime-attr'),
				format = 'Y/m/d H:i',
				newMomentFormat = 'YYYY/MM/DD HH:mm',
				timepicker = true;

			if ($allDaySwitch.is(':checked')) {
				format = 'Y/m/d';
				newMomentFormat = 'YYYY/MM/DD';
				timepicker = false;

			}

			dateTimeFirstAttrs.format = format;
			dateTimeLastAttrs.format = format;
			dateTimeFirstAttrs.timepicker = timepicker;
			dateTimeLastAttrs.timepicker = timepicker;

			$dateTimeLastWrapper.data('datetime-attr',dateTimeLastAttrs);
			$dateTimeFirstWrapper.data('datetime-attr',dateTimeFirstAttrs);

			$dateTimeFirstInput.data('moment-format', newMomentFormat);
			$dateTimeLastInput.data('moment-format', newMomentFormat);

		});

		/**
		 * Update values with new format
		 */
		fwe.on('fw:options:datetime-picker:before-init', function(data){

			if (data.el.parents('.fw-option-type-datetime-range').length){
				var newMomentFormat = 'YYYY/MM/DD HH:mm';

				if ($allDaySwitch.is(':checked')) {
					newMomentFormat = 'YYYY/MM/DD';
				}

				if (data.el.val()) {
					var value = moment(data.el.val(), data.el.data('moment-format')).format(newMomentFormat);
					data.el.val(value);
					data.options.value = value;
				}
			}

		});

		$allDaySwitch.on('change', function(){
			var momentFormat = 'YYYY/MM/DD HH:mm',
				allDay = false;

			if ($allDaySwitch.is(':checked')) {
				momentFormat = 'YYYY/MM/DD';
				allDay = true;
			}

			$eventsPopup.find('.item:not(.disabled)').each(function(){
				var data = $.parseJSON($(this).find('input').val());
				if (data.event_date_range.from !== "" && data.event_date_range.to !== "" ) {
					var from = new Date(data.event_date_range.from),
						to = new Date(data.event_date_range.to);

					if (allDay) {
						$(this).find('input').data('from-time', moment(from.toISOString()).format('HH:mm') );
						$(this).find('input').data('to-time', moment(to.toISOString()).format('HH:mm'));
					} else {
						var fromTime = moment($(this).find('input').data('from-time'), 'HH:mm').toDate(),
							toTime = moment($(this).find('input').data('to-time'), 'HH:mm').toDate();

						from.setHours(fromTime.getHours());
						to.setHours(toTime.getHours());
						from.setMinutes(fromTime.getMinutes());
						to.setMinutes(toTime.getMinutes());

					}

					data.event_date_range.from = moment(from.toISOString()).format(momentFormat);
					data.event_date_range.to =  moment(to.toISOString()).format(momentFormat);

					$(this).find('input').val(JSON.stringify(data));
					$(this).find('.content').text(data.event_date_range.from + ' - ' + data.event_date_range.to);
				}
			})

		});

	}

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-event').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);



