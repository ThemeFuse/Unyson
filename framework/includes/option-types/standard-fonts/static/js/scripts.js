/*global dnp_typography_fonts */
( function ($) {
	$(document).ready(function () {
	
		fwEvents.on('fw:options:init', function (data) {
			setTimeout(function () {
				data.$elements.find('.fw-option-standard-fonts-option-family select[data-type="family"]:not(.initialized)').each(function () {
					// $(this).html(fontsHTML).val($(this).attr('data-value')).selectize().addClass('initialized');
					$(this).selectize().addClass('initialized');
				});
			}, 150);
		});
	});
}(jQuery));