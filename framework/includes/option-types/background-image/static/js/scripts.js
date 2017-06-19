jQuery(document).ready(function ($) {
	var optionTypeClass = 'fw-option-type-background-image';
	var eventNamePrefix = 'fw:option-type:background-image:';

	fw.options.register('background-image', {
		startListeningForChanges: jQuery.noop,
		getValue: function (optionDescriptor) {
			return {
				value: getValueForEl(optionDescriptor.el),
				optionDescriptor: optionDescriptor
			}
		}
	});

	fwEvents.on('fw:options:init', function (data) {
		var $options = data.$elements.find('.'+ optionTypeClass +':not(.initialized)');

		$options.toArray().map(function (el) {
			/**
			 * Here we start listening to events triggered by inner option
			 * types. We may receive events from 3 nested option types here:
			 *
			 * 1. radio
			 * 2. image-picker
			 * 3. upload
			 */
			fw.options.on.changeByContext(el, function (optionDescriptor) {
				if (optionDescriptor.type === 'radio') {
					var $predefined = $(
						optionDescriptor.el
					).closest('.fw-inner').find('.predefined');

					var $custom = $(
						optionDescriptor.el
					).closest('.fw-inner').find('.custom');

					getValueForEl(el).then(function (value) {
						var type = value.type

						if (type === 'custom') {
							$predefined.hide();
							$custom.show();
						} else {
							$predefined.show();
							$custom.hide();
						}
					})

				}

				triggerChangeAndInferValueFor(
					// Here we refer to the optionDescriptor.context
					// as to the `background-image` option type container
					optionDescriptor.context
				)
			});
		});

		// route inner image-picker events as this option events
		{
			$options.on(
				'fw:option-type:image-picker:clicked',
				'.fw-option-type-image-picker',
				function(e, data) {
					jQuery(this).trigger(eventNamePrefix + 'clicked', data);
				}
			);

			$options.on(
				'fw:option-type:image-picker:changed',
				'.fw-option-type-image-picker',
				function(e, data) {
					jQuery(this).trigger(eventNamePrefix + 'changed', data);
				}
			);
		}

		$options.addClass('initialized');

		function triggerChangeAndInferValueFor (el) {
			getValueForEl(el).then(function (value) {
				fw.options.trigger.changeForEl(el, {
					value: value
				});
			})

		}

	});

	function getValueForEl (el) {
		var promise = $.Deferred();

		var optionDescriptor = fw.options.getOptionDescriptor(el);

		fw.options.getContextValue(
			optionDescriptor.el
		).then(function (value) {
			promise.resolve(value.value);
		});

		return promise;
	}

});
