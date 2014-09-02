var FwBuilderComponents = {
	Item: {},
	ItemView: {},
	Items: {},
	ItemsView: {}
};

/**
 * Change item width
 *
 * Usage:
 *
 * // in ItemView.initialize()
 *
 * this.widthChangerView = new FwBuilderComponents.ItemView.WidthChanger({
 *  model: this.model,
 *  view: this,
 *  widths: [
 *      {
 *          text: '1/12',
 *          value: 1, // or 8.33333333 as %
 *          itemViewClass: 'fw-col-sm-1'
 *      },
 *      //...
 *      {
 *          text: '1/1',
 *          value: 12, // or 100 as %
 *          itemViewClass: 'fw-col-sm-12'
 *      }
 *  ],
 *  modelAttribute: 'width',
 * });
 *
 * // in ItemView.render()
 *
 * this.$('.some-class').append( this.widthChangerView.$el );
 *
 * this.widthChangerView.delegateEvents(); // rebind events after element "remove" happened
 */
FwBuilderComponents.ItemView.WidthChanger = Backbone.View.extend({
	tagName: 'div',
	className: 'fw-builder-item-width-changer',
	template: _.template(
		'<a href="#" class="decrease-width dashicons dashicons-arrow-left-alt2" onclick="return false;"></a>'+
			' <span class="current-width fw-wp-link-color"><%- width %></span> '+
		'<a href="#" class="increase-width dashicons dashicons-arrow-right-alt2" onclick="return false;"></a>'
	),
	events: {
		'click .decrease-width': 'decreaseWidth',
		'click .increase-width': 'increaseWidth'
	},
	widths: [
		{
			'text': '1/12',
			'value': 1,
			'itemViewClass': 'fw-col-sm-1'
		},
		{
			'text': '2/12',
			'value': 2,
			'itemViewClass': 'fw-col-sm-2'
		},
		{
			'text': '3/12',
			'value': 3,
			'itemViewClass': 'fw-col-sm-3'
		},
		{
			'text': '4/12',
			'value': 4,
			'itemViewClass': 'fw-col-sm-4'
		},
		{
			'text': '5/12',
			'value': 5,
			'itemViewClass': 'fw-col-sm-5'
		},
		{
			'text': '6/12',
			'value': 6,
			'itemViewClass': 'fw-col-sm-6'
		},
		{
			'text': '7/12',
			'value': 7,
			'itemViewClass': 'fw-col-sm-7'
		},
		{
			'text': '8/12',
			'value': 8,
			'itemViewClass': 'fw-col-sm-8'
		},
		{
			'text': '9/12',
			'value': 9,
			'itemViewClass': 'fw-col-sm-9'
		},
		{
			'text': '10/12',
			'value': 10,
			'itemViewClass': 'fw-col-sm-10'
		},
		{
			'text': '11/12',
			'value': 11,
			'itemViewClass': 'fw-col-sm-11'
		},
		{
			'text': '12/12',
			'value': 12,
			'itemViewClass': 'fw-col-sm-12'
		}
	],
	/**
	 * The attribute name that will be changed in item on width changes
	 * this.model.set(this.modelAttribute, this.widths[N].value)
	 */
	modelAttribute: 'width',
	initialize: function(options) {
		_.extend(this, _.pick(options,
			'view',
			'widths',
			'modelAttribute'
		));

		// set special properties for first and last width
		{
			this.widths[0].first = true;
			this.widths[ this.widths.length - 1 ].last = true;
		}

		this.listenTo(this.model, 'change:' + this.modelAttribute, this.render);

		this.render();
	},
	render: function() {
		this.updateWidth();

		var widthValue = this.model.get(this.modelAttribute);
		var width      = _.findWhere(this.widths, {value: widthValue});
		var widthText  = '?';

		if (width) {
			widthText = width.text;
		}

		{
			this.$el.removeClass('is-first is-last');

			if (!!width.first) {
				this.$el.addClass('is-first');
			}

			if (!!width.last) {
				this.$el.addClass('is-last');
			}
		}

		this.$el.html(
			this.template({
				width: widthText
			})
		);
	},
	decreaseWidth: function() {
		var widthValue = this.model.get(this.modelAttribute);
		var widthsValues = _.pluck(this.widths, 'value');
		var currentWidthIndex = _.indexOf(widthsValues, widthValue);

		if (currentWidthIndex == -1) {
			// Current value does not exists (invalid) set first width
			widthValue = widthsValues[0];
		} else if (currentWidthIndex == 0) {
			// Do nothing, this is the smallest width
		} else {
			// Set smaller width
			widthValue = widthsValues[currentWidthIndex - 1];
		}

		this.updateWidth(widthValue);
	},
	increaseWidth: function() {
		var widthValue = this.model.get(this.modelAttribute);
		var widthsValues = _.pluck(this.widths, 'value');
		var currentWidthIndex = _.indexOf(widthsValues, widthValue);

		if (currentWidthIndex == -1) {
			// Current value does not exists (invalid) set last width
			widthValue = widthsValues[ widthsValues.length - 1 ];
		} else if (currentWidthIndex == widthsValues.length - 1) {
			// Do nothing, this is the biggest width
		} else {
			// Set bigger width
			widthValue = widthsValues[currentWidthIndex + 1];
		}

		this.updateWidth(widthValue);
	},
	updateWidth: function(widthValue) {
		if (typeof widthValue == 'undefined') {
			widthValue = this.model.get(this.modelAttribute);
		}

		var widthsValues = _.pluck(this.widths, 'value');

		// check if correct
		if (-1 == _.indexOf(widthsValues, widthValue)) {
			// set default
			widthValue = widthsValues[0];
		}

		if (widthValue != this.model.get(this.modelAttribute)) {
			// set only when is different, to prevent trigger actions on those who listens to model 'change'
			this.model.set(this.modelAttribute, widthValue);
		}

		var itemViewClass = _.findWhere(this.widths, {value: widthValue}).itemViewClass;

		this.view.$el
			.removeClass(
				_.pluck(this.widths, 'itemViewClass').join(' ')
			)
			.addClass(itemViewClass);
	}
});

/**
 * Usage:
 *
 * // in ItemView.initialize()
 *
 * this.inlineEditor = new FwBuilderComponents.ItemView.InlineTextEditor({
 *  model: item,
 *  editAttribute: 'model_attr_name' // also is available nested attribute property notation: 'a/b/c' will do {a: {b: {c: 'value'}}}
 * })
 *
 * // in ItemView.render()
 *
 * this.$('.some-class').append( this.inlineEditor.$el );
 *
 * this.inlineEditor.delegateEvents(); // rebind events after element "remove" happened
 */
FwBuilderComponents.ItemView.InlineTextEditor = Backbone.View.extend({
	tagName: 'div',
	className: 'fw-builder-item-width-changer',
	template: _.template(
		'<input type="text" style="width: auto;" value="<%- value %>">&nbsp;<button class="button" onclick="return false;"><%- save %></button>'
	),
	events: {
		'change input': 'update',
		'focusout input': 'hide'
	},
	render: function() {
		var localized = fw_option_type_builder_helpers;

		this.$el.html(
			this.template({
				value: this.editAttributeWitoutRoot
					? fw.opg(this.editAttributeWitoutRoot, this.model.get(this.editAttributeRoot))
					: this.model.get(this.editAttributeRoot),
				save: localized.l10n.save
			})
		);

		this.$el.addClass('fw-hidden');
	},
	initialize: function(options) {
		_.extend(this, _.pick(options,
			'editAttribute'
		));

		this.delimiter = '/';

		/**
		 * From 'a/b/c', extract: 'a' and 'b/c'
		 */
		{
			var editAttributeSplit = this.editAttribute.split(this.delimiter);

			this.editAttributeRoot       = editAttributeSplit.shift();
			this.editAttributeWitoutRoot = editAttributeSplit.join(this.delimiter);
		}

		this.listenTo(this.model, 'change:'+ this.editAttributeRoot, this.render);

		this.render();
	},
	update: function() {
		var value = this.$el.find('input').val();

		if (!jQuery.trim(value).length) {
			value = ' ';
		}

		var value = this.editAttributeWitoutRoot
			? fw.ops(this.editAttributeWitoutRoot, value,
				// clone to not change by reference, else values will be equal and model.set() will not trigger 'change'
				_.clone(this.model.get(this.editAttributeRoot)))
			: value;

		this.model.set(this.editAttributeRoot, value);
	},
	hide: function() {
		this.$el.addClass('fw-hidden');

		this.trigger('hide');
	},
	show: function() {
		this.$el.removeClass('fw-hidden');
		this.$el.find('input').focus();
	}
});