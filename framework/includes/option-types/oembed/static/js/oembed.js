(function ($, _, fwEvents) {
	var oembed = function () {
		var $wrapper = $(this);
		var $input = $wrapper.find('input[type=text]');
		var $iframeWrapper = $wrapper.find('.fw-oembed-preview');

		$input.on('input',
			_.debounce(function () {
				wp.ajax.post(
					'get_oembed_response',
					{
						'_nonce': $wrapper.data('nonce'),
						'preview': $wrapper.data('preview'),
						'url': $input.val()
					}).done(function (data) {
						$iframeWrapper.html(data.response);
					}).fail(function () {
					})

			}, 300)
		);
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-oembed:not(.fw-option-initialized)').each(oembed)
			.addClass('fw-option-initialized');
	});
})(jQuery, _, fwEvents);
