(function ($, _, fwEvents, window) {
	var popup = function () {
		var $this = $(this),
			$defaultItem = $this.find('.item.default'),
			nodes = {
				$optionWrapper: $this,
				$itemsWrapper: $this.find('.items-wrapper'),
				$disabledItem: $defaultItem.clone().removeClass('default').addClass('disabled'),
				getDefaultItem: function () {
					return $defaultItem.clone().removeClass('default');
				}
			},
			data = JSON.parse(nodes.$optionWrapper.attr('data-for-js')),
			utils = {
				modal: new fw.OptionsModal({
					title: data.title,
					options: data.options,
					size : data.size
				}),
				editItem: function (item, values) {
					var $input = item.find('input'),
						val = $input.val();

					$input.val( values = JSON.stringify( values ) );

					if (val != values) {
						$this.trigger('fw:option-type:popup:change');
						$input.trigger('change');
					}
				}
			};

		nodes.$itemsWrapper.on('click', '.item > .button', function (e) {
			e.preventDefault();

			var values = {},
				$item = $(this).closest('.item'),
				$input = $item.find('input');

			if ($input.length && $input.val().length ) {
				values = JSON.parse($input.val());
			}

			utils.modal.set('edit', true);
			utils.modal.set('values', values, {silent: true});
			utils.modal.set('itemRef', $item);
			utils.modal.open();
		});

		utils.modal.on({
			'change:values': function (modal, values) {
				utils.editItem(utils.modal.get('itemRef'), values);

                fw.options.trigger.changeForEl(utils.modal.get('itemRef'), {
					value: values
				});

				fwEvents.trigger('fw:option-type:popup:change', {
					element: $this,
					values: values
				});
			},
			'open': function () {
				$this.trigger('fw:option-type:popup:open');

				if (data['custom-events']['open']) {
					fwEvents.trigger('fw:option-type:popup:custom:' + data['custom-events']['open'], {
						element: $this,
						modal: utils.modal
					});
				}
			},
			'close': function () {
				$this.trigger('fw:option-type:popup:close');

				if (data['custom-events']['close']) {
					fwEvents.trigger('fw:option-type:popup:custom:' + data['custom-events']['close'], {
						element: $this,
						modal: utils.modal
					});
				}
			},
			'render': function () {
				$this.trigger('fw:option-type:popup:render');

				if (data['custom-events']['render']) {
					fwEvents.trigger('fw:option-type:popup:custom:' + data['custom-events']['render'], {
						element: $this,
						modal: utils.modal
					});
				}
			}
		});
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-popup:not(.fw-option-initialized)').each(popup)
			.addClass('fw-option-initialized');
	});

	fw.options.register('popup', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			return {
				value: JSON.parse(
					$(optionDescriptor.el).find('[type="hidden"]').val() || '""'
				),

				optionDescriptor: optionDescriptor
			}
		}
	});
})(jQuery, _, fwEvents, window);
