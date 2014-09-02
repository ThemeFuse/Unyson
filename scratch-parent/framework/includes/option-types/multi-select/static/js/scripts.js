function fw_option_multi_select_initialize(item) {
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

				xhr = jQuery.post(
					ajaxurl,
					data,
					function (response) {
						callback(response.data)
					}
				)
			});

		}
	});
}

jQuery(document).ready(function () {
	fwEvents.on('fw:options:init', function (data) {

		var obj = data.$elements.find('.fw-option-type-multi-select:not(.initialized)');

		obj.each(function () {
			fw_option_multi_select_initialize(jQuery(this));
		});

		obj.addClass('initialized');
	});
});