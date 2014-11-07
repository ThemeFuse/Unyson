jQuery(document).ready(function ($) {
	var optionTypeClass = '.fw-option-type-addable-box';

	var methods = {
		/** Make full/prefixed event name from short name */
		makeEventName: function(shortName) {
			return 'fw:option-type:addable-box:'+ shortName;
		},
		/** Create object with useful data about box for event data */
		getBoxDataForEvent: function($box) {
			var data = {};

			data.$box       = $box;
			data.$controls  = $box.find('.fw-option-box-controls:first');
			data.$options   = $box.find('.fw-option-box-options:first');

			data.$box       = $box.find('.fw-postbox:first');
			data.$title     = data.$box.find('> h3.hndle:first');
			data.$titleText = data.$title.find('> span:first');

			return data;
		},
		/** Make boxes to be sortable */
		reInitSortable: function ($boxes) {
			try {
				$boxes.sortable('destroy');
			} catch (e) {
				// happens when sortable was not initialized before
			}

			var isMobile = $(document.body).hasClass('mobile');

			$boxes.sortable({
				items: '> .fw-option-box',
				handle: '.hndle:first',
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

						height -= 2; // Subtract 2 for borders

						ui.placeholder.height(height);
					}
				}
			});
		},
		/** Init boxes controls */
		initControls: function ($boxes) {
			$boxes
				.find('.fw-option-box-controls:not(.initialized)')
				.on('click', '.fw-option-box-control', function(e){
					e.preventDefault();
					e.stopPropagation(); // prevent open/close of the box (when the link is in box title bar)

					var $control  = $(this);
					var controlId = $control.attr('data-control-id');

					switch (controlId) {
						case 'delete':
							var $option = $control.closest(optionTypeClass);

							$control.closest('.fw-option-box').remove();

							methods.checkLimit($option);
							break;
						default:
							// custom control. trigger event for others to handle this
							$control.closest(optionTypeClass).trigger(
								methods.makeEventName('.fw-options-tabs-wrapper .fw-options-tabs-contents'), {
									controlId: controlId,
									$control: $control,
									box: methods.getBoxDataForEvent($control.closest('.box'))
								}
							);
					}
				})
				.addClass('initialized')
				.find('.fw-option-box-control').off('click'); // remove e.stopPropagation() added by /wp-admin/js/postbox.min.js
		},
		checkLimit: function($option) {
			var $button = $option.find('> .fw-option-boxes-controls .fw-option-boxes-add-button');
			var limit = fw.intval($button.attr('data-limit'));

			if (limit > 0 && $option.find('> .fw-option-boxes > .fw-option-box').length >= limit) {
				$button.addClass('fw-hidden');
			} else {
				$button.removeClass('fw-hidden');
			}
		}
	};

	fwEvents.on('fw:options:init', function (data) {
		var $elements = data.$elements.find(optionTypeClass +':not(.fw-option-initialized)');

		/** Init Add button */
		$elements.on('click', '> .fw-option-boxes-controls > .fw-option-boxes-add-button', function(){
			var $button   = $(this);
			var $option   = $button.closest(optionTypeClass);
			var $boxes    = $option.find('.fw-option-boxes:first');
			var increment = parseInt($button.attr('data-increment'));

			var $newBox = $(
				$option.find('.default-box-template:first').attr('data-template')
					.split('###-addable-box-increment-###').join(String(increment))
			);

			$button.attr('data-increment', increment + 1);

			// animation
			{
				$newBox.addClass('fw-animation-zoom-in');

				setTimeout(function(){
					$newBox.removeClass('fw-animation-zoom-in');
				}, 300);
			}

			$boxes.append(
				$newBox
			);

			methods.initControls($newBox);
			methods.reInitSortable($boxes);

			// remove focus form "Add" button to prevent pressing space/enter to add easy many boxes
			$newBox.find('input,select,textarea').first().focus();

			fwEvents.trigger('fw:options:init', {$elements: $newBox});

			var box = methods.getBoxDataForEvent($newBox);

			$option.trigger(methods.makeEventName('box:init'), {box: box});

			methods.checkLimit($option);
		});

		// close postboxes and attach event listener
		$elements.find('> .fw-option-boxes > .fw-option-box > .fw-postbox').addClass('closed');

		methods.initControls($elements);

		$elements.each(function(){
			methods.checkLimit($(this));
		});

		$elements.addClass('fw-option-initialized');

		setTimeout(function(){
			// executed later, after .sortable('destroy') from backend-options.js
			methods.reInitSortable($elements.find('.fw-option-boxes'));

			// execute box:init event for existing boxes
			$elements.each(function(){
				var $option = $(this);

				$option.find('> .fw-option-boxes > .fw-option-box').each(function(){
					$option.trigger(methods.makeEventName('box:init'), {
						box: methods.getBoxDataForEvent($(this))
					});
				})
			});
		}, 100);
	});
});