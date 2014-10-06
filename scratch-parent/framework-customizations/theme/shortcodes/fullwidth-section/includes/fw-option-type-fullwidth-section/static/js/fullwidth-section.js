(function (fwe, _, itemData) {
	fwe.one('fw-builder:' + 'layout-builder' + ':register-items', function (builder) {
		var LayoutBuilderFullwidthItem,
			LayoutBuilderFullwidthItemView;

		LayoutBuilderFullwidthItemView = builder.classes.ItemView.extend({
			initialize: function (options) {
				this.defaultInitialize();

				if (options.modalOptions) {
					this.modal = new fw.OptionsModal({
						title: 'Custom Section',
						options: options.modalOptions,
						values: this.model.get('optionValues'),
						size: 'medium'
					});

					this.listenTo(this.modal, 'change:values', function (modal, values) {
						this.model.set('optionValues', values);
					});
				}
			},
			template: _.template(
				'<div class="lb-item-type-column lb-item custom-section">' +
					'<div class="panel fw-row">' +
					'<div class="panel-left fw-col-xs-6">' +
					'<div class="column-title">Custom Section</div>' +
					'</div>' +
					'<div class="panel-right fw-col-xs-6">' +
					'<div class="controls">' +
					'<i class="dashicons dashicons-welcome-write-blog edit-options"></i>' +
					'<i class="dashicons dashicons-admin-page custom-section-clone"></i>' +
					'<i class="dashicons dashicons-no custom-section-delete"></i>' +
					'</div>' +
					'</div>' +
					'</div>' +
					'<div class="builder-items"></div>' +
					'</div>'
			),
			render: function () {
				this.defaultRender();
			},
			events: {
				'click .edit-options': 'editOptions',
				'click .custom-section-clone': 'cloneItem',
				'click .custom-section-delete': 'removeItem'
			},
			cloneItem: function () {
				var index = this.model.collection.indexOf(this.model),
					attributes = this.model.toJSON(),
					_items = attributes['_items'],
					clonedColumn;

				delete attributes['_items'];

				clonedColumn = new LayoutBuilderFullwidthItem(attributes);
				this.model.collection.add(clonedColumn, {at: index + 1});
				clonedColumn.get('_items').reset(_items);
			},
			removeItem: function () {
				this.remove();
				this.model.collection.remove(this.model);
			},
			editOptions: function (e) {
				e.stopPropagation();
				if (!this.modal) {
					return;
				}
				this.modal.open();
			}
		});

		LayoutBuilderFullwidthItem = builder.classes.Item.extend({
			defaults: {
				type: 'fullwidth-section'
			},
			initialize: function (atts, opts) {
				this.view = new LayoutBuilderFullwidthItemView({
					id: 'layout-builder-item-' + this.cid,
					model: this,
					modalOptions: itemData.options || {}
				});

				this.defaultInitialize();
			},
			allowIncomingType: function (type) {
				return 'fullwidth-section' === type ? false : true;
			},
			allowDestinationType: function (type) {
				return 'column' === type ? false : true;
			}
		});

		builder.registerItemClass(LayoutBuilderFullwidthItem);
	});
})(fwEvents, _, layout_builder_item_type_fullwidth_section_data);

