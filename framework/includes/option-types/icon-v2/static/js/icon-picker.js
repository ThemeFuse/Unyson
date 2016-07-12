window.fwOptionTypeIconV2Picker = (function ($) {
	var modal = null,
		currentValues = null,
		callback = null,
		uniqueId = null;

	var currentFavorites = [];
	$(window).on('resize', computeModalHeight);

	$(document).on('click', '.fw-icon-v2-library-icon', markIconAsSelected);
	$(document).on('click', '.fw-icon-v2-library-icon a', markIconAsFavorite);

	$(document).on(
		'input',
		'.fw-icon-v2-icons-library .fw-icon-v2-toolbar input',
		applyFilters
	);

	return {
		pick: pick
	};

	function pick (values, id, fn, modalSize) {
		currentValues = values;
		callback = fn;
		uniqueId = id;

		createModal(modalSize);

		modal.open();
	}

	function createModal (modalSize) {
		if (modal) {
			modal.set('size', modalSize);
			return;
		}

		modal = new fw.OptionsModal({
			modalCustomClass: 'fw-icon-v2-picker-modal',
			disableLazyTabs: true,
			title: '',
			size: modalSize,
			options: [
				{
					'icon-fonts': {
						type: 'tab',
						title: fw_icon_v2_data.icon_fonts_label,
						options: {
							'icon-font': {
								type: 'html-full',
								attr: {
									class: 'fw-icon-v2-icons-library'
								},
								label: false,
								html: iconLibraryHTML()
							}
						}
					}
				},

				{
					'favorites': {
						type: 'tab',
						title: fw_icon_v2_data.favorites_label,
						attr: {
							class: '.fw-icon-v2-favorites'
						},
						options: {
							'icon-font-favorites': {
								type: 'html-full',
								label: false,
								html: '<div class="fw-icon-v2-icon-favorites"></div>'
							}
						}
					}
				},

				{
					'custom-upload': {
						type: 'tab',
						title: fw_icon_v2_data.custom_upload_label,
						options: {
							'custom-upload': {
								label: 'Upload Icon',
								type: 'upload'
							}
						}
					}
				}
			]
		});

		modal.on('change:values', function(modal, values) {
			// run callback here
			// get values based on current tab

			var type = modal.frame.$el.find('.fw-options-tabs-wrapper')
				.tabs('instance').active.index() === 2 ? 'custom-upload' : 'icon-font';

			if (type == "icon-font") {
				callback({
					type: 'icon-font',
					'icon-class': values['icon-font']
				});
			}

			if (type === 'custom-upload') {
				if (values['custom-upload'] === '') {
					values['custom-upload'] = {
						attachment_id: '',
						url: ''
					};
				}

				callback({
					type: 'custom-upload',
					'attachment-id': values['custom-upload']['attachment_id'],
					'url': values['custom-upload']['url']
				});
			}
		});

		modal.on('render', function () {
			/**
			 * Every icon picker change should trigger a new callback chain
			 * execution.
			 */
			modal.set(
				'values',
				_.extend({}, modal.get('values'), {
					current_picker: uniqueId
				}),
				{silent: true}
			);

			// select correct tab here, based on currentValues
			// also render icon sets here, if needed
			var $select = modal.frame.$el.find('.fw-icon-v2-toolbar select');

			if (! $select[0].selectize) {
				$select.selectize({
					onChange: applyFilters
				});
			}

			if (modal.frame.$el.find('.fw-icon-v2-icons-library ul').length === 0) {
				renderIconsLibrary({
					search: '',
					packs: _.values(fw_icon_v2_data.icons)
				});

				setTimeout(computeModalHeight, 100);
			}

			getLatestFavorites(refreshFavoritesClasses);

			/**
			 * Set initial values for modal. They're based on the
			 * current values from current picker.
			 */
			if (currentValues.type === 'icon-font') {
				modal.frame.$el.find(
					'.fw-icon-v2-icons-library > input[type="hidden"]'
				).val(
					currentValues['icon-class']
				);
			}

			refreshSelectedIcon();

			if (currentValues.type === 'custom-upload') {
				var currentCustomUpload = '';

				if (currentValues.url.trim() !== '') {
					currentCustomUpload = {
						url: currentValues['url'],
						attachment_id: currentValues['attachment-id']
					};
				}

				modal.set(
					'values',
					_.extend({}, modal.get('values'), {
						'custom-upload': currentCustomUpload
					})
				);

				modal.updateHtml();
			}

			modal.frame.$el.find('.fw-options-tabs-wrapper').on('tabsactivate', function (event, ui) {
				/**
				 * Every tab change should cause a change on a modal.
				 *
				 * It may be the case that the user will switch to
				 * `Custom Upload` and the value of the option type won't change
				 * because of the fact that `change:values` callback will not
				 * be executed.
				 */
				modal.set(
					'values',
					_.extend({}, modal.get('values'), {
						current_tab: ui.newTab.index()
					}),
					{silent: true}
				);
			});

			modal.frame.$el.find('.fw-options-tabs-wrapper').tabs({
				active: currentValues.type == 'custom-upload' ? 2 : 0
			});
		});

		modal.on('open', function () {
		});

		modal.on('close', function () {
		});
	}

	function iconLibraryHTML () {
		var searchInput = '<input type="text" placeholder="' +
            fw_icon_v2_data.search_label +
            '" class="fw-option fw-option-type-text">';
		var selectPack = [
            '<select class="fw-selectize">',
				'<option value="all">' + fw_icon_v2_data.all_packs_label + '</option>'
		].concat(

			_.map(
				_.values(fw_icon_v2_data.icons),
				function (e) {return '<option value="' + e.name + '">' + e.title + '</option>';}
			)

		).concat(
			'</select>'
		).join('');

		var toolbarContainer = [
			'<div class="fw-icon-v2-toolbar">',
				searchInput,
				selectPack,
			'</div>'
		].join('');

		var packsContainer = '<div class="fw-icon-v2-library-packs-wrapper"></div>';

		return toolbarContainer + packsContainer;
	}

	/**
	 * options.search - search string
	 * options.packs - packs to render
	 */
	function renderIconsLibrary (options) {
		options = _.extend({}, {
			search: '',
			packs: []
		}, options);

		$('.fw-icon-v2-library-packs-wrapper').html(

			_.map(
				options.packs,
				renderPack
			).join('')

		);

		refreshSelectedIcon();

		function renderPack (pack) {
			filteredIcons = _.filter(pack.icons, function (icon) {
				return fuzzyConsecutive(options.search, icon);
			});

			if (filteredIcons.length === 0) return '';

			var packHeader = '<h2><span>' + pack.title + '</span></h2>';

			return packHeader + renderIconsCollection(pack, filteredIcons);
		}
	}

	function renderIconsCollection (pack, icons, favorites) {

		return '<ul class="fw-icon-v2-library-pack">' + _.map(
			icons,
			renderSingleIcon
		).join('') + '</ul>';

		function renderSingleIcon (icon) {
			var iconClass = favorites ? icon : pack.css_class_prefix + ' ' + icon;

			var liClass = 'fw-icon-v2-library-icon';

			if (currentValues.type === 'icon-font' && currentValues['icon-class'] === iconClass) {
				liClass += ' selected';
			}

			return [
				'<li data-fw-icon-v2="' + iconClass + '" class="' + liClass + '">',
					'<i class="' + iconClass + '"></i>',
					'<a title="Add to Favorites" class="fw-icon-v2-favorite"><i class="dashicons dashicons-star-filled"></i></a>',
				'</li>'
			].join('');
		}
	}

	function markIconAsSelected (event) {
		event.preventDefault();

		modal.frame.$el.find('.fw-icon-v2-icons-library').find('> input[type="hidden"]').val(
			$(this).attr('data-fw-icon-v2')
		);

		refreshSelectedIcon();
	}

	function markIconAsFavorite (event) {
		event.preventDefault();
		event.stopPropagation();

		var icon = $(this).closest('.fw-icon-v2-library-icon').attr(
			'data-fw-icon-v2'
		);

		var isFavorite = _.contains(currentFavorites, icon);

		if (isFavorite) {
			currentFavorites = _.reject(currentFavorites, function (favorite) {
				return favorite == icon;
			});
		} else {
			currentFavorites.push(icon);
		}

		var data = {
			action: 'fw_icon_v2_update_favorites',
			favorites: JSON.stringify(currentFavorites)
		};

		refreshFavoritesClasses(currentFavorites);

		jQuery.post(
			ajaxurl,
			data,
			function(data) {
			}
		);
	}

	function refreshSelectedIcon () {
		var currentValue = modal.frame.$el.find(
			'.fw-icon-v2-icons-library > input[type="hidden"]'
		).val();

		modal.frame.$el
			.find('.fw-icon-v2-library-icon.selected')
			.removeClass('selected');

		modal.frame.$el.find('[data-fw-icon-v2="' + currentValue + '"]')
			.addClass('selected');
	}

	function applyFilters () {
		var search = modal.frame.$el.find(
			'.fw-icon-v2-icons-library .fw-icon-v2-toolbar input'
		).val().trim();

		var packs = modal.frame.$el.find(
			'.fw-icon-v2-icons-library .fw-icon-v2-toolbar select'
		)[0].value;

		if (packs.trim() === '' || packs === 'all') {
			packs = _.values(fw_icon_v2_data.icons);
		} else {
			packs = [ fw_icon_v2_data.icons[packs] ];
		}

		renderIconsLibrary({
			search: search,
			packs: packs
		});

		refreshFavoritesClasses(currentFavorites);
	}

	function fuzzyConsecutive (query, search) {
		if (query.trim() === '') return true;
		return search.toLowerCase().trim().indexOf(query.toLowerCase()) > -1;
	};

	function getLatestFavorites (callback) {
		var data = {
			action: 'fw_icon_v2_get_favorites'
		};

		jQuery.post(ajaxurl, data, function(data) {
			currentFavorites = data;
			callback(data);
		});
	}

	function refreshFavoritesClasses (favorites) {
		$('.fw-icon-v2-favorite').removeClass('fw-icon-v2-favorite');

		_.map(
			favorites,
			setFavoriteClass
		);

		renderFavorites();

		function setFavoriteClass (favorite) {
			$('[data-fw-icon-v2="' + favorite + '"]').addClass('fw-icon-v2-favorite');
		}
	}

	function renderFavorites () {
		var $favorites = modal.frame.$el.find('.fw-icon-v2-icon-favorites');

		$favorites.html(
			_.isEmpty(currentFavorites)
				?  fw_icon_v2_data.favorites_empty_label
				: renderIconsCollection(null, currentFavorites, true)
		);
	}

	function computeModalHeight () {
		if (! modal) { return; }
		var $icons = modal.frame.$el.find('.fw-icon-v2-library-packs-wrapper');
		var toolbarHeight = modal.frame.$el.find('.fw-icon-v2-toolbar').height();

		$icons.height(
			modal.frame.$el.find(
				'.fw-options-tabs-contents.metabox-holder'
			).height() - toolbarHeight - 50
		);
	}

})(jQuery);
