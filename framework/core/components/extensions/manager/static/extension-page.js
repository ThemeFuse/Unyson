jQuery(function($){
	$('#fw-extension-docs a:fw-external').attr('target', '_blank');

	fwEvents.on('fw:options:init', function(data){
		var $fadeWrapper = data.$elements.find('#fw-extension-tab-content');

		setTimeout(function(){
			$fadeWrapper.fadeTo('fast', 1);
		}, 50);
	});
});