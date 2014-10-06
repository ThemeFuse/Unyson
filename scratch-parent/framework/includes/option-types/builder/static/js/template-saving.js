(function ($, fwe, _) {

	fwe.one('fw:option-type:builder:init', function (data) {

		if (!data.$elements.length) {
			return;
		}

		var $builders = data.$elements;

		$builders.each(function () {

			var elements = {
					$builder: $(this),
					$input: $(this).find('> input:first'),
					$defaultLi: $('<li class="default-li">0 Templates Saved</li>')
				},
				builderType = $(this).attr('data-builder-option-type');

			elements.$navigation = elements.$builder.find('.builder-root-items .navigation');


			if (elements.$navigation.length === 0) {
				elements.$builder.find('.builder-root-items').append('<div class="navigation"></div>');
				elements.$navigation = elements.$builder.find('.builder-root-items .navigation');
			}

			elements.$navigation.append('<div class="template-container"><a class="template-btn">Templates</a></div>');

			var utils = {
				modal: new fw.OptionsModal({
					title: 'Save Template',
					options: [
						{'template_name': {
							'type': 'text',
							'label': 'Template Name',
							'desc': 'Must have at least 3 characters ( Whitespace, A-Z, 0-9, -_)'
						}}
					],
					values: ''
				}),
				generateList: function (json) {
					var ul = $('<ul/>');
					if (json.length === 0) {
						return ul.append(elements.$defaultLi);
					}
					var documentFragment = $(document.createDocumentFragment());

					$.each(json, function (key, value) {
						var li = $('<li>' + value.title + '<a class="template-delete dashicons fw-x" href="#"></a></li>').data('template-state', {'id': key, 'json': value.json});
						documentFragment.append(li);
					});
					return ul.append(documentFragment);
				}
			};

			utils.modal.on('change:values', function (modal, values) {

				$.ajax(
					{
						type: "post",
						dataType: "json",
						url: ajaxurl,
						data: {
							'action': 'save_builder_template',
							'template_name': values.template_name,
							'builder_json': elements.$input.val(),
							'builder_type': builderType
						}
					}).done(function (json) {
						var li = $('<li>' + json.data.title + '<a class="template-delete dashicons fw-x" href="#"></a></li>').data('template-state', {'id': json.data.id, 'json': json.data.json});
						elements.qtipApi.elements.tooltip.find('.default-li').remove();
						elements.qtipApi.elements.tooltip.find('ul').append(li);
						utils.modal.set('values', {}, {silent: true});
					})
					.fail(function (xhr, status, error) {
					});
			});


			var initTooltip = function (content) {
				elements.$builder.find('.template-btn').qtip({
					show: 'click',
					hide: 'unfocus',
					position: {
						at: 'bottom center',
						my: 'top center',
						viewport: $('body')
					},
					events: {
						render: function (e, api) {
							elements.qtipApi = api;
							api.elements.tooltip.find('.save-template').on('click', function (e) {
								e.preventDefault();
								utils.modal.open();
								api.hide();
							});

							api.elements.tooltip.on('click', 'li:not(.default-li)', function () {
								elements.builder.rootItems.reset(JSON.parse($(this).data('template-state').json));
							});

							api.elements.tooltip.on('click', '.template-delete', function (e) {
								e.preventDefault();
								e.stopPropagation();

								var self = $(this);

								console.log($(this).closest('li').data('template-state').id);

								$.ajax(
									{
										type: "post",
										dataType: "json",
										url: ajaxurl,
										data: {
											'action': 'delete_builder_template',
											'builder_type': builderType,
											'uniqid': $(this).closest('li').data('template-state').id
										}
									}).done(function () {
										if (self.closest('ul').children().length === 1) {
											self.closest('ul').append(elements.$defaultLi);
										}
										self.closest('li').remove();
										api.reposition();
									});
							});
						}
					},
					style: {
						classes: 'qtip-fw qtip-fw-builder',
						tip: {
							width: 12,
							height: 5
						},
						width: 180
					},
					content: {
						text: content
					}
				});
			};

			$.ajax(
				{
					type: "post",
					dataType: "json",
					url: ajaxurl,
					data: {
						'action': 'load_builder_templates',
						'builder_type': builderType
					}
				})
				.done(function (json) {

					var list = utils.generateList(json.data),
						$wrapper = $('<div class="fw-templates-wrapper">' +
							'<div class="navigation"><a href="#" class="save-template">Save Template</a></div>' +
							'<div class="templates-list">' +
							'<div class="head-text"><i>Load Template:</i></div>' +
							'</div>' +
							'</div>');

					$wrapper.find('.templates-list').append(list);

					initTooltip($wrapper);
				});

			fwe.one('fw-builder:' + builderType + ':register-items', function (builder) {
				elements.builder = builder;
			});
		});
	});
})(jQuery, fwEvents, _);