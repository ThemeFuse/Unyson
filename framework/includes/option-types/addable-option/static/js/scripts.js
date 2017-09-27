jQuery(document).ready(function ($) {
	var optionClass = '.fw-option-type-addable-option';

	function initSortable ($options) {
		try {
			$options.sortable('destroy');
		} catch (e) {
			// happens when sortable was not initialized before
		}

		if (! $options.first().closest(optionClass).hasClass('is-sortable')) {
			return false;
		}

		var isMobile = $(document.body).hasClass('mobile');

		$options.sortable({
			items: '> tbody > tr',
			handle: 'td:first',
			cursor: 'move',
			placeholder: 'sortable-placeholder',
			delay: ( isMobile ? 200 : 0 ),
			distance: 2,
			tolerance: 'pointer',
			forcePlaceholderSize: true,
			axis: 'y',
			start: function(e, ui){
				// Update the height of the placeholder to match the moving item.
				{
					var height = ui.item.outerHeight();

					ui.placeholder.height(height);
				}
			},
			update: function(){
				$(this).closest(optionClass).trigger('change'); // for customizer
				fw.options.trigger.changeForEl($(this).closest(optionClass));
			}
		});
	}

	var methods = {
		/** Make full/prefixed event name from short name */
		makeEventName: function (shortName) {
			return 'fw:option-type:addable-option:' + shortName;
		}
	};

	fwEvents.on('fw:options:init', function (data) {
		var $elements = data.$elements.find(optionClass +':not(.fw-option-initialized)');

		$elements.toArray().map(function (el) {
			// Trigger change when one of the underlying contexts change
			fw.options.on.change(function (data) {
				if (! $(data.context).is(
					'[data-fw-option-type="addable-option"] tr.fw-option-type-addable-option-option'
				)) {
					return;
				}

				// Listen to just its own virtual contexts
				if (! el.contains(data.context)) {
					return;
				}

				fw.options.trigger.changeForEl(el);
			});
		});

		/** Init Add button */
		$elements.on('click', optionClass +'-add', function(){
			var $button   = $(this);
			var $option   = $button.closest(optionClass);
			var $options  = $option.find(optionClass +'-options:first');
			var increment = parseInt($button.attr('data-increment'));

			var $newOption = $(
				$option.find('.default-addable-option-template:first').attr('data-template')
					.split( $button.attr('data-increment-placeholder') ).join( String(increment) )
			);

			// animation
			{
				$newOption.addClass('fw-animation-zoom-in');

				setTimeout(function(){
					$newOption.removeClass('fw-animation-zoom-in');
				}, 300);
			}

			$button.attr('data-increment', increment + 1);

			$options.append($newOption);

			// Re-render wp-editor
			if (
				window.fwWpEditorRefreshIds
				&&
				$newOption.find('.fw-option-type-wp-editor:first').length
			) {
				fwWpEditorRefreshIds(
					$newOption.find('.fw-option-type-wp-editor textarea:first').attr('id'),
					$newOption
				);
			}

			// remove focus form "Add" button to prevent pressing space/enter to add easy many options
			$newOption.find('input,select,textarea').first().focus();

			fwEvents.trigger('fw:options:init', {$elements: $newOption});

			$option.trigger(methods.makeEventName('option:init'), {$option: $newOption});
			fw.options.trigger.changeForEl($option);
		});

		/** Init Remove button */
		$elements.on('click', optionClass +'-remove', function(){
			fw.options.trigger.changeForEl($(this).closest(
				'[data-fw-option-type="addable-option"]'
			));

			$(this).closest(optionClass +'-option').remove();
		});

		$elements.each(function(){
			initSortable($elements.find(optionClass +'-options:first'));
		});

		$elements.addClass('fw-option-initialized');
	});

	fw.options.register('addable-option', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			var promise = $.Deferred();

			fw.whenAll(
				$(optionDescriptor.el).find(
					'table.fw-option-type-addable-option-options'
				).first().find(
					'> tbody > .fw-backend-options-virtual-context'
				).toArray().map(fw.options.getContextValue)
			).then(function (valuesAsArray) {
				promise.resolve({
					value: valuesAsArray.map(function (singleContextValue) {
						return _.values(singleContextValue.value)[0];
					}),

					optionDescriptor: optionDescriptor
				})
			});

			return promise;
		}
	})
});
