(function($){

	var insertParam2 = function(key,value, uri)
	{
		key = encodeURI(key); value = encodeURI(value);
		var kvp = key+"="+value;
		var r = new RegExp("(&|\\?)"+key+"=[^\&]*");
		uri = uri.replace(r,"$1"+kvp);
		if(!RegExp.$1) {uri += (uri.length>0 ? '&' : '?') + kvp;};
		return uri
	}

	var initButton = function() {
		var $button   = $(this),
			uri = $button.data('uri'),
			gmtOffset = new Date().getTimezoneOffset();
			options = "toolbar=yes,menubar=yes,location=yes,status=yes,scrollbars=yes,resizable=yes,width=800,height=600,left=0,top=0";

		uri = insertParam2('offset', (gmtOffset * 60), uri);
		window.open( uri, "calendar", options);
	}

	$(document).ready(function(){
			$('.details-event-button button').on('click', initButton );
		}
	);

})(jQuery)