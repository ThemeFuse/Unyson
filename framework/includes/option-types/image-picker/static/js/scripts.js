(function(){
	var optionTypeClass = 'fw-option-type-image-picker';
	var eventNamePrefix = 'fw:option-type:image-picker:';

	jQuery(document).ready(function ($) {
		/** Init image_picker options */
		fwEvents.on('fw:options:init', function (data) {
			var $elements = data.$elements.find('.'+ optionTypeClass +':not(.fw-option-initialized)');

			if (!$elements.length) {
				return;
			}

			$elements.find('select')
				.imagepicker({
					clicked: function(options) {
						var $this = $(this);
						var value = $this.val();
						var data  = $this.find('option[value="'+ value +'"]').data('extra-data');

						$this.closest('.'+ optionTypeClass).trigger(eventNamePrefix +'clicked', {
							options : options,
							value   : value,
							data    : (typeof data !== 'undefined') ? data : false
						});
					},
					changed: function (oldValues, newValues) {
						var $this = $(this);

						$this.closest('.'+ optionTypeClass).trigger(eventNamePrefix +'changed', {
							oldValues : oldValues,
							newValues : newValues
						});
					}
				})
				.closest('.'+ optionTypeClass).find('.image_picker_selector .image_picker_image').each(function(){
					var $this = $(this);
					var largeImageAttr = $this.data('large-img-attr');

					if (largeImageAttr) {
						$this.qtip({
							content: $('<div></div>').append(
								$('<img/>').attr(largeImageAttr).addClass(optionTypeClass +'-large-image')
							).html(),
							position: {
								at: 'top center',
								my: 'bottom center',
								viewport: $('body'),
								adjust: {
									y: -5
								}
							},
							style: {
								classes: 'qtip-fw',
								tip: {
									width: 12,
									height: 5
								}
							},
							show: {
								effect: function(offset) {
									$(this).fadeIn(300);

									// fix tip position
									setTimeout(function(){
										offset.elements.tooltip.css('top',
											(parseInt(offset.elements.tooltip.css('top')) + 5) + 'px'
										);
									}, 12);
								}
							},
							hide: {
								effect: function() {
									$(this).fadeOut(300);
								}
							}
						});
					}
				});

			$elements.addClass('fw-option-initialized');
		});
	});
})();
