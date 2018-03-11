;(function($) {
	window.fwOptionTypeIconV2Picker = fw.Modal.extend({
		defaults: _.extend({}, fw.Modal.prototype.defaults, {
			title: 'Icon V2',
			size: 'small',
			modalCustomClass: 'fw-icon-v2-picker-modal',
			emptyHtmlOnClose: false,
			disableResetButton: true,
		}),

		ContentView: fw.Modal.prototype.ContentView.extend({
			events: {
				'input .fw-icon-v2-icons-library .fw-icon-v2-toolbar input':
					'onSearch',
				'click .fw-icon-v2-library-icon': 'markIconAsSelected',
				'click .fw-icon-v2-library-icon a': 'markIconAsFavorite',
				'click button.fw-icon-v2-custom-upload-perform':
					'performImageUpload',
				submit: 'onSubmit',
			},

			initialize: function() {
				fw.Modal.prototype.ContentView.prototype.initialize.call(this)

				// keep track of current searches for better performance
				this.previousSearch = ''

				this.throttledApplyFilters = _.throttle(
					_.bind(this.model.applyFilters, this.model),
					200
				)
			},

			onSubmit: function(e) {
				this.model.resolveResult()

				var content = this

				e.preventDefault()

				setTimeout(function() {
					content.model.frame.modal.$el
						.find('.media-modal-close')
						.trigger('click')
				}, 0)
			},

			performImageUpload: function() {
				var vm = this

				var uploadFrame = wp.media({
					library: {
						type: 'image',
					},

					states: new wp.media.controller.Library({
						library: wp.media.query({type: 'image'}),
						multiple: true,
						filterable: 'uploaded',
						content: 'upload',
						title: 'Select Image',
						priority: 20,
					}),
				})

				uploadFrame.on('ready', function() {
					uploadFrame.modal.$el.addClass('fw-option-type-upload')
				})

				uploadFrame.off('select')

				uploadFrame.on('select', function() {
					var attachments = uploadFrame
						.state()
						.get('selection')
						.toArray()

					attachments.map(function(attachment) {
						if (
							!_.contains(
								vm.model.currentFavorites,
								attachment.id.toString()
							)
						) {
							vm.model.markAsFavorite(attachment.id.toString())
						}
					})

					vm.renderFavoritesAndRecentUploads()

					uploadFrame.detach()
				})

				uploadFrame.open()
			},

			markIconAsSelected: function markIconAsSelected(e) {
				e.preventDefault()

				var $el = $(e.currentTarget)

				var type =
					$el.closest(
						'[data-fw-option-id="upload-custom-icon-recents"]'
					).length > 0
						? 'custom-upload'
						: 'icon-font'

				var result = $el.attr('data-fw-icon-v2').trim()

				this.model.result[
					type === 'custom-upload' ? 'attachment-id' : 'icon-class'
				] = result

				if (type === 'custom-upload') {
					this.model.result.url = wp.media
						.attachment(result)
						.get('url')
				}

				this.refreshSelectedIcon()
			},

			refreshSelectedIcon: function refreshSelectedIcon() {
				this.model.frame.$el
					.find('.fw-icon-v2-library-icon.selected')
					.removeClass('selected')

				if (this.model.result.type === 'icon-font') {
					var currentValue = this.model.result['icon-class']
				} else if (this.model.result.type === 'custom-upload') {
					var currentValue = this.model.result['attachment-id']
				}

				if (currentValue) {
					this.model.frame.$el
						.find('[data-fw-icon-v2$="' + currentValue + '"]')
						.addClass('selected')
				}
			},

			markIconAsFavorite: function markIconAsFavorite(e) {
				e.preventDefault()
				e.stopPropagation()

				var icon = $(e.currentTarget)
					.closest('.fw-icon-v2-library-icon')
					.attr('data-fw-icon-v2')

				this.model.markAsFavorite(icon)

				this.renderFavoritesAndRecentUploads()
				this.refreshFavorites()
			},

			refreshFavorites: function() {
				$('.fw-icon-v2-favorite').removeClass('fw-icon-v2-favorite')

				_.map(this.model.currentFavorites, function(favorite) {
					if (
						_.compose(
							_.negate(_.isNaN),
							_.partial(parseInt, _, 10)
						)(favorite)
					) {
						return
					}

					$('[data-fw-icon-v2="' + favorite + '"]').addClass(
						'fw-icon-v2-favorite'
					)
				})
			},

			renderFavoritesAndRecentUploads: function() {
				this.model.frame.$el
					.find('.fw-favorite-icons-wrapper')
					.replaceWith(this.model.getFavoritesHtml())

				this.model.frame.$el
					.find(
						'[data-fw-option-id="upload-custom-icon-recents"] .fw-option-html'
					)
					.html(this.model.getRecentIconsHtml())
			},

			onSearch: function(event) {
				var $el = $(event.currentTarget)

				if (
					this.previousSearch.trim().length === 0 &&
					$el.val().trim().length === 0
				) {
					return
				}

				if ($el.val().trim().length === 0) {
					this.throttledApplyFilters()
				}

				if ($el.val().trim().length > 2) {
					this.throttledApplyFilters()
				}

				this.previousSearch = $el.val()
			},
		}),

		initialize: function(attributes, settings) {
			fw.Modal.prototype.initialize.call(this, attributes, {
				disableResetButton: true,
			})

			var modal = this

			this.currentFavorites = null

			this.result = {}

			jQuery.when(this.loadIconsData()).then(
				_.bind(function() {
					this.set('html', this.getTabsHtml())
				}, this)
			)

			jQuery.when(this.loadLatestFavorites()).then(
				_.bind(function() {
					this.content.renderFavoritesAndRecentUploads()
					this.content.refreshFavorites()
				}, this)
			)

			this.frame.on('close', _.bind(this.rejectResultAndResetIt, this))
		},

		resolveResult: function() {
			if (this.promise) {
				this.promise.resolve(this.result)
			}

			this.promise = null
		},

		rejectResultAndResetIt: function() {
			if (this.promise) {
				this.promise.reject(this.result)
			}

			this.promise = null
		},

		initializeFrame: function(settings) {
			fw.OptionsModal.prototype.initializeFrame.call(this, settings)
		},

		open: function(values) {
			this.promise = jQuery.Deferred()

			this.get('controls_ready') &&
				this.set('controls_ready', !!this.frame.state())

			values = values || {
				type: 'icon-font',
				'icon-class': '',
			}

			if (values.type === 'none') {
				values.type = 'icon-font'
			}

			this.set('current_state', values)
			this.result = this.get('current_state')

			if (this.frame.state()) {
				this.prepareForPick()
			}

			this.frame.open()

			/**
			 * On first open, modal is prepared here.
			 */
			if (!this.get('controls_ready')) {
				this.prepareForPick()
			}

			return this.promise
		},

		close: function() {
			fw.Modal.prototype.close.call(this)
		},

		prepareForPick: function() {
			var modal = this

			modal.frame.$el.find('.fw-icon-v2-toolbar select').selectize({
				plugins: ['hidden_textfield'],
				onChange: _.bind(modal.applyFilters, modal),
			})

			modal.frame.$el
				.find('.fw-options-tabs-wrapper')
				.off('tabsactivate.fwiconv2')
				.on('tabsactivate.fwiconv2', function(event, ui) {
					/**
					 * Every tab change should cause a change on a modal.
					 *
					 * It may be the case that the user will switch to
					 * `Custom Upload` and the value of the option type won't change
					 * because of the fact that `change:values` callback will not
					 * be executed.
					 */
					modal.result.type =
						ui.newTab.index() === 1 ? 'custom-upload' : 'icon-font'
				})

			this.content.renderFavoritesAndRecentUploads()
			this.content.refreshFavorites()

			var $tabs = modal.frame.$el.find('.ui-tabs')
			var currentTab = $tabs.tabs('option', 'active')

			if (modal.get('current_state').type === 'custom-upload') {
				if (currentTab !== 1) {
					$tabs.tabs({active: 1})
				}
			}

			if (modal.get('current_state').type !== 'custom-upload') {
				if (currentTab === 1) {
					$tabs.tabs({active: 0})
				}

				if (modal.result['icon-class']) {
					this.frame.$el
						.find(
							'.fw-icon-v2-icons-library .fw-icon-v2-toolbar input.fw-option-type-text'
						)
						.val('')

					var packForIcon = _.findWhere(
						_.values(this.getIconsData()),
						{
							css_class_prefix: this.result['icon-class'].split(
								' '
							)[0],
						}
					)

					var selectInput = modal.frame.$el.find(
						'.fw-icon-v2-icons-library .fw-icon-v2-toolbar select'
					)[0]

					if (selectInput && selectInput.value !== packForIcon) {
						this.frame.$el
							.find(
								'.fw-icon-v2-icons-library .fw-icon-v2-toolbar input.fw-option-type-text'
							)
							.val('')

						selectInput.selectize.setValue(packForIcon.name)
					}
				}
			}
		},

		applyFilters: function() {
			var packSelect = this.frame.$el.find(
				'.fw-icon-v2-icons-library .fw-icon-v2-toolbar select'
			)[0]

			var pack = packSelect
				? packSelect.value
				: _.keys(this.getIconsData())[0]

			var search = this.frame.$el
				.find(
					'.fw-icon-v2-icons-library .fw-icon-v2-toolbar input.fw-option-type-text'
				)
				.val()
				.trim()

			var packs = this.getFilteredPacks({
				pack: pack,
				search: search,
			})

			this.frame.$el
				.find(
					'[data-fw-option-id="icon-font"] .fw-icon-v2-library-pack-wrapper'
				)
				.html(
					wp.template('fw-icon-v2-packs')({
						packs: packs,
						current_state: this.result,
						should_have_headings: search.trim().length > 0,
						favorites: this.currentFavorites,
					})
				)

			this.content.refreshSelectedIcon()
		},

		getFilteredPacks: function(filters) {
			var self = this

			filters = _.extend(
				{},
				{
					search: '',
					pack: '',
				},
				filters
			)

			var packs = []

			/*
			if (filters.pack.trim() === '' || filters.pack === 'all') {
				packs = [ _.first(_.values(this.getIconsData())) ];
			} else {
				packs = [this.getIconsData()[filters.pack]];
			}
			*/

			if (filters.search.trim() === '') {
				packs = [this.getIconsData()[filters.pack]]
			} else {
				packs = _.values(this.getIconsData())
			}

			packs = _.map(packs, function(pack) {
				var newPack = _.extend({}, pack)

				newPack.icons = _.filter(pack.icons, function(icon) {
					return self.fuzzyConsecutive(filters.search, icon)
				})

				return newPack
			})

			return _.reject(packs, function(pack) {
				return _.isEmpty(pack.icons)
			})
		},

		loadIconsData: function() {
			if (this.iconsDataPromise) {
				return this.iconsDataPromise
			}

			this.iconsDataPromise = jQuery.post(ajaxurl, {
				action: 'fw_icon_v2_get_icons',
			})

			this.iconsDataPromise.then(_.bind(this.preloadFonts, this))

			return this.iconsDataPromise
		},

		getIconsData: function() {
			this.loadIconsData()

			if (this.iconsDataPromise.state() === 'resolved') {
				if (this.iconsDataPromise.responseJSON.success) {
					return this.iconsDataPromise.responseJSON.data
				}
			}

			return null
		},

		loadLatestFavorites: function() {
			var modal = this

			if (modal.favoritesPromise) {
				return modal.favoritesPromise
			}

			modal.favoritesPromise = $.Deferred()

			var ajaxPromise = $.post(ajaxurl, {
				action: 'fw_icon_v2_get_favorites',
			})

			ajaxPromise.then(function() {
				if (ajaxPromise.state() === 'resolved') {
					modal.currentFavorites = _.uniq(ajaxPromise.responseJSON)
				}

				var recent_uploads = _.filter(
					ajaxPromise.responseJSON,
					_.compose(_.negate(_.isNaN), _.partial(parseInt, _, 10))
				)

				if (recent_uploads.length === 0) {
					modal.favoritesPromise.resolve()
					return
				}

				wp.media
					.query({post__in: recent_uploads, perPage: 200})
					.more()
					.then(function() {
						var oldLength = modal.currentFavorites.length

						recent_uploads.map(function(id) {
							if (!wp.media.attachment(id).get('url')) {
								modal.currentFavorites = _.without(
									modal.currentFavorites,
									id
								)
							}
						})

						if (oldLength !== modal.currentFavorites.length) {
							modal.syncFavoritesToServer()
						}

						modal.favoritesPromise.resolve()
					})
			})

			return modal.favoritesPromise
		},

		syncFavoritesToServer: function() {
			jQuery.post(ajaxurl, {
				action: 'fw_icon_v2_update_favorites',
				favorites: JSON.stringify(_.uniq(this.currentFavorites)),
			})
		},

		markAsFavorite: function(icon) {
			icon = icon.trim()

			var modal = this

			var isFavorite = _.contains(modal.currentFavorites, icon)

			if (isFavorite) {
				modal.currentFavorites = _.uniq(
					_.reject(modal.currentFavorites, function(favorite) {
						return favorite == icon
					})
				)
			} else {
				modal.currentFavorites.push(icon)
			}

			this.syncFavoritesToServer()
		},

		preloadFonts: function() {
			_.map(this.getIconsData(), preloadFont)

			function preloadFont(pack) {
				var $el = jQuery(
					'<i class="' +
						pack.css_class_prefix +
						' ' +
						pack.icons[0] +
						'" style="opacity: 0;">'
				)

				jQuery('body').append($el)

				setTimeout(function() {
					$el.remove()
				}, 200)
			}
		},

		getTabsHtml: function() {
			return wp.template('fw-icon-v2-tabs')({
				icons_library_html: this.getLibraryHtml(),
				favorites_list_html: this.getFavoritesHtml(),
				recently_used_custom_uploads_html: this.getRecentIconsHtml(),
				current_state: this.result,
				favorites: this.currentFavorites,
			})
		},

		getLibraryHtml: function() {
			var packs = _.values(this.getIconsData())
			var pack_to_select = [_.first(packs)]

			return wp.template('fw-icon-v2-library')({
				packs: _.values(this.getIconsData()),
				pack_to_select: pack_to_select,
				current_state: this.result,
				favorites: this.currentFavorites,
			})
		},

		getFavoritesHtml: function() {
			return wp.template('fw-icon-v2-favorites')({
				favorites: this.currentFavorites || [],
				current_state: this.result,
			})
		},

		getRecentIconsHtml: function() {
			return wp.template('fw-icon-v2-recent-custom-icon-uploads')({
				favorites: this.currentFavorites || [],
				current_state: this.result,
			})
		},

		fuzzyConsecutive: function fuzzyConsecutive(query, search) {
			if (query.trim() === '') return true

			return (
				search
					.toLowerCase()
					.trim()
					.indexOf(query.toLowerCase()) > -1
			)
		},
	})

	$(function() {
		fwOptionTypeIconV2Instance = new fwOptionTypeIconV2Picker()
	})

	Selectize.define('hidden_textfield', function(options) {
		var self = this

		this.showInput = function() {
			this.$control.css({cursor: 'pointer'})

			this.$control_input.css({
				opacity: 0,
				position: 'relative',
				left: self.rtl ? 10000 : -10000,
			})

			this.isInputHidden = false
		}

		this.setup_original = this.setup

		this.setup = function() {
			self.setup_original()
			this.$control_input.prop('disabled', 'disabled')
		}
	})
})(jQuery)
