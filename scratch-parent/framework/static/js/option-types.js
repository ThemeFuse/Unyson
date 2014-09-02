jQuery(document).ready(function ($) {
	setTimeout(function(){
		fwEvents.trigger('fw:options:init', {
			$elements: $(document.body)
		});
	}, 50);
});