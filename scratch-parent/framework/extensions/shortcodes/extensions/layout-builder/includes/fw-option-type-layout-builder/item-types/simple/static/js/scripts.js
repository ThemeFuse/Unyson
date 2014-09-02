(function(fwe, _, builderData) {
	fwe.one('fw-builder:' + 'layout-builder' + ':register-items', function(builder) {
		var LayoutBuilderSimpleItem,
			LayoutBuilderSimpleItemView;

		LayoutBuilderSimpleItemView = builder.classes.ItemView.extend({
			initialize: function(options) {
				this.defaultInitialize();

				this.templateData = options.templateData || {};
				if (options.modalOptions) {
					this.modal = new fw.OptionsModal({
						title: options.templateData.title,
						options: options.modalOptions,
						values: this.model.get('optionValues'),
						size: options.modalSize
					});

					this.listenTo(this.modal, 'change:values', function(modal, values) {
						this.model.set('optionValues', values);
					});
				}
			},
			template: _.template(
				'<div class="lb-item-type-simple <% if (hasOptions) { %>has-options <% } %>lb-item fw-row">' +
					'<% if (image) { %>' +
					'<img src="<%- image %>" />' +
					'<%  } %>' +

					'<%- title %>' + // TODO: see if needs to bee escaped or not
					'<div class="controls">' +

						'<% if (hasOptions) { %>' +
						'<i class="dashicons dashicons-welcome-write-blog edit-options"></i>' +
						'<%  } %>' +

						'<i class="dashicons dashicons-admin-page item-clone"></i>' +
						'<i class="dashicons dashicons-no item-delete"></i>' +
					'</div>' +
				'</div>'
			),
			render: function() {
				this.defaultRender(this.templateData);
			},
			events: {
				'click': 'editOptions',
				'click .edit-options': 'editOptions',
				'click .item-clone': 'cloneItem',
				'click .item-delete': 'removeItem'
			},
			editOptions: function(e) {
				e.stopPropagation();
				if (!this.modal) {
					return;
				}
				this.modal.open();
			},
			cloneItem: function(e) {
				e.stopPropagation();
				var index = this.model.collection.indexOf(this.model),
					attributes = this.model.toJSON();
				this.model.collection.add(new LayoutBuilderSimpleItem(attributes), {at: index + 1})
			},
			removeItem: function(e) {
				e.stopPropagation();
				this.remove();
				this.model.collection.remove(this.model);
			}
		});

		LayoutBuilderSimpleItem = builder.classes.Item.extend({
			defaults: {
				type: 'simple'
			},
			initialize: function(atts, opts) {
				var subtype = this.get('subtype') || opts.$thumb.find('.item-data').attr('data-subtype'),
					subtypeData,
					modalOptions;

				this.defaultInitialize();

				if (!builderData[subtype]) {
					this.view = new builder.classes.ItemView({
						id: 'fw-builder-item-'+ this.cid,
						model: this
					});

					alert('The shortcode: "' + subtype +  '" not found, it was probably deleted!');
					console.error('The requested shortcode: "%s" not found, , it was probably deleted!', subtype);
				} else {
					subtypeData = builderData[subtype];
					modalOptions = subtypeData.options;

					if (!this.get('subtype')) {
						this.set('subtype', subtype);
					}

					var templateData = {
						title: subtypeData.title,
						image: subtypeData.image,
						hasOptions: !!modalOptions
					};

					this.view = new LayoutBuilderSimpleItemView({
						id: 'layout-builder-item-'+ this.cid,
						model: this,
						modalOptions: modalOptions,
						modalSize: subtypeData.popup_size,
						templateData: templateData
					});
				}
			},
			allowIncomingType: function() {
				return false;
			}
		});

		builder.registerItemClass(LayoutBuilderSimpleItem);
	});
})(fwEvents, _, layout_builder_item_type_simple_data);
