(function($, fwe){

	//waiting for datetime-picker init
	$(document).ready(function(){
		var init = function(){
			var $dateTimeRange = $(this),
				$dateTimeFirstWrapper = $dateTimeRange.find('.fw-option-type-datetime-picker:first'),
				$dateTimeLastWrapper = $dateTimeRange.find('.fw-option-type-datetime-picker:last'),
				$dateTimeFirstInput = $dateTimeFirstWrapper.find('input'),
				$dateTimeLastInput = $dateTimeLastWrapper.find('input'),
				dateTimeFirstPicker = $dateTimeFirstInput.data('xdsoft_datetimepicker'),
				dateTimeLastPicker = $dateTimeLastInput.data('xdsoft_datetimepicker');

			fwe.trigger('fw:options:datetime-range:before-init', {el: $dateTimeRange} );

			var dateMomentFormat = 'YYYY/MM/DD';

			var setMinTimeLimit = function(){
				var firstInputMomentFormat = $dateTimeFirstInput.data('moment-format'),
					lastInputMomentFormat = $dateTimeLastInput.data('moment-format'),
					dateTimeAttrs = $dateTimeLastWrapper.data('datetime-attr');

				if ($.type(dateTimeAttrs.timepicker) === 'boolean' && dateTimeAttrs.timepicker ) {

					if (moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat) === moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat)) {
						dateTimeAttrs.minTime = moment($dateTimeFirstInput.val(), firstInputMomentFormat).add(1, 'hours').format('HH:mm');
					} else {
						dateTimeAttrs.minTime = false;
					}

					if ($dateTimeFirstInput.val() === '' || $dateTimeLastInput.val() === '' ) {
						dateTimeAttrs.minTime = false;
					}

				}

				dateTimeAttrs.value = null;
				if ($dateTimeLastInput.val() !== '') {
					dateTimeLastPicker.setOptions(dateTimeAttrs);
				}
				return true;
			}

			var setMaxTimeLimit = function(){
				var firstInputMomentFormat = $dateTimeFirstInput.data('moment-format'),
					lastInputMomentFormat = $dateTimeLastInput.data('moment-format'),
					dateTimeAttrs = $dateTimeFirstWrapper.data('datetime-attr');

				if ($.type(dateTimeAttrs.timepicker) === 'boolean' && dateTimeAttrs.timepicker ) {
					if (moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat) === moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat)) {
						dateTimeAttrs.maxTime = moment($dateTimeLastInput.val(), lastInputMomentFormat).format('HH:mm');
					} else {
						dateTimeAttrs.maxTime = false;
					}

					if ($dateTimeFirstInput.val() === '' || $dateTimeLastInput.val() === '' ) {
						dateTimeAttrs.maxTime = false;
					}
				}

				dateTimeAttrs.value = null;
				if ($dateTimeFirstInput.val() !== '') {
					dateTimeFirstPicker.setOptions(dateTimeAttrs);
				}
				return true;
			}

			dateTimeFirstPicker.on('open.xdsoft', function(e){

				var firstInputMomentFormat = $dateTimeFirstInput.data('moment-format'),
					lastInputMomentFormat = $dateTimeLastInput.data('moment-format'),
					dateTimeAttrs = $dateTimeFirstWrapper.data('datetime-attr');

				//set max date in first datetime picker
				dateTimeAttrs.maxDate = function(){
					if ($dateTimeLastInput.val())
					{
						if ( $.type($dateTimeFirstWrapper.data('max-date') ) === 'string' )
						{
							if ( moment($dateTimeLastInput.val(), lastInputMomentFormat) > moment($dateTimeFirstWrapper.data('max-date'), dateMomentFormat) )
							{
								return moment($dateTimeFirstWrapper.data('max-date'), dateMomentFormat).format(dateMomentFormat);
							}
						}

						if ( $.type($dateTimeFirstWrapper.data('min-date') ) === 'string' )
						{
							if ( moment($dateTimeLastInput.val(), lastInputMomentFormat) < moment($dateTimeFirstWrapper.data('min-date'), dateMomentFormat) )
							{
								return moment($dateTimeFirstWrapper.data('min-date'), dateMomentFormat).format(dateMomentFormat);
							}
						}

						return moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat);
					}

					if ( $.type($dateTimeFirstWrapper.data('max-date') ) === 'string' )
					{
						return moment($dateTimeFirstWrapper.data('max-date'), dateMomentFormat).format(dateMomentFormat);
					}

					return false;
				}();

				//set first datetime picker default value
				dateTimeAttrs.value = function(){
					if (!$dateTimeFirstInput.val() && $dateTimeLastInput.val() ) {
						return moment($dateTimeLastInput.val(), lastInputMomentFormat).subtract(1, 'hours').format(firstInputMomentFormat);
					}

					var minDate = dateTimeAttrs.minDate
					if ($.type($dateTimeFirstWrapper.data('min-date')) === 'string'
						&& moment($dateTimeFirstWrapper.data('min-date'), dateMomentFormat) > moment(minDate, dateMomentFormat) ) {
						minDate = moment($dateTimeFirstWrapper.data('min-date'), dateMomentFormat).format(dateMomentFormat);
					}

					if ($dateTimeFirstInput.val() && !$dateTimeLastInput.val()) {
						if ( 'undefined' !== typeof minDate && moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat) < moment(minDate, dateMomentFormat).format(dateMomentFormat) ) {
							return moment(minDate, dateMomentFormat).format(firstInputMomentFormat);
						}

						if ( 'undefined' !== typeof dateTimeAttrs.maxDate && moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat) > moment(dateTimeAttrs.maxDate, dateMomentFormat).format(dateMomentFormat) ) {
							return moment(dateTimeAttrs.maxDate, dateMomentFormat).format(firstInputMomentFormat);
						}
					}

					if ($dateTimeFirstInput.val() && $dateTimeLastInput.val()) {
						if ( moment($dateTimeFirstInput.val(), firstInputMomentFormat) > moment($dateTimeLastInput.val(), lastInputMomentFormat) ) {

							if ('undefined' !== typeof minDate && moment($dateTimeFirstInput.val(), firstInputMomentFormat) < moment(minDate, dateMomentFormat)
								&& moment($dateTimeLastInput.val(), lastInputMomentFormat) < moment(minDate, dateMomentFormat)) {
								return moment(minDate, dateMomentFormat).format(firstInputMomentFormat);
							}

							if ('undefined' !== typeof minDate && moment($dateTimeLastInput.val(), lastInputMomentFormat) < moment(minDate, dateMomentFormat)) {
								return moment(minDate, dateMomentFormat).format(firstInputMomentFormat);
							}
							return moment($dateTimeLastInput.val(), lastInputMomentFormat).subtract(1, 'hours').format(firstInputMomentFormat);
						}

						if  ( moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat) > moment(minDate, dateMomentFormat).format(dateMomentFormat)
							&& moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat) < moment(minDate, dateMomentFormat).format(dateMomentFormat)
							) {
							return moment($dateTimeLastInput.val(), lastInputMomentFormat).subtract(1, 'hours').format(firstInputMomentFormat);
						}

						if ( 'undefined' !== typeof minDate && moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat) < moment(minDate, dateMomentFormat).format(dateMomentFormat)) {
							return moment(minDate, dateMomentFormat).format(firstInputMomentFormat);
						}
					}
					return null;
				}();

				dateTimeFirstPicker.setOptions(dateTimeAttrs);
				setMaxTimeLimit();

				//$dateTimeFirstInput.data('xdsoft_datetimepicker',dateTimeFirstPicker );
				//fwe.trigger('fw:datetime-range:first:open', { dateTimePicker: dateTimeFirstPicker, dateTimeInput: $dateTimeFirstInput }); ????
			});

			$dateTimeFirstInput.on('change', setMaxTimeLimit );
			$dateTimeLastInput.on('change', setMinTimeLimit );

			dateTimeLastPicker.on('open.xdsoft', function(e){
				var firstInputMomentFormat = $dateTimeFirstInput.data('moment-format'),
					lastInputMomentFormat = $dateTimeLastInput.data('moment-format'),
					dateTimeAttrs = $dateTimeLastWrapper.data('datetime-attr');


				//set last datetime picker minDate
				dateTimeAttrs.minDate = function(){
					if ($dateTimeFirstInput.val()) {

						if ( $.type($dateTimeLastWrapper.data('min-date') ) === 'string' )
						{
							if (moment($dateTimeFirstInput.val(), firstInputMomentFormat) < moment($dateTimeLastWrapper.data('min-date'), dateMomentFormat))
							{
								return moment($dateTimeLastWrapper.data('min-date'), dateMomentFormat).format(dateMomentFormat);
							}
						}

						if ( $.type($dateTimeLastWrapper.data('max-date') ) === 'string' )
						{
							if ( moment($dateTimeFirstInput.val(), firstInputMomentFormat) > moment($dateTimeLastWrapper.data('max-date'), dateMomentFormat) )
							{
								return moment($dateTimeFirstWrapper.data('max-date'), dateMomentFormat).format(dateMomentFormat);
							}
						}

						return moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat);
					}

					if ( $.type($dateTimeLastWrapper.data('min-date') ) === 'string' )
					{
						return moment($dateTimeLastWrapper.data('min-date'), dateMomentFormat).format(dateMomentFormat);
					}

					return false;
				}();

				//set last datetime picker default value
				dateTimeAttrs.value = function(){

					if ($dateTimeFirstInput.val() && !$dateTimeLastInput.val()) {
						return moment($dateTimeFirstInput.val(), firstInputMomentFormat).add(1, 'hours').format(lastInputMomentFormat);
					}

					var maxDate = dateTimeAttrs.maxDate
					if ($.type($dateTimeLastWrapper.data('max-date')) === 'string'
						&& moment($dateTimeLastWrapper.data('max-date'), dateMomentFormat) < moment(maxDate, dateMomentFormat) ) {
						maxDate = moment($dateTimeLastWrapper.data('max-date'), dateMomentFormat).format(dateMomentFormat);
					}

					if (!$dateTimeFirstInput.val() && $dateTimeLastInput.val()) {
						if ( 'undefined' !== typeof maxDate && moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat) > moment(maxDate, dateMomentFormat).format(dateMomentFormat) ) {
							return moment(maxDate, dateMomentFormat).format(lastInputMomentFormat);
						}

						if ( 'undefined' !== typeof dateTimeAttrs.minDate && moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat) < moment(dateTimeAttrs.minDate, dateMomentFormat).format(dateMomentFormat) ) {
							return moment(dateTimeAttrs.minDate, dateMomentFormat).format(lastInputMomentFormat);
						}
					}

					if ($dateTimeFirstInput.val() && $dateTimeLastInput.val()) {
						if ( moment($dateTimeFirstInput.val(), firstInputMomentFormat) > moment($dateTimeLastInput.val(), lastInputMomentFormat) ) {

							if ( 'undefined' !== typeof maxDate &&
								moment($dateTimeLastInput.val(), lastInputMomentFormat) > moment(maxDate, dateMomentFormat)
								&& moment($dateTimeFirstInput.val(), firstInputMomentFormat) > moment(maxDate, dateMomentFormat)) {
								return moment(maxDate, dateMomentFormat).format(lastInputMomentFormat);
							}

							if ( 'undefined' !== typeof maxDate && moment($dateTimeFirstInput.val(), firstInputMomentFormat) > moment(maxDate, dateMomentFormat)) {
								return moment(maxDate, dateMomentFormat).format(lastInputMomentFormat);
							}

							return moment($dateTimeFirstInput.val(), firstInputMomentFormat).add(1, 'hours').format(lastInputMomentFormat);
						}

						if ( moment($dateTimeFirstInput.val(), firstInputMomentFormat).format(dateMomentFormat) < moment(maxDate, dateMomentFormat).format(dateMomentFormat)
							&& moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat) > moment(maxDate, dateMomentFormat).format(dateMomentFormat)
							) {
							return moment($dateTimeFirstInput.val(), firstInputMomentFormat).add(1, 'hours').format(lastInputMomentFormat);
						}

						if ( 'undefined' !== typeof maxDate && moment($dateTimeLastInput.val(), lastInputMomentFormat).format(dateMomentFormat) > moment(maxDate, dateMomentFormat).format(dateMomentFormat)) {
							return moment(maxDate, dateMomentFormat).format(lastInputMomentFormat);
						}
					}

					return null;
				}();

				dateTimeLastPicker.setOptions(dateTimeAttrs);
				setMinTimeLimit();

				//$dateTimeLastInput.data('xdsoft_datetimepicker', dateTimeLastPicker);
				//fwe.trigger('fw:datetime-range:last:open', { dateTimePicker: dateTimeLastPicker, dateTimeInput: $dateTimeLastInput }); ????
			});
		}

		fwe.on('fw:options:init', function(data) {
			data.$elements
				.find('.fw-option-type-datetime-range:not(.fw-option-initialized)').each(init)
				.addClass('fw-option-initialized');
		});

	});

})(jQuery, fwEvents);

