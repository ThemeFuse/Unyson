jQuery(document).ready(function ($) {
	function initSortable ($options) {
		try {
			$options.sortable('destroy');
		} catch (e) {
			// happens when sortable was not initialized before
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
			}
		});
	}

	var methods = {
		/** Make full/prefixed event name from short name */
		makeEventName: function (shortName) {
			return 'fw:option-type:addable-option:' + shortName;
		}
	};

	var optionClass = '.fw-option-type-addable-option';

	fwEvents.on('fw:options:init', function (data) {
		var $elements = data.$elements.find(optionClass +':not(.fw-option-initialized)');

		/** Init Add button */
		$elements.on('click', optionClass +'-add', function(){
			var $button   = $(this);
			var $option   = $button.closest(optionClass);
			var $options  = $option.find(optionClass +'-options:first');
			var increment = parseInt($button.attr('data-increment'));

			var $newOption = $(
				$option.find('.default-addable-option-template:first').attr('data-template')
					.split('###-addable-option-increment-###').join(String(increment))
			);

			// animation
			{
				$newOption.addClass('fw-animation-zoom-in');

				setTimeout(function(){
					$newOption.removeClass('fw-animation-zoom-in');
				}, 300);
			}

			$button.attr('data-increment', increment + 1);

			$options.append(
				$newOption
			);

			// remove focus form "Add" button to prevent pressing space/enter to add easy many options
			$newOption.find('input,select,textarea').first().focus();

			fwEvents.trigger('fw:options:init', {$elements: $newOption});

			$option.trigger(methods.makeEventName('option:init'), {$option: $newOption});
		});

		/** Init Remove button */
		$elements.on('click', optionClass +'-remove', function(){
			$(this).closest(optionClass +'-option').remove();
		});

		$elements.each(function(){
			initSortable($elements.find(optionClass +'-options:first'));
		});

		$elements.addClass('fw-option-initialized');
	});
});