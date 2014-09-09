var fwSidebars;
(function($) {

fwSidebars = {

	lastActiveTabIndex : 0,
	the_widget_id : null,

	init : function() {
		fwSidebars.sidebarsTabsInit();
		fwSidebars.initDeleteButtons();

		fwEvents.on('fw:sidebars:remove:sidebar:error', function(data){
			var $deleteButton = $('#widgets-right').find('#' + data.sidebarId + ' .fw-ext-sidebars-delete-button');
			fwSidebars.showTip($deleteButton, data.message);
		});

		fwEvents.on('fw:sidebars:remove:sidebar:success', function(data){
			fwSidebars.removeSidebar(data.sidebarId);
		});

		fwEvents.on('fw:sidebars:remove:sidebar:click', function(data){
			//todo: ajax manager
			if (!fwSidebars.isBusy) {
				fwSidebars.removeSidebarAjax(data);
			}
			data.event.stopPropagation();
		});

		/**
		 * Ajax delete preset
		 */
		$('.fw-ext-sidebars-preset-remove').on('click', function(e){
			var $elem = $(this);
			fwSidebars.removePresetAjax($elem);
			return e.preventDefault();
		});

		/**
		 * Exit from init if only one 'created' tab was initialized
		 */
		if ($('.fw-sidebars-tabs-initialized ul li').length <= 1) {
			//disable edit presets
			$('.fw-ext-sidebars-preset-edit').on('click', function(e){
				return false;
			});

			fwEvents.on('fw:sidebars:created-tab:recalculate',function(data){
				if (data.count <= 0){
					$('.fw-ext-sidebars-wrap-container').remove();
				}
			});

			return false;
		}

		fwSidebars.selectizeInit();
		fwSidebars.showPositions();

		fwEvents.on('fw:sidebars:created-tab:recalculate', function(data) {
			if (data.count <= 0){

				var tabIndex = 0;
				if (fwSidebars.lastActiveTabIndex < 2) {
					tabIndex = fwSidebars.lastActiveTabIndex;
				}

				$('.fw-sidebars-tabs-initialized').tabs('option','active', tabIndex );
			}
		});

		fwEvents.on('fw:sidebars:selectize:change:value', function(data) {
			data.$tab.find('.' + data.color).find('select.sidebar-selectize').data('selectize').setValue(data.sidebar);
		});

		fwEvents.on('fw:sidebars:create:sidebar:success', function(data){
			fwSidebars.onSidebarCreate(data.sidebar);
		});

		$('#widget-list').children('.widget').on('mousedown',function(e){
			fwSidebars.the_widget_id = e.currentTarget.id;
		});

		/**
		 * on click submit button start saving preset for grouped pages tab
		 */
		$('#submit-settings-grouped-positions').on('click', function(){
			var groupedPagesData = fwSidebars.getGroupedPagesTabData();
			fwSidebars.saveGroupedTabPresetAjax(groupedPagesData);
		});

		/**
		 * on click submit button start saving preset for specific pages tab
		 */
		$('#submit-settings-specific-positions').on('click', function(){
			var specificPagesData = fwSidebars.getSpecificPagesTabData();
			if (specificPagesData === false) {
				return false;
			}
			fwSidebars.saveSpecificTabPresetAjax(specificPagesData);
		});

		/**
		 * on created tab link click start edit preset
		 */
		$('.fw-ext-sidebars-preset-edit').on('click', function(e){
			var $elem = $(this);
			fwSidebars.editPreset($elem);
			e.preventDefault();
		});

		/**
		 * on change image-picker show selectize block by colors
		 */
		$('.fw-ext-sidebars-positions.fw-option-type-image-picker')
			.on('fw:option-type:image-picker:changed', function(e, data){
				fwSidebars.showPositions();
			});
	},

	/**
	 * Collecting data and make object for SpecificPages ajax request
	 */
	getSpecificPagesTabData: function() {
		var slugs = $.unique($.map($('.fw-sidebars-remove-page'), function(a){ return $(a).data('slug')}));

		if ($('.fw-sidebars-remove-page').length === 0){
			$('#specific-field-id').addClass('fw-ext-sidebars-error');
			return false;
		}

		var possibleColors = fwSidebars.getCurrentTab().find('.fw-ext-sidebars-positions select option:selected').data('extra-data');

		var specificPagesData = {
			preset:  fwSidebars.getCurrentTab().data('preset-id'),
			position: fwSidebars.getCurrentTab().find('.fw-ext-sidebars-positions select').val(),
			sidebars: (function(){
				var sidebars = {};

			if (possibleColors && possibleColors.colors) {
				fwSidebars.getCurrentTab().find('.fw-ext-sidebars-location').slice(0, possibleColors.colors).each(function(){
					var color = $(this).data('color');
					sidebars[color] = $(this).find('select').val();
				});
			}

				return sidebars;
			})(),
			selected: (function(){
				var selected = [];
				slugs.forEach(function(item){
					var emptyItem = {slug: null, ids: []}
					emptyItem.slug = item;
					$('.fw-sidebars-remove-page[data-slug="' + item + '"]').each(function(){
						emptyItem.ids[emptyItem.ids.length] = $(this).data('id');
					});
					selected[selected.length] = emptyItem;
				})

				return selected;
			})()
		};

		return specificPagesData;
	},

	/**
	 * Collecting data and make object for GroupedPages ajax request
	 */
	getGroupedPagesTabData: function() {
		var possibleColors = fwSidebars.getCurrentTab().find('.fw-ext-sidebars-positions select option:selected').data('extra-data');

		var groupedPagesData = {
			preset: null,
			slug: $('#fw-option-sidebars-for-grouped').val(),
			position: fwSidebars.getCurrentTab().find('.fw-ext-sidebars-positions select').val(),
			ids: null,
			sidebars: (function(){
				var sidebars = {};
				if (possibleColors && possibleColors.colors) {
					fwSidebars.getCurrentTab().find('.fw-ext-sidebars-location').slice(0, possibleColors.colors).each(function(){
						var color = $(this).data('color');
						sidebars[color] = $(this).find('select').val();
						});
				}
				return sidebars;
			})()
		};

		return groupedPagesData;
	},

	/**
	 * Ajax save settings for grouped tab
	 */
	saveGroupedTabPresetAjax: function(groupedPagesData) {
		var data = {
			action: 'save_sidebar_preset_ajax',
			settings: groupedPagesData
		}

		var $spinner = $('.fw-ext-sidebars-submiting-grouped-positions');
		$spinner.show();
		$.post(ajaxurl, data, function(response){
			$spinner.hide();
			if (response.success) {
				fwSidebars.resetSettings();
				$element = fwSidebars.renderCreatedTabPreset(response.data.slug, '', response.data.label, PhpVar.groupedTabDesc);

				$element.find('.fw-ext-sidebars-preset-remove').on('click',function(e){
					var $elem = $(this);
					fwSidebars.removePresetAjax($elem);
					return e.preventDefault();
				});

				$element.find('.fw-ext-sidebars-preset-edit').on('click', function(e){
					var $elem = $(this);
					fwSidebars.editPreset($elem);
					e.preventDefault();
				});

				fwSidebars.recalculateCreatedTab();
				$('.fw-sidebars-tabs-initialized').tabs('option','active', 2);
			} else {
				if (response.data.colors) {
					response.data.colors.forEach(function(color){
						fwSidebars.getCurrentTab().find('.fw-ext-sidebars-location.' + color + ' .selectize-input').addClass('fw-ext-sidebars-error');
					});
				}
			}
		}).fail(function(){
			$spinner.hide();
		});
	},

	/**
	 * Ajax save settings for specific tab
	 */
	saveSpecificTabPresetAjax: function(specificPagesData){

		var data = {
			action: 'save_sidebar_preset_ajax',
			settings: specificPagesData
		}

		$spinner = $('.fw-ext-sidebars-submiting-specific-positions');
		$spinner.show();
		$.post(ajaxurl, data, function(response){
			$spinner.hide();
			if (response.success) {
				fwSidebars.resetSettings();
				$element = fwSidebars.renderCreatedTabPreset('', response.data.preset, response.data.label, PhpVar.specificTabDesc);

				$element.find('.fw-ext-sidebars-preset-remove').on('click' ,function(e){
					var $elem = $(this);
					fwSidebars.removePresetAjax($elem);
					return e.preventDefault();
				});

				$element.find('.fw-ext-sidebars-preset-edit').on('click', function(e){
					var $elem = $(this);

					fwSidebars.editPreset($elem);
					e.preventDefault();
				});

				fwSidebars.recalculateCreatedTab();
				$('.fw-sidebars-tabs-initialized').tabs('option','active', 2);
			} else {
				if (response.data.colors) {
					response.data.colors.forEach(function(color){
						fwSidebars.getCurrentTab().find('.fw-ext-sidebars-location.' + color + ' .selectize-input').addClass('fw-ext-sidebars-error');
					});
				}
			}
		}).fail(function(){
			$spinner.hide();
		});
	},

	editPreset : function($elem){
		var presetId = $elem.parent().parent().data('preset-id');
		var slug = $elem.parent().parent().data('type');
		var tabIndex = 0;
		//Only specific tab has presetId
		if (presetId !== '') {
			tabIndex = 1;
		}

		fwSidebars.loadingPresetAjax(tabIndex, presetId, slug);
	},

	/**
	 *  Ajax removing preset from created tab
	 */
	removePresetAjax : function($elem){
		//disable multi-click
		if($elem.data('blocked')){
			return false;
		}
		$elem.data('blocked', true);

		var $loadingElement = $elem.parent().parent().find('.fw-ext-sidebars-preset-removing');
		var removePresetData = {
			preset: $elem.parent().parent().data('preset-id'),
			slug: $elem.parent().parent().data('type')
		}

		var data = {
			action: 'remove_sidebar_preset_ajax',
			data : removePresetData
		};

		$loadingElement.show()
		$.post(ajaxurl, data, function(response){
			$loadingElement.hide();
			$elem.data('blocked', false);
			if (response.success){
				$elem.parent().parent().remove();
				fwSidebars.recalculateCreatedTab();
			}else {
				fwSidebars.initQTip($elem);
				fwSidebars.showTip($elem, response.data.message)
			}

		}).fail(function(){
			$elem.data('blocked', false);
			$loadingElement.hide();
			return false;
		});
	},

	/**
	 * Recalculate created presets on created tab
	 */
	recalculateCreatedTab : function(){
			var cnt = $('.fw-ext-sidebars-created-tab-preset').length;

			$('a[href="#fw-sidebars-tab-3"]').text(cnt + ' '+ PhpVar.createdTabName);
			if (cnt < 1) {
				$('a[href="#fw-sidebars-tab-3"]').parent().hide();
				$('#fw-sidebars-tab-3').addClass('empty');
			}else {
				$('a[href="#fw-sidebars-tab-3"]').parent().show();
				$('#fw-sidebars-tab-3').removeClass('empty');
			}

			fwEvents.trigger('fw:sidebars:created-tab:recalculate', {count: cnt});
		},

	/**
	 * Ajax load preset
	 */
	loadingPresetAjax : function(tabIndex, presetId, slug){
		var data = {
			action: 'load_sidebar_preset_ajax',
			params: {
				preset: presetId,
				slug:   slug
			}
		}

		var $spinner = $('.fw-ext-sidebars-created-tab-preset[data-type="' + slug + '"][data-preset-id="' + presetId + '"]').find('.fw-ext-sidebars-preset-editing');
		$spinner.show();

		$.post(ajaxurl, data, function(response){
			if (response.success){

				var $selectedTab = fwSidebars.getSidebarTab(tabIndex)
				var slug = $selectedTab.find('.fw-ext-sidebars-selector select option:nth(0)').val();

				if (response.data.preset.slug) {
					slug = response.data.preset.slug;
				}

				$selectedTab.find('.fw-ext-sidebars-selector select').val(slug);

				if (response.data.by_ids) {
					$('.sidebars-specific-pages').find('div').remove();
					$('#specific-field-id').removeClass('fw-ext-sidebars-error');
					$.each(response.data.by_ids, function(key,item){
						var slug, name;
						for(var i in item.slug){
							slug = i;
							name = item.slug[i];
						}

						for(var i in item.ids){
							fwSidebars.addRemovableItem(i, item.ids[i], slug, name);
						}
					});
				}

				$.each(response.data.preset.sidebars, function(color, sidebar){
					fwEvents.trigger('fw:sidebars:selectize:change:value', { color: color, sidebar: sidebar, $tab: fwSidebars.getSidebarTab(tabIndex)});
				});

				$('.fw-sidebars-tabs-initialized').tabs('option','active', tabIndex);

				$selectedTab.data('preset-id', response.data.preset.preset);
				fwSidebars.changeImagePickerVal($selectedTab, response.data.preset.position);

			}else{
				console.log('error smth wrong');
			}
			$spinner.hide();
		}).fail(function(){
			$spinner.hide();
			return false;
		});
	},

	changeImagePickerVal : function($tab, value){
		$tab.find('.fw-ext-sidebars-positions select').val(value).data('picker').sync_picker_with_select();
		fwSidebars.showPositions();
	},

	/**
	 * Tabs init
	 */
	sidebarsTabsInit : function(){
		var $elements = $('.fw-sidebars-tabs-wrapper:not(.fw-sidebars-tabs-initialized)');

		if ($elements.length) {
			$elements.tabs({
				activate: function(event, ui){
					fwSidebars.showPositions();
				},
				beforeActivate: function() {
					fwSidebars.lastActiveTabIndex = $('.fw-sidebars-tabs-initialized').tabs('option','active');
				}
			});
			$elements.addClass('fw-sidebars-tabs-initialized');

			setTimeout(function(){
				$elements.fadeTo('fast', 1);
			}, 50);
		}
	},

	/**
	 * Draw and activate button for delete on SIDEBARS
	 */
	initDeleteButtons : function(){
		var dynamicSidebars = PhpVar.dynamicSidebars;
		var html = '<a href="#" title="Delete sidebar" class="fw-ext-sidebars-delete-button dashicons fw-x"></a>' +
					'<span class="fw-ext-sidebars-deleting" style="display: none;"></span>';
		for(i=0;i<dynamicSidebars.length;i++)
		{
			if($('#'+dynamicSidebars[i]).prev('.sidebar-name').length==0)
				$('#'+dynamicSidebars[i]).find('.sidebar-name').find('.sidebar-name-arrow').after(html);
			else
				$('#'+dynamicSidebars[i]).prev('.sidebar-name').find('.sidebar-name-arrow').after(html);
		}

		$('.fw-ext-sidebars-delete-button').each(function(){
			fwSidebars.initQTip($(this));
		});

		$('.fw-ext-sidebars-delete-button').on('click', function(e){
			fwEvents.trigger('fw:sidebars:remove:sidebar:click', { $this: $(this), event: e });
			return false;
		});

	},

	/**
	 *  Delete sidebar ajax
	 */
	removeSidebarAjax : function(data){
		fwSidebars.isBusy = true;
		var $deleteButton = data.$this;
		var $sidebarElem = data.$this.parent().parent();
		var sidebarId = $sidebarElem.attr('id');

		if($sidebarElem.data('blocked')){
			return false;
		}

		$sidebarElem.data('blocked', true);
		$sidebarElem.find('.fw-ext-sidebars-deleting').show();

		var data = {
			action: 'delete_sidebar_ajax',
			sidebar: sidebarId
		}
		$.post(ajaxurl, data, function(response){
			$sidebarElem.data('blocked', false);
			$sidebarElem.find('.fw-ext-sidebars-deleting').hide();
			if (response.success){
				fwEvents.trigger('fw:sidebars:remove:sidebar:success', {sidebarId: sidebarId});
			}else {
				fwEvents.trigger('fw:sidebars:remove:sidebar:error',   {sidebarId: sidebarId, message: response.data.message});
			}
			fwSidebars.isBusy = false;
		}).fail(function(){
			$sidebarElem.data('blocked', false);
			$sidebarElem.find('.fw-ext-sidebars-deleting').hide();
			fwSidebars.isBusy = false;
		});
		return false;
	},

	removeSidebar : function(sidebarId) {
		//remove sidebar container with content
		var $sidebarContainer = $('#widgets-right').find('#' + sidebarId).parent();
		$sidebarContainer.remove();

		//remove sidebar from hidden list (widgets-chooser)
		$('#wpbody-content .widgets-chooser .widgets-chooser-sidebars li').each(function(e){
			if ($(this).data('sidebarId') === sidebarId ){
				$(this).remove();
			}
		});
	},

	/**
	 *  Add item to specific page
	 */
	addRemovableItem : function(id, value, slug, name) {
		$('.sidebars-specific-pages').append("<div><a id='#' class='fw-sidebars-remove-page dashicons fw-x' data-slug='"+slug+"' data-id='" + id + "'  ></a>&nbsp;" + name + ' - ' + value + "</div>");
	},

	/**
	 * Refresh location position on current active tab
	 */
	showPositions : function(){
		var $currentTab = fwSidebars.getCurrentTab();
		var possibleColors = $currentTab.find('.fw-ext-sidebars-positions select option:selected').data('extra-data');
		$currentTab.find('.placeholders')
					.addClass('empty')
					.find('.fw-ext-sidebars-location')
					.addClass('empty');

		if (possibleColors && possibleColors.colors) {
				$currentTab.find('.placeholders')
							.removeClass('empty')
							.find('.fw-ext-sidebars-location')
							.slice(0, possibleColors.colors)
							.removeClass('empty');
		}
	},

	/**
	 * Create new sidebar ajax
	 */
	createNewSidebarAjax : function(sidebarName, $currentSelectize){
		if (!sidebarName) {
			alert(PhpVar.missingSidebarName);
			return false;
		}

		var data = {
			action:'add_new_sidebar_ajax',
			name: sidebarName
		};

		var $spinner = $("[aria-selected='true']").find('.spinner');
		$spinner.show();
		$.post(ajaxurl, data, function(response) {
			$spinner.hide();
			if (response.success) {
				fwEvents.trigger('fw:sidebars:create:sidebar:success', {sidebar: response.data.sidebar, $currentSelectize: $currentSelectize});
			} else {
				alert(response.data.message);
			}
		}).fail(function(){
			$spinner.hide();
		});
		return false;
	},

	/**
	 * Return current selected tab object
	 */
	getCurrentTab : function(){
			var currentActiveTab = $('.fw-sidebars-tabs-wrapper.fw-sidebars-tabs-initialized').tabs('option', 'active');
			return fwSidebars.getSidebarTab(currentActiveTab);
	},

	/**
	 *  Return tab object by index
	 */
	getSidebarTab : function(index){
		return $('.fw-sidebars-tabs-wrapper.fw-sidebars-tabs-initialized').find('.ui-tabs-panel').eq(index).find('.fw-ext-sidebars-box-holder');
	},

	/**
	 * Reset 'preset' settings at current tab
	 */
	resetSettings : function(){
			var $currentTab = fwSidebars.getCurrentTab();
			$currentTab.data('preset-id', '');
			//remove specific pages ids
			$currentTab.find('.sidebars-specific-pages div').remove();

			$currentTab.find('select.sidebar-selectize').each(function(){
				$(this).data('selectize').setDefaultValue();
			});

			var defaultValue = $currentTab.find('.fw-ext-sidebars-positions select option:nth(0)').val();
			fwSidebars.changeImagePickerVal($currentTab, defaultValue);
	},

	/**
	 *  Generate removable item on created tab
	 */
	renderCreatedTabPreset : function(slug, preset, label, description){
		var html = '<div class="fw-ext-sidebars-created-tab-preset" data-type="' + slug + '" data-preset-id="'+preset +'" >' +
						'<span class="fw-ext-sidebars-preset-edit-span">' +
							'<span class="spinner fw-ext-sidebars-preset-editing" style="display: none;"></span>' +
							'<a href="#" class="fw-ext-sidebars-preset-edit" >' + label + '</a>' +
							'<span class="fw-ext-sidebars-desc">&nbsp;' + description + '</span>' +
						'</span>' +
						'<span class="fw-ext-sidebars-preset-remove-span">' +
							'<a href="#" class="fw-ext-sidebars-preset-remove dashicons fw-x"></a>' +
						'</span>' +
						'<span class="spinner fw-ext-sidebars-preset-removing" style="display: none;"></span>'+
					'</div>'

		$presetList = $('.fw-ext-sidebars-preset-list');

		var $replaceItem = $presetList.find('div[data-type="' + slug + '"][data-preset-id="' + preset + '"]');
		if ($replaceItem.length){
			$replaceItem.data('type', slug)
						.data('preset-id', preset)
						.find('.fw-ext-sidebars-preset-edit')
						.text(label);
			//move $replaceItem down to list
			$cloneItem = $replaceItem.clone(true, true);
			$replaceItem.remove();
			$presetList.append($cloneItem);
			$replaceItem = $cloneItem;
		}else{
			$presetList.append(html);
			//find just created object
			$replaceItem = $presetList.find('div[data-type="' + slug + '"][data-preset-id="'+preset +'"]');
		}

		return $replaceItem;
	},

	/**
	 *  Init error popup messages
	 */
	initQTip : function($elem) {
		$elem.qtip({
			id: 'r'+ Math.random(),
			position: {
				at: 'top center',
				my: 'bottom center',
				viewport: $(document.body)
			},
			style: {
				classes: 'qtip-fw qtip-fw-info-sidebars',
				tip: {
					width: 12,
					height: 5
				}
			},
			show: {
				event: 'show_tip'
			},
			hide: {
				event: 'hide_tip'
			}
		});
	},

	/**
	 * Show popup message on $elem with text
	 */
	showTip : function($elem, text){
		$elem.attr('title', text);
		$elem.trigger('show_tip');
		setTimeout(function(){
			$elem.trigger('hide_tip');
		},5000)
	},

	selectizeInit : function(){
		fwEvents.on('fw:sidebars:create:sidebar:success', function(data){
			$('select.sidebar-selectize').each(function(){
				$(this).data('selectize').addOption({value: data.sidebar.id, text: data.sidebar.name});
			});

			if (data.$currentSelectize) {
				data.$currentSelectize.setValue(data.sidebar.id);
			}
		});

		fwEvents.on('fw:sidebars:selectize:input', function(data){
			$('#'+data.event.target.id).focus();
		});

		fwEvents.on('fw:sidebars:selectize:new-sidebar-submit', function(data){
			var name = $('#fw-ext-sidebars-new-sidebar-name').val();
			//hide selectize dropdown before submit
			data.selectize.onBlur.apply(data.selectize, arguments);
			fwSidebars.createNewSidebarAjax(name, data.selectize);
		})

		fwEvents.on('fw:sidebars:remove:sidebar:success', function(data){
			if (!data.sidebarId.length) return false;

			$('select.sidebar-selectize').each(function(){
				$(this).data('selectize').removeOption(data.sidebarId);
				$(this).data('selectize').setDefaultValue();
			});

		});

		Selectize.define('fw_sidebars_selectize_plugin', function(options) {
				var self = this;
				this.setup = (function() {
					var original = self.setup;
					return function() {
						original.apply(this, arguments);
						var eventNS   = self.eventNS;
						this.$control_input.unbind('blur');
						$(document).unbind('mousedown' + eventNS );
						$(document).on('mousedown' + eventNS, function(e) {
							if (self.isFocused) {

								if (e.target.id === 'fw-ext-sidebars-new-sidebar-name' || e.target.id === 'fw-ext-sidebars-new-sidebar-label'){
									fwEvents.trigger('fw:sidebars:selectize:input',{event: e, selectize: self});
									return false;
								}

								if ( e.target.id === 'fw-ext-sidebars-new-sidebar-submit') {
									fwEvents.trigger('fw:sidebars:selectize:new-sidebar-submit',{event: e, selectize: self});
									return false;
								}

								if (e.target === self.$dropdown[0] || e.target.parentNode === self.$dropdown[0]) {
									return false;
								}

								if (!self.$control.has(e.target).length && e.target !== self.$control[0]) {
									self.onBlur.apply(self, arguments);
								}
							}
						});

					};
				})();
				//method set default value
				this.setDefaultValue = function() {
					var $option = null;
					var options = this.options;

					for (var i in options) {
						if (options.hasOwnProperty(i)) {
							$option = options[i];
							break;
						}
					}

					if ($option){
						this.setValue($option.value);
					} else {
						this.setValue('');
					}
				}

			});

		$('.sidebar-selectize').selectize({
			plugins: ['fw_sidebars_selectize_plugin'],
			render: {
				option: function(item){
					return '<div class="selectize-item">' + item.text + '</div>';
				}
			},

			onDropdownOpen: function($dropdown){
				var self = this;
				var html =
							'<div id="fw-ext-sidebars-new-sidebar-container">' +
							'<div id="fw-ext-sidebars-new-sidebar-label">'+ PhpVar.newSidebarLabel +'</div>' +
							'<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>'+
								'<td style="padding-right: 5px">'+
									'<input type="text" id="fw-ext-sidebars-new-sidebar-name" class="fw-option" placeholder="' + PhpVar.newSidebarPlaceholder + '">'+
								'</td>'+
								'<td width="10">'+
									'<input type="button" class="button" value="' + PhpVar.addSidebarButtonTxt + '" id="fw-ext-sidebars-new-sidebar-submit">'+
								'</td>'+
							'</tr></table>'+
							'</div>';
				$dropdown.append(html);

				$('#fw-ext-sidebars-new-sidebar-name').on('keyup', function(event){
					if(event.keyCode == 13){
						fwEvents.trigger('fw:sidebars:selectize:new-sidebar-submit',{event: event, selectize: self});
					}
				});

				var selectOffset = parseInt($dropdown.closest('.selectize-control').offset().top - $(document).scrollTop(), 10),
					selectHeight = $dropdown.closest('.selectize-control').height(),
					dropdownHeight = $dropdown.height();

				if(selectOffset + selectHeight + dropdownHeight > $(window).height()) {
					$dropdown.addClass('dropdown-top');
				}

				return $dropdown;
			},

			onDropdownClose: function($dropdown){
				$dropdown.find('#fw-ext-sidebars-new-sidebar-container').remove();
				$dropdown.removeClass('dropdown-top');
				return $dropdown;
			},

			onChange: function(values) {
				$(this.$control).removeClass('fw-ext-sidebars-error');
			}
		});

	},

	onSidebarCreate : function(sidebar){
		var id = sidebar.id;
		var name = sidebar.name;
		var $col1 = $('.sidebars-column-1');
		var $col2 = $('.sidebars-column-2');

		/**
		 * Empty sidebar html on widgets.php page
		 */
		var html =
			'<div class="widgets-holder-wrap closed">' +
			'<div id="' + sidebar.id + '" class="widgets-sortables">' +
			'<div class="sidebar-name">' +
			'<div class="sidebar-name-arrow"><br /></div>' +
			'<a href="#" title="Delete sidebar" class="fw-ext-sidebars-delete-button dashicons fw-x"></a>' +
			'<span class="fw-ext-sidebars-deleting" style="display: none;"></span>' +
			'<h3>' + sidebar.name + ' <span class="spinner"></span></h3>' +
			'</div>' +
			'<div class="sidebar-description"></div></div>' +
			'</div>';

		var $newElement = $(html);

		if ($col2.length) {
				if($col1.find('.widgets-holder-wrap').length > $col2.find('.widgets-holder-wrap').length ) {
					$col2.append($newElement);
				}else{
					$col1.append($newElement);
				}
		} else {
			$col1.append($newElement);
		}

		//FROM: /wp-admin/js/widgets.js
		{
				$newElement.find('.widgets-sortables').sortable({
				placeholder: 'widget-placeholder',
				items: '> .widget',
				handle: '> .widget-top > .widget-title',
				cursor: 'move',
				distance: 2,
				containment: 'document',
				start: function( event, ui ) {
					var height, $this = $(this),
						$wrap = $this.parent(),
						inside = ui.item.children('.widget-inside');

					if ( inside.css('display') === 'block' ) {
						inside.hide();
						$(this).sortable('refreshPositions');
					}

					if ( ! $wrap.hasClass('closed') ) {
						// Lock all open sidebars min-height when starting to drag.
						// Prevents jumping when dragging a widget from an open sidebar to a closed sidebar below.
						height = ui.item.hasClass('ui-draggable') ? $this.height() : 1 + $this.height();
						$this.css( 'min-height', height + 'px' );
					}
				},

				stop: function( event, ui ) {
					var addNew, widgetNumber, $sidebar, $children, child, item,
						$widget = ui.item,
						id = fwSidebars.the_widget_id;

					if ( $widget.hasClass('deleting') ) {
						wpWidgets.save( $widget, 1, 0, 1 ); // delete widget
						$widget.remove();
						return;
					}

					var addNew = $widget.find('input.add_new').val();
					var widgetNumber = $widget.find('input.multi_number').val();

					$widget.attr( 'style', '' ).removeClass('ui-draggable');
					fwSidebars.the_widget_id = '';

					if ( addNew ) {
						if ( 'multi' === addNew ) {
							$widget.html(
								$widget.html().replace( /<[^<>]+>/g, function( tag ) {
									return tag.replace( /__i__|%i%/g, widgetNumber );
								})
							);

							$widget.attr( 'id', id.replace( '__i__', widgetNumber ) );
							widgetNumber++;

							$( 'div#' + id ).find( 'input.multi_number' ).val( widgetNumber );
						} else if ( 'single' === addNew ) {
							$widget.attr( 'id', 'new-' + id );
							rem = 'div#' + id;
						}

						wpWidgets.save( $widget, 0, 0, 1 );
						$widget.find('input.add_new').val('');
					}

					$sidebar = $widget.parent();

					if ( $sidebar.parent().hasClass('closed') ) {
						$sidebar.parent().removeClass('closed');
						$children = $sidebar.children('.widget');

						// Make sure the dropped widget is at the top
						if ( $children.length > 1 ) {
							child = $children.get(0);
							item = $widget.get(0);

							if ( child.id && item.id && child.id !== item.id ) {
								$( child ).before( $widget );
							}
						}
					}

					if ( addNew ) {
						$widget.find( 'a.widget-action' ).trigger('click');
					} else {
						wpWidgets.saveOrder( $sidebar.attr('id') );
					}
				},

				activate: function() {
					$(this).parent().addClass( 'widget-hover' );
				},

				deactivate: function() {
					// Remove all min-height added on "start"
					$(this).css( 'min-height', '' ).parent().removeClass( 'widget-hover' );
				},

				receive: function( event, ui ) {
					var $sender = $( ui.sender );

					// Don't add more widgets to orphaned sidebars
					if ( this.id.indexOf('orphaned_widgets') > -1 ) {
						$sender.sortable('cancel');
						return;
					}

					// If the last widget was moved out of an orphaned sidebar, close and remove it.
					if ( $sender.attr('id').indexOf('orphaned_widgets') > -1 && ! $sender.children('.widget').length ) {
						$sender.parents('.orphan-sidebar').slideUp( 400, function(){ $(this).remove(); } );
					}
				}
			}).sortable( 'option', 'connectWith', 'div.widgets-sortables' );
			}

		$widgetChooser = $('#wpbody-content').find('.widgets-chooser');
		if($widgetChooser.find())
		$widgetChooser.find('.widgets-chooser-sidebars').append($('<li tabindex="0" class="widgets-chooser-selected">'+name+'</li>').data('sidebarId',sidebar.id));

		$newElement.find('.sidebar-name').on('click', function(event){
		$newElement.toggleClass('closed');
	});

		fwSidebars.initQTip($newElement.find('.fw-ext-sidebars-delete-button'));

		$newElement.find('.fw-ext-sidebars-delete-button').on('click', function(e){
		fwEvents.trigger('fw:sidebars:remove:sidebar:click', { $this: $(this), event: e });
		return false;
	});

		}

	};

	$(document).ready(function() {
		fwSidebars.init();
	});

})(jQuery);