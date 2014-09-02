jQuery(function ($) {
	var categoryPrefix = 'dialog-icon-category-';
	var beginsWithPrefix = new RegExp('^' + categoryPrefix);

	fwEvents.on('fw:options:init', function (data) {
		var optionSelector = '.fw-option-type-icon';
		var $options = data.$elements.find(optionSelector +':not(.initialized)');

		// click on an icon
		$options.on('click', '.fa', function () {
			$(this).addClass('active').siblings().removeClass('active');
			$(this).closest(optionSelector).find('input').val( $(this).data('value') );
		});

		// category select, show/hide categories
		{
			$options.find('[data-type=dialog-icon-category]')
				.on('change', function () {
					var $this = $(this);
					var $container = $this.closest(optionSelector).find('.fontawesome-icon-list');

					$.each(($container.attr('class') || '').split(/\s+/), function (index, cssClass) {
						if (cssClass.match(beginsWithPrefix)) {
							$container.removeClass(cssClass);
						}
					});

					$container.addClass(categoryPrefix + $this.val());
				})
				.trigger('change');
		}

		$options.addClass('initialized');
	});
});