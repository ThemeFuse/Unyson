(function ($) {
window.fwOptionTypeIconV2Picker = fw.Modal.extend({
	defaults: _.extend({}, fw.Modal.prototype.defaults, {
		title: 'Icon V2',
		size: 'small',
		modalCustomClass: 'fw-icon-v2-picker-modal',
		emptyHtmlOnClose: false
	}),

	ContentView: fw.Modal.prototype.ContentView.extend({
		events: {
			'input .fw-icon-v2-icons-library .fw-icon-v2-toolbar input': 'onSearch',
			'click .fw-icon-v2-library-icon': 'markIconAsSelected',
			'click .fw-icon-v2-library-icon a': 'markIconAsFavorite',
			'submit': 'onSubmit'
		},

		initialize: function () {
			fw.Modal.prototype.ContentView.prototype.initialize.call(this);

			// keep track of current searches for better performance
			this.previousSearch = '';

			this.throttledApplyFilters = _.throttle(
				_.bind(this.model.applyFilters, this.model),
				200
			);
		},

		computeModalHeight: function() {
			var $icons = this.model.frame.$el.find('.fw-icon-v2-library-packs-wrapper');
			var toolbarHeight = this.model.frame.$el.find('.fw-icon-v2-toolbar').height();
			var $tabsList = this.model.frame.$el.find('.fw-options-tabs-list');

			$icons.height(
				this.model.frame.$el.find(
					'> .media-frame-content'
				).height() - $tabsList.height() - toolbarHeight - 75
			);
		},

		onSubmit: function(e) {
			this.model.resolveResult();
			var content = this;

			e.preventDefault();

			setTimeout(function () {
				content.model.frame.modal.$el.find('.media-modal-close').trigger('click');
			}, 0);
		},

		markIconAsSelected: function markIconAsSelected (e) {
			e.preventDefault();
			var $el = $(e.currentTarget);

			this.model.result['icon-class'] = $el.attr('data-fw-icon-v2').trim();

			this.refreshSelectedIcon();
		},

		refreshSelectedIcon: function refreshSelectedIcon() {
			var currentValue = this.model.result['icon-class'];

			this.model.frame.$el
				.find('.fw-icon-v2-library-icon.selected')
				.removeClass('selected');

			if (currentValue) {
				this.model.frame.$el.find('[data-fw-icon-v2$="' + currentValue + '"]')
					.addClass('selected');
			}
		},

		refreshAttachment: function() {
			var currentId = this.model.result['attachment-id'];

			this.model.frame.$el
				.find('.fw-option-type-upload > input[type="hidden"]')
				.val(currentId).trigger('change');
		},

		markIconAsFavorite: function markIconAsFavorite (e) {
			e.preventDefault();
			e.stopPropagation();

			var icon = $(e.currentTarget).closest('.fw-icon-v2-library-icon').attr(
				'data-fw-icon-v2'
			);

			this.model.markAsFavorite(icon);

			this.renderFavorites();
			this.refreshFavorites();
		},

		refreshFavorites: function() {
			$('.fw-icon-v2-favorite').removeClass('fw-icon-v2-favorite');

			_.map(
				this.model.currentFavorites,
				setFavoriteClass
			);

			function setFavoriteClass (favorite) {
				$('[data-fw-icon-v2="' + favorite + '"]').addClass('fw-icon-v2-favorite');
			}
		},

		renderFavorites: function() {
			var $favorites = this.model.frame.$el.find('.fw-icon-v2-icon-favorites');

			$favorites.empty();

			$favorites.replaceWith(
				this.model.getFavoritesHtml()
			);
		},

		onSearch: function (event) {
			var $el = $(event.currentTarget);

			if (
				this.previousSearch.trim().length === 0
				&&
				$el.val().trim().length === 0
			) {
				return;
			}

			if ($el.val().trim().length === 0) {
				this.throttledApplyFilters();
			}

			if ($el.val().trim().length > 2) {
				this.throttledApplyFilters();
			}

			this.previousSearch = $el.val();
		}
	}),

	initialize: function(attributes, settings) {
		fw.Modal.prototype.initialize.call(this, attributes, settings);
		this.set('picker_id', fw.randomMD5());

		this.currentFavorites = null;

		this.result = {};

		jQuery.when(
			this.loadIconsData(),
			this.loadLatestFavorites()
		).then(
			_.bind(this.setHtml, this)
		);

		this.attachEvents();

		this.frame.on('close', _.bind(this.rejectResultAndResetIt, this));

		this.frame.once('ready', _.bind(function () {
			var modal = this;
			this.frame.$el.find('.fw-options-tabs-wrapper').on('tabsactivate', function (event, ui) {
				/**
				 * Every tab change should cause a change on a modal.
				 *
				 * It may be the case that the user will switch to
				 * `Custom Upload` and the value of the option type won't change
				 * because of the fact that `change:values` callback will not
				 * be executed.
				 */
				modal.result.type = ui.newTab.index() === 2 ? 'custom-upload' : 'icon-font';
			});
			
		}, this));

	},

	resolveResult: function () {
		if (this.promise) {
			this.promise.resolve(this.result);
		}

		this.promise = null;
	},

	rejectResultAndResetIt: function() {
		if (this.promise) {
			this.promise.reject(this.result);
		}

		this.promise = null;
	},

	initializeFrame: function (settings) {
		fw.OptionsModal.prototype.initializeFrame.call(this, settings);
	},

	setAttachment: function (data) {
		if (data.$element.attr('data-fw-icon-v2-id') !== this.get('picker_id')) {
			return;
		}

		this.result.attachment = data.attachment;

		if (data.attachment) {
			this.result['attachment-id'] = data.attachment.get('id');
			this.result['url'] = data.attachment.get('url');
		} else {
			this.result['attachment-id'] = "";
			this.result['url'] = "";
		}
	},

	attachEvents: function() {
		fwEvents.on('fw:option-type:upload:change', this.setAttachment, this);
		fwEvents.on('fw:option-type:upload:clear', this.setAttachment, this);
	},

	setHtml: function() {
		this.set('html', this.getTabsHtml());
	},

	open: function(values) {
		this.promise = jQuery.Deferred();

		this.get('controls_ready') && this.set(
			'controls_ready',
			!! this.frame.state()
		);

		values = values || {
			type: 'icon-font',
			'icon-class': ''
		}

		this.set('current_state', values);
		this.setResultBasedOnCurrentState();

		if (this.frame.state()) {
			this.prepareForPick();
		}

		this.frame.open();

		/**
		 * On first open, modal is prepared here.
		 */
		if (! this.get('controls_ready')) {
			setTimeout(_.bind(this.prepareForPick, this), 0);
			this.setupControls();
		}

		return this.promise;
	},

	close: function () {
		fw.Modal.prototype.close.call(this);
	},

	setResultBasedOnCurrentState: function() {
		this.result = this.get('current_state');
	},

	prepareForPick: function() {
		// use this.get('current_state') in order to populate current
		// active icon or current attachment
		//
		// this.frame.$el.find('.ui-tabs').tabs({active: 2});

		var $tabs = this.frame.$el.find('.ui-tabs');

		var currentTab = $tabs.tabs('option', 'active');

		if (this.get('current_state').type === 'custom-upload') {
			if (currentTab !== 2) {
				$tabs.tabs({active: 2});
			}

			this.content.refreshAttachment();
		}

		if (this.get('current_state').type !== 'custom-upload') {
			if (currentTab === 2) {
				$tabs.tabs({active: 0});
			}

			this.content.refreshSelectedIcon();

			if (this.result['icon-class']) {
				var el = this.frame.$el.find(
					'[data-fw-icon-v2$="' + this.result['icon-class'] + '"]'
				)[0];

				if (el.scrollIntoViewIfNeeded) {
					el.scrollIntoViewIfNeeded(true);
				} else if (el.scrollIntoView) {
					el.scrollIntoView();
				}
			}
		}
	},

	setupControls: function() {
		this.frame.$el.find('.fw-icon-v2-toolbar select').selectize({
			onChange: _.bind(this.applyFilters, this)
		});

		this.content.refreshFavorites();
		this.content.computeModalHeight();

		this.frame.$el.find('.fw-option-type-upload').attr(
			'data-fw-icon-v2-id', this.get('picker_id')
		);
	},

	applyFilters: function() {
		var pack = this.frame.$el.find(
			'.fw-icon-v2-icons-library .fw-icon-v2-toolbar select'
		)[0].value;

		var search = this.frame.$el.find(
			'.fw-icon-v2-icons-library .fw-icon-v2-toolbar input'
		).val().trim();

		var packs = this.getFilteredPacks({
			pack: pack,
			search: search
		});

		this.frame.$el.find(
			'.fw-icon-v2-library-packs-wrapper'
		).html(
			wp.template('fw-icon-v2-packs')({
				packs: packs,
				current_state: this.result,
				favorites: this.currentFavorites
			})
		);
	},

	getFilteredPacks: function(filters) {
		var self = this;

		filters = _.extend({}, {
			search: '',
			pack: 'all'
		}, filters);

		var packs = [];

		if (filters.pack.trim() === '' || filters.pack === 'all') {
			packs = _.values(this.getIconsData());
		} else {
			packs = [ this.getIconsData()[filters.pack] ];
		}

		packs = _.map(packs, function (pack) {
			var newPack = _.extend({}, pack);

			newPack.icons = _.filter(pack.icons, function (icon) {
				return self.fuzzyConsecutive(filters.search, icon);
			});

			return newPack;
		});

		return _.reject(packs, _.isEmpty);
	},

	loadIconsData: function () {
		if (this.iconsDataPromise) { return this.iconsDataPromise; }

		this.iconsDataPromise = jQuery.post(ajaxurl, {action: 'fw_icon_v2_get_icons'});
		this.iconsDataPromise.then(_.bind(this.preloadFonts, this));
		return this.iconsDataPromise;
	},

	getIconsData: function () {
		this.loadIconsData();

		if (this.iconsDataPromise.state() === 'resolved') {
			if (this.iconsDataPromise.responseJSON.success) {
				return this.iconsDataPromise.responseJSON.data;
			}
		}

		return null;
	},

	loadLatestFavorites: function () {
		if (this.favoritesPromise) { return this.favoritesPromise; }

		this.favoritesPromise = jQuery.post(ajaxurl, {
			action: 'fw_icon_v2_get_favorites'
		});

		this.favoritesPromise.then(_.bind(this.getFavoritesData, this));

		return this.favoritesPromise;
	},

	getFavoritesData: function () {
		this.loadLatestFavorites();

		if (this.favoritesPromise.state() === 'resolved') {
			this.currentFavorites = _.uniq(this.favoritesPromise.responseJSON);
		}

		return null;
	},

	setFavorites: function () {
		var data = {
			action: 'fw_icon_v2_update_favorites',
			favorites: JSON.stringify(_.uniq(this.currentFavorites))
		};

		jQuery.post(
			ajaxurl,
			data,
			function(data) { }
		);
	},

	markAsFavorite: function(icon) {
		icon = icon.trim();

		var isFavorite = _.contains(this.currentFavorites, icon);

		if (isFavorite) {
			this.currentFavorites = _.uniq(_.reject(this.currentFavorites, function (favorite) {
				return favorite == icon;
			}));
		} else {
			this.currentFavorites.push(icon);
		}

		this.setFavorites();
	},

	preloadFonts: function () {
		_.map(this.getIconsData(), preloadFont);

		function preloadFont (pack) {
			var $el = jQuery(
				'<i class="' + pack.css_class_prefix + " " + pack.icons[0] +
				'" style="opacity: 0;">'
			);

			jQuery('body').append(
				$el
			);

			setTimeout(function () { $el.remove(); }, 200);
		}
	},

	getTabsHtml: function () {
		return wp.template('fw-icon-v2-tabs')({
			icons_library_html: this.getLibraryHtml(),
			favorites_list_html: this.getFavoritesHtml(),
			current_state: this.result,
			favorites: this.currentFavorites
		});
	},

	getLibraryHtml: function() {

		return wp.template('fw-icon-v2-library')({
			packs: _.values(this.getIconsData()),
			current_state: this.result,
			favorites: this.currentFavorites
		})

	},

	getFavoritesHtml: function() {

		return wp.template('fw-icon-v2-favorites')({
			favorites: this.currentFavorites,
			current_state: this.result
		})

	},

	fuzzyConsecutive: function fuzzyConsecutive (query, search) {
		if (query.trim() === '') return true;
		return search.toLowerCase().trim().indexOf(query.toLowerCase()) > -1;
	}
});

fwOptionTypeIconV2Instance = new fwOptionTypeIconV2Picker();

})(jQuery);
