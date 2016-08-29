(function ($, _, fwEvents) {
    
	var is_url = function(str) {
	    var pattern = new RegExp(/^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/, 'i');
	    return pattern.test(str);
	};
    
	var oembed = function () {
		var $wrapper = $(this);
		var $input = $wrapper.find('input[type=text]');
		var $iframeWrapper = $wrapper.find('.fw-oembed-preview');

		$input.on('input',
		    _.debounce(function () {
			if( $input.val() && is_url( $input.val() ) ) {
			    wp.ajax.post(
				'get_oembed_response',
				{
				    '_nonce': $wrapper.data('nonce'),
				    'preview': $wrapper.data('preview'),
				    'url': $input.val()
			    }).done(function (data) {
				$iframeWrapper.html(data.response);
			    }).fail(function () {
			    	$iframeWrapper.html('');
			    	console.error('Get Oembed Response: Ajax error.', error);
			    })
			} else {
			    $iframeWrapper.html('');
			}
		    }, 300)
		);
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-oembed:not(.fw-option-initialized)').each(oembed)
			.addClass('fw-option-initialized');
	});
})(jQuery, _, fwEvents);
