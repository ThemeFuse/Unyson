jQuery(document).ready(function ($) {
	_.delay(function(){
		_.defer(function(){
			fwEvents.trigger('fw:options:init', {$elements: $(document.body)});
		});
	}, 30);
});