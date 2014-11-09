/**
 * Included on pages where backend options are rendered
 */

jQuery(document).ready(function($){
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
			$boxes.find('h3, .handlediv').off('click.postboxes');

			var eventNamespace = '.fw-postboxes';

			// make postboxes to close/open on click
			$boxes
				.off('click'+ eventNamespace) // remove already attached, just to be sure, prevent multiple execution
				.on('click'+ eventNamespace, '> h3.hndle, > .handlediv', function(e){
					$(this).closest('.fw-postbox').toggleClass('closed');
				});

			/**
			 * Prevent event to be propagated to first level WordPress sortable (on edit post page)
			 * If not prevented, boxes within options can be dragged out of parent box to first level boxes
			 */
			$boxes.closest('.postbox-with-fw-options')
				.off('mousedown'+ eventNamespace) // remove already attached, just to be sure, prevent multiple execution
				.on('mousedown'+ eventNamespace, '.fw-postbox > h3.hndle, .fw-postbox > .handlediv', function(e){
					e.stopPropagation();
				});
		}

		/** Remove box header if title is empty */
		function hideBoxEmptyTitles($boxes) {
			$boxes.find('> h3.hndle > span').each(function(){
				var $this = $(this);
				var name  = $.trim($this.text());

				if (!name.length) {
					$this.closest('.postbox').addClass('fw-postbox-without-name');
				} else if (name == '&nbsp;') {
					// developer tried to set &nbsp; but htmlspecialchars made it as text
					// make it as html
					$this.html('&nbsp;');
				}
			});
		}
	}

	/** Init tabs */
	fwEvents.on('fw:options:init', function (data) {
		var $elements = data.$elements.find('.fw-options-tabs-wrapper:not(.fw-option-initialized)');

		if ($elements.length) {
			$elements.tabs();
			$elements.addClass('fw-option-initialized');

			$elements.each(function(){
				var $this = $(this);

				if (!$this.parent().closest('.fw-options-tabs-wrapper').length) {
					// add special class to first level tabs
					$this.addClass('fw-options-tabs-first-level');
				}
			});

			setTimeout(function(){
				$elements.fadeTo('fast', 1);
			}, 50);
		}
	});

	/** Init boxes */
	fwEvents.on('fw:options:init', function (data) {
		var $boxes = data.$elements.find('.fw-postbox:not(.fw-postbox-initialized)');

		hideBoxEmptyTitles($boxes);

		setTimeout(function(){
			addPostboxToggles($boxes);
		}, 100);

		/**
		 * leave open only first boxes
		 */
		data.$elements.find('.fw-postboxes > .fw-postbox:not(:first-child)').addClass('closed');

		$boxes.addClass('fw-postbox-initialized');

		setTimeout(function(){
			// trigger on box custom event for others to do something after box initialized
			$boxes.trigger('fw-options-box:initialized');
		}, 100);
	});

	/** Fixes */
	fwEvents.on('fw:options:init', function (data) {
		/** add special class to first level postboxes that contains framework options */
		{
			data.$elements.find('.postbox:not(.fw-postbox) .fw-option')
				.closest('.postbox:not(.fw-postbox)')
				.addClass('postbox-with-fw-options');
		}

		/**
		 * disable sortable (drag/drop) for postboxes created by framework options
		 * (have no sense, the order is not saved like for first level boxes on edit post page)
		 */
		{
			var $sortables = data.$elements
				.find('.postbox:not(.fw-postbox) .fw-postbox, .fw-options-tabs-wrapper .fw-postbox')
				.closest('.fw-postboxes')
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

	setTimeout(function(){
		hideBoxEmptyTitles($('.postbox-with-fw-options'));
	}, 55);

	$('#side-sortables').addClass('fw-force-xs');
});