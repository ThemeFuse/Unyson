(function ($) {
	var fw_option_multi_select_initialize = function(item) {
		var population = item.attr('data-population');
		var source = item.attr('data-source');
		var limit = parseInt(item.attr('data-limit'));
		var xhr;

		item.selectize({
			maxItems: ( limit > 0 ) ? limit : null,
			delimiter: '/*/',
			valueField: 'val',
			labelField: 'title',
			searchField: 'title',
			options: JSON.parse(item.attr('data-options')),
			create: false,
			onType: function (value) {
				if (population == 'array') {
					return;
				}

				if (value.length < 2) {
					return;
				}

				this.load(function (callback) {
					xhr && xhr.abort();

					var data = {
						action: 'admin_action_get_ajax_response',
						data: {
							string: value,
							type: population,
							names: source
						}
					};

					xhr = $.post(
						ajaxurl,
						data,
						function (response) {
							callback(response.data)
						}
					)
				});

			}
		});
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-multi-select:not(.initialized)')
			.each(function () {
				fw_option_multi_select_initialize($(this));
			});

		/*
		 * WARNING:
		 *
		 * data.$elements.find is intentionally looked up twice instead of cached
		 * this is done because when fw_option_multi_select_initialize is called
		 * the selectize plugin inserts an element which copies the
		 * `fw-option-type-multi-select` class, thus making the cache invalid.
		 */
		data.$elements
			.find('.fw-option-type-multi-select:not(.initialized)')
			.addClass('initialized');
	});
})(jQuery);
