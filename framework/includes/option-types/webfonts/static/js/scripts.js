/*global fw_google_fonts */
( function ($) {
	$(document).ready(function () {
		var fontsHTML = [];
		var gfonts = fw_google_fonts['google'];
				
		_.each(gfonts, function (item) {
			fontsHTML += '<option value="' + item['family'] + '">' + item['family'] + '</option>';
		});
				
		fwEvents.on('fw:options:init', function (data) {
			setTimeout(function () {
				data.$elements.find('.fw-option-webfonts-option-family select[data-type="family"]:not(.initialized)').each(function () {
					$(this).html(fontsHTML).val($(this).attr('data-value')).selectize({
						render: {
							option: function (item) {
								return '<div data-value="' + item.value + '" data-selectable="" class="option">' + item.text + '</div>';							
							}
						},
						onChange: function (selected) {														
							var var_html = [];								
							var sbs_html = [];								
							if ( gfonts.hasOwnProperty(selected) ) {
								var font = gfonts[selected];
																
								_.each(font.subsets, function (subset) {
									sbs_html += '<option value="' + subset + '">' + fw.capitalizeFirstLetter(subset) + '</option>';
								});																
								_.each(font.variants, function (variant) {
									var_html += '<option value="' + variant + '">' + fw.capitalizeFirstLetter(variant) + '</option>';
								});
							} else {
								sbs_html += '<option value="cyrillic">Cyrillic</option><option value="cyrillic-extended">Cyrillic Extended</option><option value="devanagari">Devanagari</option><option value="greek">Greek</option><option value="greek-extended">Greek Extended</option><option value="khmer">Khmer</option><option value="latin" selected="selected">Latin</option><option value="latin-extended">Latin Extended</option><option value="telugu">Telugu</option><option value="vietnamese">Vietnamese</option>';
								var_html += '<option value="300">Thin</option><option value="300italic">Thin/Italic</option><option value="400" selected="selected">Normal</option><option value="italic">Italic</option><option value="700">Bold</option><option value="700italic">Bold/Italic</option>';
							}
							this.$dropdown.closest('.fw-option-webfonts-option-family').next('.fw-option-webfonts-option-style').find('select[data-type="style"]').html(var_html);
							this.$dropdown.closest('.fw-option-webfonts-option-family').next('.fw-option-webfonts-option-style').next('.fw-option-webfonts-option-subsets').find('select[data-type="subsets"]').html(sbs_html);
						}
					}).addClass('initialized');
				});
			}, 1500);
		});
	});
}(jQuery));