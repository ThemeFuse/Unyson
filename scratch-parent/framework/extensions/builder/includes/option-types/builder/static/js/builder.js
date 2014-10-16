jQuery(document).ready(function($){
	/** Some functions */
	{
		/**
		 * Loop recursive through all items in given collection
		 */
		function forEachItemRecursive(collection, callback) {
			collection.each(function(item){
				callback(item);

				forEachItemRecursive(item.get('_items'), callback);
			});
		}
	}

	var Builder = Backbone.Model.extend({
		defaults: {
			type: null // required
		},
		/**
		 * Extract item type from class
		 * @param {this.classes.Item} ItemClass
		 * @returns {String}
		 */
		getItemClassType: function(ItemClass) {
			return (typeof ItemClass.prototype.defaults === 'function')
				? ItemClass.prototype.defaults().type
				: ItemClass.prototype.defaults.type;
		},
		/**
		 * @param {String} type
		 * @returns {this.classes.Item}
		 */
		getRegisteredItemClassByType: function(type) {
			return this.registeredItemsClasses[type];
		},
		/**
		 * Register Item Class (with unique type)
		 * @param {this.classes.Item} ItemClass
		 * @returns {boolean}
		 */
		registerItemClass: function(ItemClass) {
			if (!(ItemClass.prototype instanceof this.classes.Item)) {
				console.error('Tried to register Item Type Class that does not extend this.classes.Item', ItemClass);
				return false;
			}

			var type = this.getItemClassType(ItemClass);

			if (typeof type != 'string') {
				console.error('Invalid Builder Item type: '+ type, ItemClass);
				return false;
			}

			if (typeof this.registeredItemsClasses[type] != 'undefined') {
				console.error('Builder Item type "'+ type +'" already registered', ItemClass);
				return false;
			}

			this.registeredItemsClasses[type] = ItemClass;

			return true;
		},
		/**
		 * ! Do not rewrite this (it's final)
		 * @private
		 *
		 * Properties created in initialize():
		 *
		 * Classes to extend
		 * - classes.Item
		 * - classes.ItemView
		 * - classes.Items
		 * - classes.ItemsView
		 *
		 * From this classes will be created items instances
		 * { 'type' => Class }
		 * - registeredItemsClasses
		 *
		 * Reference to root this.classes.Items Collection instance that contains all items
		 * - rootItems
		 *
		 * Hidden input that stores JSON.stringify(this.rootItems)
		 * - $input
		 */
		initialize: function(attributes, options) {
			var builder = this;

			/**
			 * todo: To be able to extend and customize for e.g. only Item class. To not rewrite entire .initialize()
			 * this.__definePrivateMethods()
			 * this.__defineItem()
			 * this.__defineItemView()
			 * this.__defineItems()
			 * this.__defineItemsView()
			 */

			/**
			 * Assign a value to define this property inside this, not in prototype
			 * Instances of Builder should not share items
			 */
			this.registeredItemsClasses = {};

			/** Define private functions accessible only within this method */
			{
				/**
				 * Find Item instance recursive in Items collection
				 * @param {this.classes.Items} collection
				 * @param {Object} itemAttr
				 * @return {this.classes.Item|null}
				 */
				function findItemRecursive(items, itemAttr) {
					var item = items.get(itemAttr);

					if (item) {
						return item;
					}

					items.each(function(_item){
						if (item) {
							// stop search if item found
							return false;
						}

						/** @var {builder.classes.Item} _item */
						item = findItemRecursive(
							_item.get('_items'),
							itemAttr
						);
					});

					return item;
				}

				/**
				 * (Re)Create Items from json
				 *
				 * Used on collection.reset([...]) to create nested items
				 *
				 * @param {this.classes.Item} item
				 * @param {Array} _items
				 * @returns {boolean}
				 * @private
				 */
				function createItemsFromJSON(item, _items) {
					if (!_items) {
						return false;
					}

					_.each(_items, function(_item) {
						var ItemClass = builder.getRegisteredItemClassByType(_item['type']);

						if (!ItemClass) {
							return;
						}

						var __items = _item['_items'];

						delete _item['_items'];

						var subItem = new ItemClass(_item);

						item.get('_items').add(subItem);

						createItemsFromJSON(subItem, __items);
					});

					return true;
				}

				// Mark new added items with special class, to be able to add css effects to it
				{
					var markItemAsNew;

					(function(){
						var lastNewItem = false;

						var rootItemsInitialized = false;

						var removeClassTimeout;
						var removeClassAfter = 700;

						markItemAsNew = function (item) {
							clearTimeout(removeClassTimeout);

							if (lastNewItem) {
								lastNewItem.view.$el.removeClass('new-item');
							}

							item.view.$el.addClass('new-item');

							lastNewItem = item;

							removeClassTimeout = setTimeout(function(){
								if (lastNewItem) {
									lastNewItem.view.$el.removeClass('new-item');
								}
							}, removeClassAfter);

							if (!rootItemsInitialized) {
								builder.rootItems.on('builder:change', function(){
									if (lastNewItem) {
										lastNewItem.view.$el.removeClass('new-item');
									}

									lastNewItem = false;
								});

								rootItemsInitialized = true;
							}
						}
					})();
				}
			}

			/** Define classes */
			{
				this.classes = {};

				/** Items */
				{
					this.classes.Items = Backbone.Collection.extend({
						/**
						 * Guess which item type to create from json
						 * (usually called on .reset())
						 */
						model: function(attrs, options) {
							do {
								if (typeof attrs == 'function') {
									// It's a class. Check if has correct type
									if (builder.getItemClassType(attrs)) {
										return attrs;
									} else {
										break;
									}
								} else if (typeof attrs == 'object') {
									/**
									 * it's an object with attributes for new instance
									 * check if has correct type in it (get registered class with this type)
									 */

									var ItemClass = builder.getRegisteredItemClassByType(attrs['type']);

									if (!ItemClass) {
										break;
									}

									var _items = attrs['_items'];

									delete attrs['_items'];

									var item = new ItemClass(attrs);

									createItemsFromJSON(item, _items);

									return item;
								}
							} while(false);

							console.error('Cannot detect Item type', attrs, options);

							return new builder.classes.Item;
						},
						/**
						 * View that contains sortable with items views
						 */
						view: null,
						initialize: function() {
							this.defaultInitialize();

							this.view = new builder.classes.ItemsView({
								collection: this
							});
						},
						/**
						 * It is required to call this method in .initialize()
						 */
						defaultInitialize: function() {
							this.on('add', function(item) {
								// trigger custom event on rootItems to update input value
								builder.rootItems.trigger('builder:change');

								markItemAsNew(item);
							});

							this.on('remove', function(item) {
								// trigger custom event on rootItems to update input value
								builder.rootItems.trigger('builder:change');
							});
						}
					});

					this.classes.ItemsView = Backbone.View.extend({
						// required

						collection: null,

						// end: required

						tagName: 'div',
						className: 'builder-items fw-row fw-border-box-sizing',
						template: _.template(''),
						events: {},
						initSortableTimeout: 0,
						initialize: function() {
							this.defaultInitialize();
						},
						/**
						 * It is required to call this method in .initialize()
						 */
						defaultInitialize: function() {
							this.listenTo(this.collection, 'add change remove reset', this.render);

							this.render();
						},
						render: function() {
							/**
							 * First .detach() elements
							 * to prevent them to be removed (reset) on .html('...') replace
							 */
							{
								this.collection.each(function(item) {
									item.view.$el.detach();
								});
							}

							if (this.$el.hasClass('ui-sortable')) {
								this.$el.sortable('destroy');
							}

							this.$el.html(this.template({
								items: this.collection
							}));

							var that = this;

							this.collection.each(function(item) {
								that.$el.append(item.view.$el);
							});

							/**
							 * init sortable with delay, after element added to DOM
							 * fixes bug: sortable sometimes not initialized if element is not in DOM
							 */
							{
								clearTimeout(this.initSortableTimeout);

								this.initSortableTimeout = setTimeout(function(){
									that.initSortable();
								}, 12);
							}

							return this;
						},
						initSortable: function(){
							if (this.$el.hasClass('ui-sortable')) {
								// already initialized
								return false;
							}

							// remove "allowed" and "denied" classes from all items
							function itemsRemoveAllowedDeniedClasses() {
								builder.rootItems.view.$el.removeClass(
									'fw-builder-item-allow-incoming-type fw-builder-item-deny-incoming-type'
								);

								forEachItemRecursive(builder.rootItems, function(item){
									item.view.$el.removeClass(
										'fw-builder-item-allow-incoming-type fw-builder-item-deny-incoming-type'
									);
								});
							}

							this.$el.sortable({
								items: '> .builder-item',
								connectWith: '#'+ builder.$input.closest('.fw-option-type-builder').attr('id') +' .builder-root-items .builder-items',
								distance: 10,
								opacity: 0.6,
								start: function(event, ui) {
									// check if it is an exiting item (and create variables)
									{
										// extract cid from view id
										var movedItemCid = ui.item.attr('id');

										if (!movedItemCid) {
											// not an existing item, it's a thumbnail from draggable
											return;
										}

										movedItemCid = movedItemCid.split('-').pop();

										if (!movedItemCid) {
											// not an existing item, it's a thumbnail from draggable
											return;
										}

										var movedItem = findItemRecursive(
											builder.rootItems,
											{cid: movedItemCid}
										);

										if (!movedItem) {
											console.warn('Item not found (cid: "'+ movedItemCid +'")');
											return;
										}
									}

									var movedItemType = movedItem.get('type');

									/**
									 * add "allowed" classes to items vies where allowIncomingType(movedItemType) returned true
									 * else add "denied" class
									 */
									{
										{
											if (movedItem.allowDestinationType(null)) {
												builder.rootItems.view.$el.addClass('fw-builder-item-allow-incoming-type');
											} else {
												builder.rootItems.view.$el.addClass('fw-builder-item-deny-incoming-type');
											}
										}

										forEachItemRecursive(builder.rootItems, function(item){
											if (item.cid === movedItemCid) {
												// this is current moved item
												return;
											}

											if (
												item.allowIncomingType(movedItemType)
												&&
												movedItem.allowDestinationType(item.get('type'))
											) {
												item.view.$el.addClass('fw-builder-item-allow-incoming-type');
											} else {
												item.view.$el.addClass('fw-builder-item-deny-incoming-type');
											}
										});
									}
								},
								stop: function() {
									itemsRemoveAllowedDeniedClasses();
								},
								receive: function(event, ui) {
									// sometimes the "stop" event is not triggered and classes remains
									itemsRemoveAllowedDeniedClasses();

									{
										var currentItemType = null; // will remain null if it is root collection
										var currentItem;

										if (this.collection._item) {
											currentItemType = this.collection._item.get('type');
											currentItem     = this.collection._item;
										}
									}

									var incomingItemType = ui.item.attr('data-builder-item-type');

									if (incomingItemType) {
										// received item type from draggable

										var IncomingItemClass = builder.getRegisteredItemClassByType(incomingItemType);

										if (IncomingItemClass) {
											if (
												IncomingItemClass.prototype.allowDestinationType(currentItemType)
												&&
												(
													!currentItemType
													||
													currentItem.allowIncomingType(incomingItemType)
												)
											) {
												this.collection.add(
													new IncomingItemClass({}, {
														$thumb: ui.item
													}),
													{
														at: this.$el.find('> .builder-item-type').index()
													}
												);
											} else {
												// replace all html, so dragged element will be removed
												this.render();
											}
										} else {
											console.error('Invalid item type: '+ incomingItemType);

											this.render();
										}
									} else {
										// received existing item from another sortable

										if (!ui.item.attr('id')) {
											console.warn('Invalid view id', ui.item);
											return;
										}

										// extract cid from view id
										var incomingItemCid = ui.item.attr('id').split('-').pop();

										var incomingItem = findItemRecursive(
											builder.rootItems,
											{cid: incomingItemCid}
										);

										if (!incomingItem) {
											console.warn('Item not found (cid: "'+ incomingItemCid +'")');
											return;
										}

										var incomingItemType = incomingItem.get('type');
										var IncomingItemClass = builder.getRegisteredItemClassByType(incomingItemType);

										if (
											IncomingItemClass.prototype.allowDestinationType(currentItemType)
											&&
											(
												!currentItemType
												||
												currentItem.allowIncomingType(incomingItemType)
											)
										) {
											// move item from one collection to another
											{
												var at = ui.item.index();

												// prevent 'remove', that will remove all events from the element
												incomingItem.view.$el.detach();

												incomingItem.collection.remove(incomingItem);

												this.collection.add(incomingItem, {
													at: at
												});
											}
										} else {
											console.log('[Builder] Item move denied');
											ui.sender.sortable('cancel');
										}
									}
								}.bind(this),
								update: function (event, ui) {
									if (ui.item.attr('data-ignore-update-once')) {
										ui.item.removeAttr('data-ignore-update-once');
										return;
									}

									if (ui.item.attr('data-builder-item-type')) {
										// element just received from draggable, it is not builder item yet, do nothing
										return;
									}

									if (!ui.item.attr('id')) {
										console.warn('Invalid item, no id');
										return;
									}

									if (!$(this).find('> #'+ ui.item.attr('id') +':first').length) {
										// Item not in sortable, probably moved to another sortable, do nothing

										/**
										 * Right after this event, is expected to be next 'update' for on same item.
										 * But between this two 'update' is a 'receive' that takes care about item move from
										 * one collection to another and place ar right index position in destination model,
										 * so it is better to ignore next coming 'update'.
										 * Set a special attribute to ignore 'update' once
										 */
										ui.item.attr('data-ignore-update-once', 'true');

										return;
									}

									// extract cid from view id
									var itemCid = ui.item.attr('id').split('-').pop();

									var item = findItemRecursive(
										builder.rootItems,
										{cid: itemCid}
									);

									if (!item) {
										console.warn('Item not found (cid: "'+ itemCid +'")');
										return;
									}

									var index = ui.item.index();

									// change item position in collection
									{
										var collection = item.collection;

										// prevent 'remove', that will remove all events from the element
										item.view.$el.detach();

										collection.remove(item);

										collection.add(item, {at: index});
									}
								}
							});

							return true;
						}
					});
				}

				/** Item */
				{
					this.classes.Item = Backbone.RelationalModel.extend({
						// required

						defaults: {
							/** @type {String} Your item unique type (withing the builder) */
							type: null
						},

						/** @type {builder.classes.ItemView} */
						view: null,

						// end: required

						/** ! Do not overwrite this property */
						relations: [
							{
								type: Backbone.HasMany,
								key: '_items',
								//relatedModel: builder.classes.Item, // class does not exists at this point, initialized below
								collectionType: builder.classes.Items,
								collectionKey: '_item'
							}
						],
						initialize: function(){
							this.view = new builder.classes.ItemView({
								id: 'fw-builder-item-'+ this.cid,
								model: this
							});

							this.defaultInitialize();
						},
						/**
						 * It is required to call this method in .initialize()
						 */
						defaultInitialize: function() {
							// trigger custom event on rootItems to update input value
							this.on('change', function() {
								builder.rootItems.trigger('builder:change');
							});
						},
						/**
						 * Item decide if allows an incoming item type to be placed inside it's _items
						 *
						 * @param {String} type
						 * @returns {boolean}
						 */
						allowIncomingType: function(type) {
							return false;
						},
						/**
						 * Item decide if allows to be placed into _items of another item type
						 *
						 * ! Do not use "this" in this method, it will be called without an instance via Class.prototype.allowDestinationType()
						 *
						 * @param {String|null} type String - item type; null - root items
						 * @returns {boolean}
						 */
						allowDestinationType: function(type) {
							return true;
						}
					});

					{
						this.classes.Item.prototype.relations[0].relatedModel = this.classes.Item;
					}

					this.classes.ItemView = Backbone.View.extend({
						// required

						/** @type {builder.classes.Item} */
						model: null,
						/** @type {String} 'any-string-'+ this.model.cid */
						id: null,

						// end: required

						tagName: 'div',
						className: 'builder-item fw-border-box-sizing fw-col-xs-12',
						template: _.template([
							'<div style="border: 1px solid #CCC; padding: 5px; color: #999;">',
							'<em class="fw-text-muted">Default View</em>',
							'<a href="#" onclick="return false;" class="dashicons fw-x"></a>',
							'<div class="builder-items"></div>',
							'</div>'
						].join('')),
						events: {
							'click a.dashicons.fw-x': 'defaultRemove'
						},
						initialize: function(){
							this.defaultInitialize();
							this.render();
						},
						/**
						 * It is required to call this method in .initialize()
						 */
						defaultInitialize: function() {
							this.listenTo(this.model, 'change', this.render);
						},
						render: function() {
							this.defaultRender();
						},
						defaultRender: function(templateData) {
							var _items = this.model.get('_items');

							/**
							 * First .detach() elements
							 * to prevent them to be removed (reset) on .html('...') replace
							 */
							_items.view.$el.detach();



							this.$el.html(
								this.template(
									templateData || {}
								)
							);

							/**
							 * Sometimes sub items sortable view is not initialized or (destroyed if was initialized)
							 * Tell it to render and maybe it will fix itself
							 */
							if (!_items.view.$el.hasClass('ui-sortable')) {
								_items.view.render();
							}

							/**
							 * replace <div class="builder-items"> with builder.classes.ItemsView.$el
							 */
							this.$el.find('.builder-items:first').replaceWith(
								_items.view.$el
							);

							return this;
						},
						defaultRemove: function() {
							this.remove();

							this.model.collection.remove(this.model);
						}
					});
				}
			}

			this.rootItems = new this.classes.Items;


			// prepare this.$input
			{
				if (typeof options.$input == 'undefined') {
					console.warn('$input not specified. Items will no be saved');

					this.$input = $('<input type="hidden">');
				} else {
					this.$input = options.$input;
				}

				fwEvents.trigger('fw-builder:'+ this.get('type') +':register-items', this);


				// recover saved items from input
				{
					var savedItems = [];

					try {
						savedItems = JSON.parse(this.$input.val());
					} catch (e) {
						console.error('Failed to recover items from input', e);
					}

					this.rootItems.reset(savedItems);

					delete savedItems;
				}

				// listen to items changes and update input
				(function(){
					function saveBuilderValueToInput() {
						builder.$input.val(JSON.stringify(builder.rootItems));
						builder.$input.trigger('fw-builder:input:change');
					}

					/**
					 * use timeout to not load browser/cpu when there are many changes at once (for e.g. on .reset())
					 */
					var saveTimeout = 0;

					builder.listenTo(builder.rootItems, 'builder:change', function(){
						clearTimeout(saveTimeout);

						saveTimeout = setTimeout(function(){
							saveTimeout = 0;

							saveBuilderValueToInput();
						}, 100);
					});

					/**
					 * Save value to input if there is a pending timeout on form submit
					 */
					builder.$input.closest('form').on('submit', function(){
						if (saveTimeout) {
							clearTimeout(saveTimeout);
							saveTimeout = 0;

							saveBuilderValueToInput();
						}
					});
				})();
			}
		}
	});

	/**
	 * Create qTips for elements with data-hover-tip="Tip Text" attribute
	 */
	var RootItemsTips = (function(rootItems){
		/**
		 * Store all created qTip instances APIs
		 */
		this.tipsAPIs = [];

		this.resetTimeout = 0;

		this.resetTips = function() {
			_.each(this.tipsAPIs, function(api) {
				api.destroy(true);
			});

			this.tipsAPIs = [];

			var that = this;

			rootItems.view.$el.find('[data-hover-tip]').each(function(){
				$(this).qtip({
					position: {
						at: 'top center',
						my: 'bottom center',
						viewport: rootItems.view.$el.parent()
					},
					style: {
						classes: 'qtip-fw qtip-fw-builder',
						tip: {
							width: 12,
							height: 5
						}
					},
					content: {
						text: $(this).attr('data-hover-tip')
					}
				});

				that.tipsAPIs.push(
					$(this).qtip('api')
				);
			});
		};

		// initialize
		{
			this.resetTips();

			var that = this;

			rootItems.on('builder:change', function(){
				clearTimeout(that.resetTimeout);

				that.resetTimeout = setTimeout(function(){
					that.resetTips();
				}, 100);
			});
		}
	});

	fwEvents.on('fw:options:init', function (data) {
		var $options = data.$elements.find('.fw-option-type-builder:not(.initialized)');

		$options.closest('.fw-backend-option').addClass('fw-backend-option-type-builder');

		fwEvents.trigger('fw:option-type:builder:init', {
			$elements: $options
		});

		$options.each(function(){
			var $this = $(this);
			var id    = $this.attr('id');
			var type  = $this.attr('data-builder-option-type');

			/**
			 * Create instance of Builder
			 */
			{
				var data = {
					type:       type,
					$option:    $this,
					$input:     $this.find('> input:first'),
					$types:     $this.find('.builder-items-types:first'),
					$rootItems: $this.find('.builder-root-items:first')
				};

				var eventData = $.extend(data, {
					/**
					 * In event you can extend (customize/change) and replace this (property) class
					 */
					Builder: Builder
				});

				fwEvents.trigger('fw-builder:'+ type +':before-create', eventData);

				var builder = new eventData.Builder(
					{
						type: data.type
					},
					{
						$input: data.$input
					}
				);

				builder.rootItems.view.$el.appendTo(data.$rootItems);

				new RootItemsTips(builder.rootItems);
			}

			/**
			 * Init draggable thumbnails
			 */
			$this.find('.builder-items-types .builder-item-type').draggable({
				connectToSortable: '#'+ id +' .builder-root-items .builder-items',
				helper: 'clone',
				distance: 10,
				start: function(event, ui) {
					var movedType = ui.helper.attr('data-builder-item-type');

					if (!movedType) {
						return;
					}

					var MovedTypeClass = builder.getRegisteredItemClassByType(movedType);

					if (!MovedTypeClass) {
						return;
					}

					/**
					 * add "allowed" classes to items vies where allowIncomingType(movedType) returned true
					 * else add "denied" class
					 */
					{
						{
							if (MovedTypeClass.prototype.allowDestinationType(null)) {
								builder.rootItems.view.$el.addClass('fw-builder-item-allow-incoming-type');
							} else {
								builder.rootItems.view.$el.addClass('fw-builder-item-deny-incoming-type');
							}
						}

						forEachItemRecursive(builder.rootItems, function(item){
							if (
								item.allowIncomingType(movedType)
								&&
								MovedTypeClass.prototype.allowDestinationType(item.get('type'))
							) {
								item.view.$el.addClass('fw-builder-item-allow-incoming-type');
							} else {
								item.view.$el.addClass('fw-builder-item-deny-incoming-type');
							}
						});
					}
				},
				stop: function() {
					// remove "allowed" and "denied" classes from all items
					{
						builder.rootItems.view.$el.removeClass(
							'fw-builder-item-allow-incoming-type fw-builder-item-deny-incoming-type'
						);

						forEachItemRecursive(builder.rootItems, function(item){
							item.view.$el.removeClass(
								'fw-builder-item-allow-incoming-type fw-builder-item-deny-incoming-type'
							);
						});
					}
				}
			});

			/**
			 * Add item on thumbnail click
			 */
			$this.find('.builder-items-types').on('click', '.builder-item-type', function(){
				var $itemType = $(this);

				var itemType = $itemType.attr('data-builder-item-type');

				if (itemType) {
					var ItemTypeClass = builder.getRegisteredItemClassByType(itemType);

					if (ItemTypeClass) {
						if (ItemTypeClass.prototype.allowDestinationType(null)) {
							builder.rootItems.add(
								new ItemTypeClass({}, {
									$thumb: $itemType
								})
							);

							// animation
							{
								// stop previous animation
								{
									clearTimeout($itemType.attr('data-animation-timeout-id'));
									$itemType.removeClass('fw-builder-animation-item-type-add');
								}

								$itemType.addClass('fw-builder-animation-item-type-add');

								$itemType.attr('data-animation-timeout-id',
									setTimeout(function(){
										$itemType.removeClass('fw-builder-animation-item-type-add');
									}, 500)
								);
							}
						}
					} else {
						console.error('Invalid item type: '+ itemType);
					}
				} else {
					console.error('Cannot extract item type from clicked thumbnail');
				}
			});

			/**
			 * Add tips to thumbnails
			 */
			$this.find('.builder-items-types .builder-item-type [data-hover-tip]').each(function(){
				$(this).qtip({
					position: {
						at: 'top center',
						my: 'bottom center',
						viewport: $('body')
					},
					style: {
						classes: 'qtip-fw qtip-fw-builder',
						tip: {
							width: 12,
							height: 5
						}
					},
					content: {
						text: $(this).attr('data-hover-tip')
					}
				});
			});
		});

		$options.addClass('initialized');
	});
});