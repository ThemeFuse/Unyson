/**
 * Included on pages where backend options are rendered
 */

var fwBackendOptions = {
	/**
	 * @deprecated Tabs are lazy loaded https://github.com/ThemeFuse/Unyson/issues/1174
	 */
	openTab: function(tabId) { console.warn('deprecated'); }
};

jQuery(document).ready(function($){
	var localized = _fw_backend_options_localized;

	/**
	 * Functions
	 */
	{
		/**
		 * Make fw-postbox to close/open on click
		 *
		 * (fork from /wp-admin/js/postbox.js)
		 */
		function addPostboxToggles($boxes) {
			/** Remove events added by /wp-admin/js/postbox.js */
			$boxes.find('h2, h3, .handlediv').off('click.postboxes');

			var eventNamespace = '.fw-backend-postboxes';

			// make postboxes to close/open on click
			$boxes
				.off('click'+ eventNamespace) // remove already attached, just to be sure, prevent multiple execution
				.on('click'+ eventNamespace, '> .hndle, > .handlediv', function(e){
					var $box = $(this).closest('.fw-postbox');

					if ($box.parent().is('.fw-backend-postboxes') && !$box.siblings().length) {
						// Do not close if only one box https://github.com/ThemeFuse/Unyson/issues/1094
						$box.removeClass('closed');
					} else {
						$box.toggleClass('closed');
					}

					var isClosed = $box.hasClass('closed');

					$box.trigger('fw:box:'+ (isClosed ? 'close' : 'open'));
					$box.trigger('fw:box:toggle-closed', {isClosed: isClosed});
				});
		}

		/** Remove box header if title is empty */
		function hideBoxEmptyTitles($boxes) {
			$boxes.find('> .hndle > span').each(function(){
				var $this = $(this);

				if (!$.trim($this.html()).length) {
					$this.closest('.postbox').addClass('fw-postbox-without-name');
				}
			});
		}
	}

	/** Init tabs */
	(function(){
		var htmlAttrName = 'data-fw-tab-html',
			initTab = function($tab) {
				var html;

				if (html = $tab.attr(htmlAttrName)) {
					fwEvents.trigger('fw:options:init', {
						$elements: $tab.removeAttr(htmlAttrName).html(html),
						/**
						 * Sometimes we want to perform some action just when
						 * lazy tabs are rendered. It's important in those cases
						 * to distinguish regular fw:options:init events from
						 * the ones that will render tabs. Passing by this little
						 * detail may break some widgets because fw:options:init
						 * event may be fired even when tabs are not yet rendered.
						 *
						 * That's how you can be sure that you'll run a piece
						 * of code just when tabs will be arround 100%.
						 *
						 * fwEvents.on('fw:options:init', function (data) {
						 *   if (! data.lazyTabsUpdated) {
						 *     return;
						 *   }
						 *
						 *   // Do your business
						 * });
						 *
						 */
						lazyTabsUpdated: true
					});
				}
			},
			initAllTabs = function ($el) {
				var selector = '.fw-options-tab[' + htmlAttrName + ']', $tabs;

				// fixes https://github.com/ThemeFuse/Unyson/issues/1634
				$el.each(function(){
					if ($(this).is(selector)) {
						initTab($(this));
					}
				});

				// initialized tabs can contain tabs, so init recursive until nothing is found
				while (($tabs = $el.find(selector)).length) {
					$tabs.each(function(){ initTab($(this)); });
				}
			};

		fwEvents.on('fw:options:init:tabs', function (data) {
			initAllTabs(data.$elements);
		});

		fwEvents.on('fw:options:init', function (data) {
			var $tabs = data.$elements.find('.fw-options-tabs-wrapper:not(.initialized)');

			if (localized.lazy_tabs) {
				$tabs.tabs({
					create: function (event, ui) {
						initTab(ui.panel);
					},
					activate: function (event, ui) {
						initTab(ui.newPanel);
					}
				});

				$tabs
					.closest('form')
					.off('submit.fw-tabs')
					.on('submit.fw-tabs', function () {
						if (!$(this).hasClass('prevent-all-tabs-init')) {
							// All options needs to be present in html to be sent in POST on submit
							initAllTabs($(this));
						}
					});
			} else {
				$tabs.tabs();
			}

			$tabs.each(function () {
				var $this = $(this);

				if (!$this.parent().closest('.fw-options-tabs-wrapper').length) {
					// add special class to first level tabs
					$this.addClass('fw-options-tabs-first-level');
				}
			});

			$tabs.addClass('initialized');
		});
	})();

	/** Init boxes */
	fwEvents.on('fw:options:init', function (data) {
		var $boxes = data.$elements.find('.fw-postbox:not(.initialized)');

		hideBoxEmptyTitles(
			$boxes.filter('.fw-backend-postboxes > .fw-postbox')
		);

		addPostboxToggles($boxes);

		/**
		 * leave open only first boxes
		 */
		$boxes
			.filter('.fw-backend-postboxes > .fw-postbox:not(.fw-postbox-without-name):not(:first-child):not(.prevent-auto-close)')
			.addClass('closed');

		$boxes.addClass('initialized');

		// trigger on box custom event for others to do something after box initialized
		$boxes.trigger('fw-options-box:initialized');
	});

	/** Init options */
	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-backend-option:not(.initialized)')
			// do nothing, just a the initialized class to make the fadeIn css animation effect
			.addClass('initialized');
	});

	/** Fixes */
	fwEvents.on('fw:options:init', function (data) {
		{
			var eventNamespace = '.fw-backend-postboxes';

			data.$elements.find('.postbox:not(.fw-postbox) .fw-option')
				.closest('.postbox:not(.fw-postbox)')

				/**
				 * Add special class to first level postboxes that contains framework options (on post edit page)
				 */
				.addClass('postbox-with-fw-options')

				/**
				 * Prevent event to be propagated to first level WordPress sortable (on edit post page)
				 * If not prevented, boxes within options can be dragged out of parent box to first level boxes
				 */
				.off('mousedown'+ eventNamespace) // remove already attached (happens when this script is executed multiple times on the same elements)
				.on('mousedown'+ eventNamespace, '.fw-postbox > .hndle, .fw-postbox > .handlediv', function(e){
					e.stopPropagation();
				});
		}

		/**
		 * disable sortable (drag/drop) for postboxes created by framework options
		 * (have no sense, the order is not saved like for first level boxes on edit post page)
		 */
		{
			var $sortables = data.$elements
				.find('.postbox:not(.fw-postbox) .fw-postbox, .fw-options-tabs-wrapper .fw-postbox')
				.closest('.fw-backend-postboxes')
				.not('.fw-sortable-disabled');

			$sortables.each(function(){
				try {
					$(this).sortable('destroy');
				} catch (e) {
					// happens when not initialized
				}
			});

			$sortables.addClass('fw-sortable-disabled');
		}

		/** hide bottom border from last option inside box */
		{
			data.$elements.find('.postbox-with-fw-options > .inside, .fw-postbox > .inside')
				.append('<div class="fw-backend-options-last-border-hider"></div>');
		}

		hideBoxEmptyTitles(
			data.$elements.find('.postbox-with-fw-options')
		);
	});

	/**
	 * Help tips (i)
	 */
	(function(){
		fwEvents.on('fw:options:init', function (data) {
			var $helps = data.$elements.find('.fw-option-help:not(.initialized)');

			fw.qtip($helps);

			$helps.addClass('initialized');
		});
	})();

	$('#side-sortables').addClass('fw-force-xs');
});
