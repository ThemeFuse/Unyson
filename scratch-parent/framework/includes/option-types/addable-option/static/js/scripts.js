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

	fwEvents.on('fw:options:init', function (data) {
		var $elements = data.$elements.find('.fw-option-type-addable-option:not(.fw-option-initialized)');

		/** Init Add button */
		$elements.on('click', '.fw-option-type-addable-option-add', function(){
			var $button   = $(this);
			var $option   = $button.closest('.fw-option-type-addable-option');
			var $options  = $option.find('.fw-option-type-addable-option-options:first');
			var increment = parseInt($button.attr('data-increment'));

			var $newOption = $(
				$option.find('.default-addable-option-template:first').attr('data-template')
					.split('###-addable-option-increment-###').join(String(increment))
			);

			$button.attr('data-increment', increment + 1);

			$options.append(
				$newOption
			);

			// remove focus form "Add" button to prevent pressing space/enter to add easy many options
			$newOption.find('input,select,textarea').first().focus();

			fwEvents.trigger('fw:options:init', {$elements: $newOption});
		});

		/** Init Remove button */
		$elements.on('click', '.fw-option-type-addable-option-remove', function(){
			$(this).closest('.fw-option-type-addable-option-option').remove();
		});

		$elements.each(function(){
			initSortable($elements.find('.fw-option-type-addable-option-options:first'));
		});

		$elements.addClass('fw-option-initialized');
	});
});