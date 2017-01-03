(function ($) {
	var xhr,
		optionsCache = {},
		ajaxAutocompleteCallback = _.throttle(function(selectize, value, population, source, hash){
			selectize.load(function(callback){
				xhr && xhr.abort();

				xhr = $.post(
					ajaxurl,
					{
						action: 'fw_option_type_multi_select_autocomplete',
						data: {
							string: value,
							type: population,
							names: source
						}
					},
					function (response) {
						if (!response.success) {
							console.error('multi-select ajax error', response);
							callback([]);
							return;
						}

						callback(response.data);

						optionsCache[hash] = []; // transform object to array
						$.each(selectize.options, function(i, o){
							optionsCache[hash].push(o);
						});
					}
				);
			});
		}, 300);

	function init() {
		var $this = $(this);

		$this.one('fw:option-type:multi-select:init', function () {
			var population = $this.attr('data-population'),
				source = $this.attr('data-source'),
				limit = parseInt($this.attr('data-limit')),
				hash = fw.md5(JSON.stringify([population, source])),
				options = (
					typeof optionsCache[hash] == 'undefined'
						? JSON.parse($this.attr('data-options'))
						: optionsCache[hash]
				);

			$this.selectize({
				maxItems: ( limit > 0 ) ? limit : null,
				delimiter: '/*/',
				valueField: 'val',
				labelField: 'title',
				searchField: 'title',
				options: options,
				create: false,
				onType: function (value) {
					if (population == 'array' || value.length < 2) {
						return;
					}

					ajaxAutocompleteCallback(this, value, population, source, hash);
				}
			});

			$this.next()
				.addClass('fw-selectize')
				.find('> .selectize-input > input').css('width', '11px'); // more than padding left+right to make the cursor visible

			$this.on('remove', function () {
				$this.get(0).selectize.destroy();
			});
		});

		if ($this.val().length) { // there are values that needs to be show right away
			$this.trigger('fw:option-type:multi-select:init');
		} else {
			$this.one('focus', function(){
				$this.trigger('fw:option-type:multi-select:init');
				$this.get(0).selectize.focus();
			});
		}
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-multi-select:not(.initialized)')
			.addClass('initialized')
			.each(init);
	});
})(jQuery);
