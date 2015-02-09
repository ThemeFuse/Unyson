(function ($, _, fwEvents, window) {
	var addablePopup = function () {
		var $this = $(this),
			$defaultItem = $this.find('.item.default'),
			nodes = {
				$optionWrapper: $this,
				$addButton: $this.find('.add-new-item'),
				$itemsWrapper: $this.find('.items-wrapper'),
				getDefaultItem: function () {
					return $defaultItem.clone().removeClass('default');
				}
			},
			data = JSON.parse(nodes.$optionWrapper.attr('data-for-js')),
			utils = {
				modal: new fw.OptionsModal({
					title: data.title,
					options: data.options
				}),
				countItems: function () {
					return nodes.$itemsWrapper.find('.item:not(.default)').length;
				},
				removeDefaultItem: function () {
					nodes.$itemsWrapper.find('.item.default').remove();
				},
				toogleItemsWrapper: function () {

					if (utils.countItems() === 0) {
						nodes.$itemsWrapper.hide();
					} else {
						nodes.$itemsWrapper.show();
					}
				},
				init: function () {
					utils.initItemsTemplates();
					utils.toogleItemsWrapper();
					utils.removeDefaultItem();
					utils.initSortable();
				},
				initSortable: function () {
					nodes.$itemsWrapper.sortable({
						items: '.item:not(.default)',
						cursor: 'move',
						distance: 2,
						tolerance: 'pointer',
						axis: 'y'
					});
				},
				initItemsTemplates: function () {
					var $items = nodes.$itemsWrapper.find('.item:not(.default)');
					if ($items.length > 0) {
						$items.each(function () {
							utils.editItem($(this), JSON.parse($(this).find('input').val()));
						});
					}
				},
				createItem: function (values) {
					var $clonedItem = nodes.getDefaultItem(),
						$clonedInput = $clonedItem.find('.input-wrapper');

					var $inputTemplate = $(
						$.trim($clonedInput.html())
							.split( nodes.$addButton.attr('data-increment-placeholder') ).join(utils.countItems())
					);
					$inputTemplate.attr('value', JSON.stringify(values));

					$clonedInput.find('input').replaceWith($inputTemplate);

					var template = '';

					try {
						/**
						 * may throw error in in template is used an option id added after some items was already saved
						 */
						template = _.template(
							$.trim(data.template),
							values,
							{
								evaluate: /\{\{(.+?)\}\}/g,
								interpolate: /\{\{=(.+?)\}\}/g,
								escape: /\{\{-(.+?)\}\}/g
							}
						);
					} catch (e) {
						template = '[Template Error] '+ e.message;
					}

					$clonedItem.find('.content').html(template);

					return $clonedItem;
				},
				addNewItem: function (values) {
					nodes.$itemsWrapper.append(utils.createItem(values));
				},
				editItem: function (item, values) {
					item.replaceWith(utils.createItem(values));
				}
			};

		nodes.$itemsWrapper.on('click', '.delete-item', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).closest('.item').remove();
			utils.toogleItemsWrapper();
		});

		nodes.$itemsWrapper.on('click', '.item', function (e) {
			e.preventDefault();

			var values = {};
			var $input = $(this).find('input');

			if ($input.length) {
				values = JSON.parse($input.val());
			}

			utils.modal.set('edit', true);
			utils.modal.set('values', values, {silent: true});
			utils.modal.set('itemRef', $(this));
			utils.modal.open();
		});

		nodes.$addButton.on('click', function () {
			utils.modal.set('edit', false);
			utils.modal.set('values', {}, {silent: true});
			utils.modal.open();
		});

		utils.modal.on('change:values', function (modal, values) {
			if (!modal.get('edit')) {
				utils.addNewItem(values);
				utils.toogleItemsWrapper();
			} else {
				utils.editItem(utils.modal.get('itemRef'), values);
			}
		});

		utils.init();
	};

	fwEvents.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-addable-popup:not(.fw-option-initialized)').each(addablePopup)
			.addClass('fw-option-initialized');
	});

})(jQuery, _, fwEvents, window);