/*global googleFonts */
( function ($) {
	$(document).ready(function () {
		var fontsHTML = '';
		_.each(googleFonts['standard'], function (item) {
			fontsHTML += '<option value="' + item + '">' + item + '</option>';
		});
		_.each(googleFonts['google'], function (item) {
			fontsHTML += '<option value="' + item['family'] + '">' + item['family'] + '</option>';
		});

		fwEvents.on('fw:options:init', function (data) {
			setTimeout(function () {
				data.$elements.find('.fw-option-typography-option-family select[data-type="family"]:not(.initialized)').each(function () {
					$(this).html(fontsHTML).val($(this).attr('data-value')).selectize({
						render: {
							option: function (item) {
								if (googleFonts['google'].hasOwnProperty(item.value)) {
									var background = (typeof googleFonts['google'][item.value].position === "number") ? 'style="background-position: 0 -' + googleFonts['google'][item.value].position + 'px;' : 'style="background: none;';
									return '<div data-value="' + item.value + '" data-selectable="" class="option">' + item.text + '<div class="preview" ' + background + '"></div></div>';
								} else {
									return '<div data-value="' + item.value + '" data-selectable="" class="option">' + item.text + '<div class="preview" style="background: none; font-family: ' + item.value + '">' + item.value + '</div></div>';
								}
							}
						},
						onChange: function (selected) {
							var html = '';
							if (googleFonts['google'].hasOwnProperty(selected)) {
								var font = googleFonts['google'][selected];
								_.each(font.variants, function (variant) {
									html += '<option value="' + variant + '">' + fw.capitalizeFirstLetter(variant) + '</option>';
								});
							} else {
								html += '<option value="300">Thin</option><option value="300italic">Thin/Italic</option><option value="400" selected="selected">Normal</option><option value="italic">Italic</option><option value="700">Bold</option><option value="700italic">Bold/Italic</option>';
							}
							this.$dropdown.closest('.fw-option-typography-option-family').next('.fw-option-typography-option-style').find('select[data-type="style"]').html(html);
						}
					}).addClass('initialized');
				});
			}, 1500);
		});
	});
}(jQuery));