/*global fw_typography_fonts */
( function ($) {
	$(document).ready(function () {
		var optionTypeClass = '.fw-option-type-typography',
			/**
			 * [ {'value': 'Font Family', 'text': 'Font Family'} ]
			 */
			fontsOptions = null,
			/**
			 * { 'Font Family': '<option ...' }
			 */
			fontsOptionsHTML = null,
			getFontsOptions = function(){
				if (fontsOptions === null) {
					fontsOptions = [];
					fontsOptionsHTML = {};

					_.each(fw_typography_fonts['standard'], function (item) {
						fontsOptionsHTML[item] = '<option value="' + item + '">' + item + '</option>';
						fontsOptions.push({
							value: item,
							text: item
						});
					});

					_.each(fw_typography_fonts['google'], function (item) {
						fontsOptionsHTML[item['family']] = '<option value="' + item['family'] + '">' + item['family'] + '</option>';
						fontsOptions.push({
							value: item['family'],
							text: item['family']
						});
					});
				}

				return fontsOptions;
			},
			getFontsOptionHTML = function(fontFamily) {
				if (fontsOptionsHTML === null) {
					getFontsOptions();
				}

				return fontsOptionsHTML[fontFamily];
			};

		fwEvents.on('fw:options:init', function (data) {
			data.$elements.find(optionTypeClass +':not(.initialized)').each(function(){
				var $option = $(this);

				{
					var $fontFamilySelect = $option.find('.fw-option-typography-option-family select[data-type="family"]');

					$fontFamilySelect
						.html(getFontsOptionHTML($fontFamilySelect.attr('data-value')))
						.selectize({
							render: {
								option: function (item) {
									if (fw_typography_fonts['google'].hasOwnProperty(item.value)) {
										var background = (typeof fw_typography_fonts['google'][item.value].position === "number")
											? 'style="background-position: 0 -' + fw_typography_fonts['google'][item.value].position + 'px;'
											: 'style="background: none;';

										return ''+
										'<div data-value="' + item.value + '" data-selectable="" class="option">' +
											item.text +
											'<div class="preview" ' + background + '"></div>'+
										'</div>';
									} else {
										return ''+
										'<div data-value="' + item.value + '" data-selectable="" class="option">' +
											item.text +
											'<div class="preview" style="background: none; font-family: ' + item.value + '">' + item.value + '</div>'+
										'</div>';
									}
								}
							},
							onChange: function (selected) {
								var html = '';

								if (fw_typography_fonts['google'].hasOwnProperty(selected)) {
									var font = fw_typography_fonts['google'][selected];
									_.each(font.variants, function (variant) {
										html += '<option value="' + variant + '">' + fw.capitalizeFirstLetter(variant) + '</option>';
									});
								} else {
									html += [ // todo: translate these strings
										'<option value="300">Thin</option>',
										'<option value="300italic">Thin/Italic</option>',
										'<option value="400" selected="selected">Normal</option>',
										'<option value="italic">Italic</option>',
										'<option value="700">Bold</option>',
										'<option value="700italic">Bold/Italic</option>'
									].join("\n");
								}

								this.$dropdown
									.closest('.fw-option-typography-option-family')
									.next('.fw-option-typography-option-style')
									.find('select[data-type="style"]').html(html);
							},
							onFocus: function() {
								var selectize = $fontFamilySelect[0].selectize;
								var selectedValue = selectize.getValue();
								selectize.removeOption(selectedValue, true);

								_.each(getFontsOptions(), function(option){
									selectize.addOption({
										value: option.value,
										text: option.text
									});
								});

								selectize.setValue(selectedValue, true);
								selectize.refreshOptions(true);

							},
							onBlur: function() {
								var selectize = $fontFamilySelect[0].selectize,
									value = selectize.getValue();

								_.each(getFontsOptions(), function(option){
									if (value !== option.value) {
										selectize.removeOption(option.value);
									}
								});

								selectize.refreshOptions(false);
							},
							onInitialize: function(){
								$fontFamilySelect.removeAttr('data-value');

								$fontFamilySelect.trigger('selectizeLoaded', [$fontFamilySelect[0].selectize]);
							}
						});
				}
			}).addClass('initialized');
		});
	});
}(jQuery));