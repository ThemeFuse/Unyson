var fw;

if (typeof Object['create'] != 'undefined') {
	/**
	 * create clean object
	 * autocomplete in console to show only defined methods, without other unnecesary methods from Object prototype
	 */
	fw = Object.create(null);
} else {
	fw = {};
}

/**
 * URI to framework directory
 */
fw.FW_URI = _fw_localized.FW_URI;

fw.SITE_URI = _fw_localized.SITE_URI;

/**
 * Useful images
 */
fw.img = {
	loadingSpinner: fw.SITE_URI + '/wp-admin/images/spinner.gif'
};

/**
 * parseInt() alternative
 * Like intval() in php. Returns 0 on failure, not NaN
 * @param val
 */
fw.intval = function(val)
{
	val = parseInt(val);

	return !isNaN(val) ? val : 0;
};

/**
 * Calculate md5 hash of the string
 * @param {String} string
 */
fw.md5 = (function(){
	"use strict";

	/*
	 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
	 * Digest Algorithm, as defined in RFC 1321.
	 * Copyright (C) Paul Johnston 1999 - 2000.
	 * Updated by Greg Holt 2000 - 2001.
	 * See http://pajhome.org.uk/site/legal.html for details.
	 */

	/*
	 * Convert a 32-bit number to a hex string with ls-byte first
	 */
	var hex_chr = "0123456789abcdef";
	function rhex(num)
	{
		var str = "", j;
		for(j = 0; j <= 3; j++)
			str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) +
			hex_chr.charAt((num >> (j * 8)) & 0x0F);
		return str;
	}

	/*
	 * Convert a string to a sequence of 16-word blocks, stored as an array.
	 * Append padding bits and the length, as described in the MD5 standard.
	 */
	function str2blks_MD5(str)
	{
		var nblk = ((str.length + 8) >> 6) + 1,
			blks = new Array(nblk * 16),
			i;
		for(i = 0; i < nblk * 16; i++) blks[i] = 0;
		for(i = 0; i < str.length; i++)
			blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
		blks[i >> 2] |= 0x80 << ((i % 4) * 8);
		blks[nblk * 16 - 2] = str.length * 8;
		return blks;
	}

	/*
	 * Add integers, wrapping at 2^32. This uses 16-bit operations internally
	 * to work around bugs in some JS interpreters.
	 */
	function add(x, y)
	{
		var lsw = (x & 0xFFFF) + (y & 0xFFFF);
		var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
		return (msw << 16) | (lsw & 0xFFFF);
	}

	/*
	 * Bitwise rotate a 32-bit number to the left
	 */
	function rol(num, cnt)
	{
		return (num << cnt) | (num >>> (32 - cnt));
	}

	/*
	 * These functions implement the basic operation for each round of the
	 * algorithm.
	 */
	function cmn(q, a, b, x, s, t)
	{
		return add(rol(add(add(a, q), add(x, t)), s), b);
	}
	function ff(a, b, c, d, x, s, t)
	{
		return cmn((b & c) | ((~b) & d), a, b, x, s, t);
	}
	function gg(a, b, c, d, x, s, t)
	{
		return cmn((b & d) | (c & (~d)), a, b, x, s, t);
	}
	function hh(a, b, c, d, x, s, t)
	{
		return cmn(b ^ c ^ d, a, b, x, s, t);
	}
	function ii(a, b, c, d, x, s, t)
	{
		return cmn(c ^ (b | (~d)), a, b, x, s, t);
	}

	/*
	 * Take a string and return the hex representation of its MD5.
	 */
	function calcMD5(str)
	{
		var x = str2blks_MD5(str);
		var a =  1732584193;
		var b = -271733879;
		var c = -1732584194;
		var d =  271733878;

		var i, olda, oldb, oldc, oldd;

		for(i = 0; i < x.length; i += 16)
		{
			olda = a;
			oldb = b;
			oldc = c;
			oldd = d;

			a = ff(a, b, c, d, x[i+ 0], 7 , -680876936);
			d = ff(d, a, b, c, x[i+ 1], 12, -389564586);
			c = ff(c, d, a, b, x[i+ 2], 17,  606105819);
			b = ff(b, c, d, a, x[i+ 3], 22, -1044525330);
			a = ff(a, b, c, d, x[i+ 4], 7 , -176418897);
			d = ff(d, a, b, c, x[i+ 5], 12,  1200080426);
			c = ff(c, d, a, b, x[i+ 6], 17, -1473231341);
			b = ff(b, c, d, a, x[i+ 7], 22, -45705983);
			a = ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
			d = ff(d, a, b, c, x[i+ 9], 12, -1958414417);
			c = ff(c, d, a, b, x[i+10], 17, -42063);
			b = ff(b, c, d, a, x[i+11], 22, -1990404162);
			a = ff(a, b, c, d, x[i+12], 7 ,  1804603682);
			d = ff(d, a, b, c, x[i+13], 12, -40341101);
			c = ff(c, d, a, b, x[i+14], 17, -1502002290);
			b = ff(b, c, d, a, x[i+15], 22,  1236535329);

			a = gg(a, b, c, d, x[i+ 1], 5 , -165796510);
			d = gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
			c = gg(c, d, a, b, x[i+11], 14,  643717713);
			b = gg(b, c, d, a, x[i+ 0], 20, -373897302);
			a = gg(a, b, c, d, x[i+ 5], 5 , -701558691);
			d = gg(d, a, b, c, x[i+10], 9 ,  38016083);
			c = gg(c, d, a, b, x[i+15], 14, -660478335);
			b = gg(b, c, d, a, x[i+ 4], 20, -405537848);
			a = gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
			d = gg(d, a, b, c, x[i+14], 9 , -1019803690);
			c = gg(c, d, a, b, x[i+ 3], 14, -187363961);
			b = gg(b, c, d, a, x[i+ 8], 20,  1163531501);
			a = gg(a, b, c, d, x[i+13], 5 , -1444681467);
			d = gg(d, a, b, c, x[i+ 2], 9 , -51403784);
			c = gg(c, d, a, b, x[i+ 7], 14,  1735328473);
			b = gg(b, c, d, a, x[i+12], 20, -1926607734);

			a = hh(a, b, c, d, x[i+ 5], 4 , -378558);
			d = hh(d, a, b, c, x[i+ 8], 11, -2022574463);
			c = hh(c, d, a, b, x[i+11], 16,  1839030562);
			b = hh(b, c, d, a, x[i+14], 23, -35309556);
			a = hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
			d = hh(d, a, b, c, x[i+ 4], 11,  1272893353);
			c = hh(c, d, a, b, x[i+ 7], 16, -155497632);
			b = hh(b, c, d, a, x[i+10], 23, -1094730640);
			a = hh(a, b, c, d, x[i+13], 4 ,  681279174);
			d = hh(d, a, b, c, x[i+ 0], 11, -358537222);
			c = hh(c, d, a, b, x[i+ 3], 16, -722521979);
			b = hh(b, c, d, a, x[i+ 6], 23,  76029189);
			a = hh(a, b, c, d, x[i+ 9], 4 , -640364487);
			d = hh(d, a, b, c, x[i+12], 11, -421815835);
			c = hh(c, d, a, b, x[i+15], 16,  530742520);
			b = hh(b, c, d, a, x[i+ 2], 23, -995338651);

			a = ii(a, b, c, d, x[i+ 0], 6 , -198630844);
			d = ii(d, a, b, c, x[i+ 7], 10,  1126891415);
			c = ii(c, d, a, b, x[i+14], 15, -1416354905);
			b = ii(b, c, d, a, x[i+ 5], 21, -57434055);
			a = ii(a, b, c, d, x[i+12], 6 ,  1700485571);
			d = ii(d, a, b, c, x[i+ 3], 10, -1894986606);
			c = ii(c, d, a, b, x[i+10], 15, -1051523);
			b = ii(b, c, d, a, x[i+ 1], 21, -2054922799);
			a = ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
			d = ii(d, a, b, c, x[i+15], 10, -30611744);
			c = ii(c, d, a, b, x[i+ 6], 15, -1560198380);
			b = ii(b, c, d, a, x[i+13], 21,  1309151649);
			a = ii(a, b, c, d, x[i+ 4], 6 , -145523070);
			d = ii(d, a, b, c, x[i+11], 10, -1120210379);
			c = ii(c, d, a, b, x[i+ 2], 15,  718787259);
			b = ii(b, c, d, a, x[i+ 9], 21, -343485551);

			a = add(a, olda);
			b = add(b, oldb);
			c = add(c, oldc);
			d = add(d, oldd);
		}
		return rhex(a) + rhex(b) + rhex(c) + rhex(d);
	}

	return calcMD5;
})();

/**
 * Show/Hide loading on page
 */
fw.loading = new (function()
{
	var $ = jQuery;

	/** DOM element */
	var $loading = $('<div></div>')

	/** Current state */
	var isLoading = false;

	/** Prevent infinite loading if some error */
	var loadingTimeoutId = 0;

	/** After that time, loading will hide automaticaly */
	var loadingTimeout = 30 * 1000;

	/**
	 * How many times show() was called
	 * This prevent this situation: 2 sripts called show(), first finishes execution and calls hide(),
	 *  but the loading needs to remain until the second script will tell it to hide()
	 */
	var loadingStackSize = 0;

	var setAutoHideTimeout = function()
	{
		clearTimeout(loadingTimeoutId);

		loadingTimeoutId = setTimeout(function(){
			console.log('[Warning] Loading timeout. Auto hidding. Probably happend an error and hide cannot be done.');

			that.hide();
		}, loadingTimeout);
	};

	/** Public Methods */
	{
		this.show = function()
		{
			if (isLoading) {
				loadingStackSize++;
				return;
			}

			setAutoHideTimeout();

			$loading.stop(true);
			$loading.fadeIn();

			loadingStackSize++;

			isLoading = true;
		};

		this.hide = function()
		{
			if (!isLoading) {
				return;
			}

			loadingStackSize--;

			if (loadingStackSize < 0) {
				loadingStackSize = 0;
			}

			if (loadingStackSize == 0) {
				clearTimeout(loadingTimeoutId);

				$loading.stop(true);
				$loading.fadeOut('fast');

				isLoading = false;
			} else {
				setAutoHideTimeout();
			}
		};
	}

	var that = this;

	/** Init */
	{
		$loading.css({
			'position': 'fixed',
			'top': '0',
			'left': '0',
			'height': '100%',
			'width': '100%',
			'z-index': '9999999',
			'display': 'none',
			'background-image': 'url('+ fw.img.loadingSpinner +')',
			'background-repeat': 'no-repeat',
			'background-position': 'center center'
		});

		$(document).ready(function(){
			$(document.body).prepend($loading);
		});
	}
})();

/**
 * Capitalizes the first letter of a string.
 */
fw.capitalizeFirstLetter = function(str) {
	str = str == null ? '' : String(str);
	return str.charAt(0).toUpperCase() + str.slice(1);
};

/**
 * Set nested property value
 *
 * Usage:
 * var obj = {foo: {}};
 * fw.ops('foo/bar', 'ok', obj); // {foo: {bar: 'ok'}}
 *
 * @param {String} properties 'a.b.c'
 * @param {*} value
 * @param {Object} obj
 * @param {String} [delimiter] Default '/'
 */
fw.ops = function(properties, value, obj, delimiter) {
	delimiter = delimiter || '/';

	if (typeof properties == 'string') {
		properties = properties.split(delimiter);
	} else {
		properties = [properties];
	}

	var property = properties.shift();

	if (properties.length) {
		properties = properties.join(delimiter);

		if (typeof obj[property] == 'undefined') {
			obj[property] = {};
		} else if (typeof obj[property] != 'object') {
			console.warn(
				'[fw.ops] Object property "'+ property +'" already has non object value:', obj[property],
				'It will be replaced with an empty object'
			);

			obj[property] = {};
		} else if (typeof obj[property] == 'object' && typeof obj[property].length != 'undefined') {
			// it's array, check if property is integer
			if ((/^[0-9]+$/).test(property)) {
				property = parseInt(property);
			} else {
				console.warn(
					'[fw.ops] Try to set non numeric property "'+ property +'" to array:', obj[property],
					'The array will be be replaced with an empty object'
				);

				obj[property] = {};
			}
		}

		fw.ops(properties, value, obj[property], delimiter);
	} else {
		obj[property] = value;
	}

	return obj;
};

/**
 * Get nested property value
 *
 * Usage:
 * var obj = {foo: {bar: 'ok'}};
 * fw.opg('foo/bar', obj); // 'ok'
 *
 * @param {String} properties 'a.b.c'
 * @param {Object} obj
 * @param {*} [defaultValue] If property will not exist
 * @param {String} [delimiter] Default '/'
 */
fw.opg = function(properties, obj, defaultValue, delimiter) {
	delimiter = delimiter || '/';

	if (typeof properties == 'string') {
		properties = properties.split(delimiter);
	} else {
		properties = [properties];
	}

	var property = properties.shift();

	if (typeof obj[property] == 'undefined') {
		return defaultValue;
	}

	if (properties.length) {
		properties = properties.join(delimiter);

		return fw.opg(properties, obj[property], defaultValue, delimiter);
	} else {
		return obj[property];
	}
};

/**
 * Unique random md5
 * @returns {String}
 */
fw.randomMD5 = function() {
	return String(
		fw.md5(
			Math.random() +'-'+ Math.random() +'-'+ Math.random()
		)
	);
};

/**
 * Return value from QueryString
 * @param name
 * @returns {string}
 */
fw.getQueryString = function(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		results = regex.exec(location.search);
	return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
};

(function() {

	/*
	 * A stack-like structure to manage chains of modals
	 * (modals that are opened one from another)
	 */
	var modalsStack = {
		_stack: [],
		push: function(modal) {
			this._stack.push(modal);
		},
		pop: function() {
			return this._stack.pop();
		},
		peek: function() {
			return this._stack[this._stack.length - 1];
		},
		getSize: function() {
			return this._stack.length;
		}
	};

	/**
	 * Modal to edit backend options
	 *
	 * Usage:
	 * var modal = new fw.OptionsModal({
	 *  title: 'Custom Title',
	 *  options: [
	 *      {'test1': {
	 *          'type': 'text',
	 *          'label': 'Test1'
	 *      }},
	 *      {'test2': {
	 *          'type': 'textarea',
	 *          'label': 'Test2'
	 *      }}
	 *  ],
	 *  values: {
	 *      'test1': 'Default1',
	 *      'test2': 'Default2'
	 *  }
	 * });
	 *
	 * // listen for values change
	 * modal.on('change:values', function(modal, values) {
	 *  // do something with values
	 * });
	 *
	 * // replace values
	 * modal.set('values', { ... });
	 *
	 * modal.open();
	 */
	fw.OptionsModal = Backbone.Model.extend({
		defaultSize: 'small', // 'large', 'medium', 'small'
		defaults: {
			/** Will be transformed to array with json_decode($options, true) and sent to fw()->backend->render_options() */
			options: [
				{'demo-text': {
					'type': 'text',
					'label': 'Demo Text'
				}},
				{'demo-textarea': {
					'type': 'textarea',
					'label': 'Demo Textarea'
				}}
			],
			/** Values of the options {'option-id': 'option value'} , also used in fw()->backend->render_options() */
			values: {},
			/* Modal title */
			title: 'Edit Options',
			/**
			 * Content html
			 * @private
			 */
			html: ''
		},
		/**
		 * Properties created in .initialize():
		 * - {Backbone.View} contentView
		 * - {wp.media.view.MediaFrame} frame
		 *
		 * @private
		 */
		initialize: function() {
			var modal = this;

			var ContentView = Backbone.View.extend({
				tagName: 'form',
				attributes: {
					'onsubmit': 'return false;'
				},
				render: function() {
					this.$el.html(
						this.model.get('html')
					);

					fwEvents.trigger('fw:options:init', {$elements: this.$el});

					/* options fixes */
					{
						// hide last border
						this.$el.prepend('<div class="fw-backend-options-last-border-hider"></div>');

						// hide last border from tabs
						this.$el.find('.fw-options-tabs-contents > .fw-inner > .fw-options-tab')
							.append('<div class="fw-backend-options-last-border-hider"></div>');
					}
				},
				initialize: function() {
					this.listenTo(this.model, 'change:html', this.render);
				}
			});

			// prepare this.frame
			{
				var ControllerMainState = wp.media.controller.State.extend({
					id: 'main',
					defaults: {
						content: 'main',
						menu: 'default',
						title: this.get('title')
					},
					initialize: function() {
						this.listenTo(modal, 'change:title', function(){
							this.set('title', modal.get('title'));
						});
					}
				});

				this.frame = new wp.media.view.MediaFrame({
					state: 'main',
					states: [ new ControllerMainState ]
				});

				this.frame.once('ready', function(){
					var $modalWrapper = modal.frame.modal.$el,
						$modal        = $modalWrapper.find('.media-modal'),
						$backdrop     = $modalWrapper.find('.media-modal-backdrop'),
						size          = modal.get('size'),
						stackSize     = modalsStack.getSize(),
						$close        = $modalWrapper.find('.media-modal-close');

					$modalWrapper.addClass('fw-modal fw-options-modal');

					if (_.indexOf(['large', 'medium', 'small'], size) !== -1) {
						$modalWrapper.addClass('fw-options-modal-' + size);
					} else {
						$modalWrapper.addClass('fw-options-modal-' + modal.defaultSize);
					}

					if (stackSize) {
						$modal.css({
							border: (stackSize * 30) +'px solid transparent'
						});
					}

					/**
					 * adjust the z-index for the new frame's backdrop and modal
					 * (160000 is what wp sets for its modals)
					 */
					$backdrop.css('z-index', 160000 + (stackSize * 2 + 1));
					$modal.css('z-index',    160000 + (stackSize * 2 + 2));

					// show effect on close
					(function(){
						var eventsNamespace = '.fwOptionsModalCloseEffect';
						var closingTimeout  = 0;

						var closeEffect = function(){
							clearTimeout(closingTimeout);

							// begin css animation
							$modalWrapper.addClass('fw-modal-closing');

							closingTimeout = setTimeout(function(){
								closingTimeout = 0;

								// remove events that prevent original close
								$close.off(eventsNamespace);
								$backdrop.off(eventsNamespace);

								// fire original close process after animation effect finished
								$close.trigger('click');
								$backdrop.trigger('click');

								// remove animation class
								$modalWrapper.removeClass('fw-modal-closing');

								preventOriginalClose();
							},
							300 // css animation duration
							);
						};

						function handleCloseClick(e) {
							e.stopPropagation();
							e.preventDefault();

							if (closingTimeout) {
								// do nothing if currently there is a closing delay/animation in progress
								return;
							}

							closeEffect();
						}

						// add events that prevent original close
						function preventOriginalClose() {
							$close.on('click'+ eventsNamespace, handleCloseClick);
							$backdrop.on('click'+ eventsNamespace, handleCloseClick);
						}

						preventOriginalClose();
					})();
				});

				this.frame.on('open', function() {
					var $modalWrapper = modal.frame.modal.$el;

					$modalWrapper.addClass('fw-modal-open');

					modalsStack.push($modalWrapper.find('.media-modal'));

					// Resize .fw-options-tabs-contents to fit entire window
					{
						modal.on('change:html', modal.resizeTabsContent);

						jQuery(window).on('resize.resizeTabsContent', function () { modal.resizeTabsContent(); });
					}
				});

				this.frame.on('close', function(){
					// Stop tracking modal HTML and window size
					{
						modal.off('change:html', modal.resizeTabsContent);

						jQuery(window).off('resize.resizeTabsContent');
					}

					/**
					 * clear html
					 * to prevent same ids in html when another modal with same options will be opened
					 */
					modal.set('html', '');

					modal.frame.modal.$el.removeClass('fw-modal-open');

					modalsStack.pop();
				});

				this.contentView = new ContentView({
					controller: this.frame,
					model: this
				});

				this.frame.on('content:create:main', function () {
					modal.frame.content.set(
						modal.contentView
					);

					modal.frame.toolbar.set(
						new wp.media.view.Toolbar({
							controller: modal.frame,
							items: [
								{
									style: 'primary',
									text: 'Save',
									priority: 40,
									click: function () {
										fw.loading.show();

										jQuery.ajax({
											url: ajaxurl,
											type: 'POST',
											data: [
												'action=fw_backend_options_get_values',
												'options='+ encodeURIComponent(JSON.stringify(modal.get('options'))),
												'name_prefix=fw_edit_options_modal',
												modal.contentView.$el.serialize()
											].join('&'),
											dataType: 'json',
											success: function (response, status, xhr) {
												fw.loading.hide();

												if (!response.success) {
													/**
													 * do not replace html here
													 * user completed the form with data and wants to submit data
													 * do not delete all his work
													 */
													alert('Error: '+ response.data.message);
													return;
												}

												modal.set('values', response.data.values);

												// simulate click on close button to fire animations
												modal.frame.modal.$el.find('.media-modal-close').trigger('click');
											},
											error: function (xhr, status, error) {
												fw.loading.hide();

												/**
												 * do not replace html here
												 * user completed the form with data and wants to submit data
												 * do not delete all his work
												 */
												alert(status+ ': '+ error.message);
											}
										});
									}
								}
							]
						})
					);
				});
			}
		},
		/**
		 * @param {Object} options used for fw()->backend->render_options(json_decode(options, true))
		 */
		open: function() {
			this.frame.open();

			this.updateHtml();
		},
		updateHtml: function() {
			fw.loading.show();

			this.set('html', '');

			var modal = this;

			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'fw_backend_options_render',
					options: JSON.stringify(this.get('options')),
					values: this.get('values'),
					data: {
						name_prefix: 'fw_edit_options_modal',
						id_prefix: 'fw-edit-options-modal-'
					}
				},
				dataType: 'json',
				success: function (response, status, xhr) {
					fw.loading.hide();

					if (!response.success) {
						modal.set('html', 'Error: '+ response.data.message);
						return;
					}

					modal.set('html', response.data.html);
				},
				error: function (xhr, status, error) {
					fw.loading.hide();

					modal.set('html', status+ ': '+ error.message);
				}
			});
		},
		/**
		 * Resize .fw-options-tabs-contents to fit entire window
		 */
		resizeTabsContent: function () {

			var $content, $frame;

			$content = this.frame.$el.find('.fw-options-tabs-first-level > .fw-options-tabs-contents');
			if ($content.length == 0) {
				return;
			}

			$frame = $content.closest('.media-frame-content');

			// resize icon list to fit entire window
			$content.css('overflow-y', 'auto').height(1000000);
			$frame.scrollTop(1000000);

			// -1 is necessary for Linux and Windows
			// -2 is necessary for Mac OS
			// I don't know where this numbers come from but without this adjustment
			// vertical scroll bar appears.
			$content.height($content.height() - $frame.scrollTop() /* - 2  */);

			// This is another fix for vertical scroll bar issue
			$frame.css('overflow-y', 'hidden');
		}
	});

})();

/*!
 * jquery.base64.js 0.0.3 - https://github.com/yckart/jquery.base64.js
 * Makes Base64 en & -decoding simpler as it is.
 *
 * Based upon: https://gist.github.com/Yaffle/1284012
 *
 * Copyright (c) 2012 Yannick Albert (http://yckart.com)
 * Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php).
 * 2013/02/10
 *
 * Modified
 **/
;(function($) {

	var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		a256 = '',
		r64 = [256],
		r256 = [256],
		i = 0;

	var UTF8 = {

		/**
		 * Encode multi-byte Unicode string into utf-8 multiple single-byte characters
		 * (BMP / basic multilingual plane only)
		 *
		 * Chars in range U+0080 - U+07FF are encoded in 2 chars, U+0800 - U+FFFF in 3 chars
		 *
		 * @param {String} strUni Unicode string to be encoded as UTF-8
		 * @returns {String} encoded string
		 */
		encode: function(strUni) {
			// use regular expressions & String.replace callback function for better efficiency
			// than procedural approaches
			var strUtf = strUni.replace(/[\u0080-\u07ff]/g, // U+0080 - U+07FF => 2 bytes 110yyyyy, 10zzzzzz
				function(c) {
					var cc = c.charCodeAt(0);
					return String.fromCharCode(0xc0 | cc >> 6, 0x80 | cc & 0x3f);
				})
				.replace(/[\u0800-\uffff]/g, // U+0800 - U+FFFF => 3 bytes 1110xxxx, 10yyyyyy, 10zzzzzz
				function(c) {
					var cc = c.charCodeAt(0);
					return String.fromCharCode(0xe0 | cc >> 12, 0x80 | cc >> 6 & 0x3F, 0x80 | cc & 0x3f);
				});
			return strUtf;
		},

		/**
		 * Decode utf-8 encoded string back into multi-byte Unicode characters
		 *
		 * @param {String} strUtf UTF-8 string to be decoded back to Unicode
		 * @returns {String} decoded string
		 */
		decode: function(strUtf) {
			// note: decode 3-byte chars first as decoded 2-byte strings could appear to be 3-byte char!
			var strUni = strUtf.replace(/[\u00e0-\u00ef][\u0080-\u00bf][\u0080-\u00bf]/g, // 3-byte chars
				function(c) { // (note parentheses for precence)
					var cc = ((c.charCodeAt(0) & 0x0f) << 12) | ((c.charCodeAt(1) & 0x3f) << 6) | (c.charCodeAt(2) & 0x3f);
					return String.fromCharCode(cc);
				})
				.replace(/[\u00c0-\u00df][\u0080-\u00bf]/g, // 2-byte chars
				function(c) { // (note parentheses for precence)
					var cc = (c.charCodeAt(0) & 0x1f) << 6 | c.charCodeAt(1) & 0x3f;
					return String.fromCharCode(cc);
				});
			return strUni;
		}
	};

	while(i < 256) {
		var c = String.fromCharCode(i);
		a256 += c;
		r256[i] = i;
		r64[i] = b64.indexOf(c);
		++i;
	}

	function code(s, discard, alpha, beta, w1, w2) {
		s = String(s);
		var buffer = 0,
			i = 0,
			length = s.length,
			result = '',
			bitsInBuffer = 0;

		while(i < length) {
			var c = s.charCodeAt(i);
			c = c < 256 ? alpha[c] : -1;

			buffer = (buffer << w1) + c;
			bitsInBuffer += w1;

			while(bitsInBuffer >= w2) {
				bitsInBuffer -= w2;
				var tmp = buffer >> bitsInBuffer;
				result += beta.charAt(tmp);
				buffer ^= tmp << bitsInBuffer;
			}
			++i;
		}
		if(!discard && bitsInBuffer > 0) result += beta.charAt(buffer << (w2 - bitsInBuffer));
		return result;
	}

	var Plugin = function(dir, input, encode) {
		return input ? Plugin[dir](input, encode) : dir ? null : this;
	};

	Plugin.btoa = Plugin.encode = function(plain, utf8encode) {
		plain = Plugin.raw === false || Plugin.utf8encode || utf8encode ? UTF8.encode(plain) : plain;
		plain = code(plain, false, r256, b64, 8, 6);
		return plain + '===='.slice((plain.length % 4) || 4);
	};

	Plugin.atob = Plugin.decode = function(coded, utf8decode) {
		coded = coded.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		coded = String(coded).split('=');
		var i = coded.length;
		do {--i;
			coded[i] = code(coded[i], true, r64, a256, 6, 8);
		} while (i > 0);
		coded = coded.join('');
		return Plugin.raw === false || Plugin.utf8decode || utf8decode ? UTF8.decode(coded) : coded;
	};

	if (!window.btoa) window.btoa = Plugin.btoa;
	if (!window.atob) window.atob = Plugin.atob;
}(jQuery));

/**
 * fw.qtip($elements)
 */
(function($){
	/**
	 * Trigger custom event with delay when mouse left (i) and popup
	 * @param $i
	 */
	function initHide($i) {
		var api = $i.qtip('api');

		var hideTimeout = 0;
		var hideDelay = 200;

		var hide = function(){
			clearTimeout(hideTimeout);

			hideTimeout = setTimeout(function(){
				$i.trigger('fw-qtip:hide');
			}, hideDelay);
		};

		{
			api.elements.tooltip
				.on('mouseenter', function(){
					clearTimeout(hideTimeout);
				})
				.on('mouseleave', function(){
					hide();
				});

			$i
				.on('mouseenter', function(){
					clearTimeout(hideTimeout);
				})
				.on('mouseleave', function(){
					hide();
				});
		}
	};

	var idIncrement = 1;

	function initHelps($helps) {
		$helps.each(function(){
			var $i = $(this);

			var id = 'fw-qtip-'+ idIncrement++;

			var hideInitialized = false;

			$i.qtip({
				id: id,
				position: {
					viewport: $(document.body),
					at: 'top center',
					my: 'bottom center',
					adjust: {
						y: 2
					}
				},
				style: {
					classes: $i.hasClass('dashicons-info')
						? 'qtip-fw fw-tip-info'
						: 'qtip-fw',
					tip: {
						width: 12,
						height: 5
					}
				},
				show: {
					solo: true,
					event: 'mouseover',
					effect: function(offset) {
						// fix tip position
						setTimeout(function(){
							offset.elements.tooltip.css('top',
								(parseInt(offset.elements.tooltip.css('top')) + 5) + 'px'
							);
						}, 12);

						if (!hideInitialized) {
							initHide($i);

							hideInitialized = true;
						}

						$(this).fadeIn(300);
					}
				},
				hide: {
					event: 'fw-qtip:hide',
					effect: function(offset) {
						$(this).fadeOut(300, function(){
							/**
							 * Reset tip content html.
							 * Needed for video tips, after hide the video should stop.
							 */
							api.elements.content.html($i.attr('title'))
						});
					}
				}
			});

			$i.on('remove', function(){
				api.hide();
			});

			var api = $i.qtip('api');
		});
	};

	fw.qtip = initHelps;
})(jQuery);

/**
 * Allow to select external links
 * jQuery('a:fw-external')
 */
jQuery.expr[':']['fw-external'] = function(obj){
	return !obj.href.match(/^mailto\:/)
		&& (obj.hostname != location.hostname)
		&& !obj.href.match(/^javascript\:/)
		&& !obj.href.match(/^$/);
};

/**
 * Check if an event fired from an element has listeners within specified container/parent element
 * @param $element
 * @param {String} event
 * @param $container
 * @return {Boolean}
 */
fw.elementEventHasListenerInContainer = function ($element, event, $container) {
	"use strict";

	var events, container = $container.get(0);

	/**
	 * Check if container element has delegated event that matches the element
	 */
	{
		var foundListener = false;

		if (
			(events = $container.data('events'))
			&&
			events[event]
		) {
			jQuery.each(events[event], function(i, eventData){
				if ($element.is(eventData.selector)) {
					foundListener = true;
					return false;
				}
			});
		}

		if (foundListener) {
			return true;
		}
	}

	/**
	 * Check every parent if has event listener
	 */
	{
		var $currentParent = $element;

		while ($currentParent.get(0) !== container) {
			if (
				(events = $currentParent.data('events'))
				&&
				events[event]
			) {
				return true;
			}

			if ($currentParent.attr('on'+ event)) {
				return true;
			}

			$currentParent = $currentParent.parent();

			if (!$currentParent.length) {
				/**
				 * The element doesn't exist in DOM
				 * This means that the event was processed, so it has listener
				 */
				return true;
			}
		}
	}

	return false;
};

/**
 * Simple modal
 * Meant to display success/error messages
 * Can be called multiple times,all calls will be pushed to queue and displayed one-by-one
 *
 * Usage:
 *
 * // open modal with close button and wait for user to close it
 * fw.soleModal.show('unique-id', 'Hello World!');
 *
 * // open modal with close button but auto hide it after 3 seconds
 * fw.soleModal.show('unique-id', 'Hello World!', {autoHide: 3000});
 *
 * fw.soleModal.hide('unique-id');
 */
fw.soleModal = (function(){
	var inst = {
		queue: [
			/*
			{
				id: 'hello'
				html: 'Hello <b>World</b>!'
				autoHide: 0000 // auto hide timeout in ms
				allowClose: true // useful when you make an ajax and must force the user to wait until it will finish
				showCloseButton: true // false will hide the button, but the user will still be able to click on backdrop to close it
				width: 350
				height: 200
				hidePrevious: false // just replace the modal content or hide the previous modal and open it again with new content
			}
			*/
		],
		/** @type {Object|null} */
		current: null,
		animationTime: 300,
		$modal: null,
		backdropOpacity: 0.7, // must be the same as in .fw-modal style
		currentMethod: '',
		currentMethodTimeoutId: 0,
		pendingMethod: '',
		lazyInit: function(){
			if (this.$modal) {
				return false;
			}

			this.$modal = jQuery(
				'<div class="fw-modal fw-sole-modal" style="display:none;">'+
				'    <div class="media-modal wp-core-ui" style="width: 350px; height: 200px;">'+
				'        <div class="media-modal-content" style="min-height: 200px;">' +
				'            <a class="media-modal-close" href="#" onclick="return false;"><span class="media-modal-icon"></span></a>'+
				'            <table width="100%" height="100%"><tbody><tr>'+
				'                <td valign="middle" class="fw-sole-modal-content fw-text-center"><!-- modal content --></td>'+
				'            </tr><tbody></table>'+
				'        </div>'+
				'    </div>'+
				'    <div class="media-modal-backdrop"></div>'+
				'</div>'
			);

			( this.$getCloseButton().add(this.$getBackdrop()) ).on('click', _.bind(function(){
				if (this.current && !this.current.allowClose) {
					// manual close not is allowed
					return;
				}

				this.hide();
			}, this));

			jQuery(document.body).append(this.$modal);

			return true;
		},
		$getBackdrop: function() {
			this.lazyInit();

			return this.$modal.find('.media-modal-backdrop:first');
		},
		$getCloseButton: function() {
			this.lazyInit();

			return this.$modal.find('.media-modal-close:first');
		},
		$getContent: function() {
			return this.$modal.find('.fw-sole-modal-content:first');
		},
		setContent: function(html) {
			this.lazyInit();

			this.$getContent().html(html || '&nbsp;');
		},
		runPendingMethod: function() {
			if (this.currentMethod) {
				return false;
			}

			if (!this.pendingMethod) {
				if (this.queue.length) {
					// there are messages to display
					this.pendingMethod = 'show';
				} else {
					return false;
				}
			}

			var pendingMethod = this.pendingMethod;

			this.pendingMethod = '';

			if (pendingMethod == 'hide') {
				this.hide();
				return true;
			} else if (pendingMethod == 'show') {
				this.show();
				return true;
			} else {
				console.warn('Unknown pending method:', pendingMethod);
				this.hide();
				return false;
			}
		},
		/**
		 * Show modal
		 * Call without arguments to display next from queue
		 * @param {String} [id]
		 * @param {String} [html]
		 * @param {Object} [opts]
		 * @returns {Boolean}
		 */
		show: function (id, html, opts) {
			if (typeof id != 'undefined') {
				// make sure to remove this id from queue (if was added previously)
				this.queue = _.filter(this.queue, function (item) { return item.id != id; });

				{
					opts = jQuery.extend({
						autoHide: 0,
						allowClose: true,
						showCloseButton: true,
						width: 350,
						height: 200,
						hidePrevious: false
					}, opts || {});

					// hide close button if close is not allowed
					opts.showCloseButton = opts.showCloseButton && opts.allowClose;

					opts.id = id;
					opts.html = html;
				}

				this.queue.push(opts);

				return this.show();
			}

			if (this.currentMethod) {
				return false;
			}

			if (this.current) {
				return false;
			}

			this.currentMethod = '';

			this.current = this.queue.shift();

			if (!this.current) {
				this.hide();
				return false;
			}

			this.currentMethod = 'show';

			this.setContent(this.current.html);

			this.$getCloseButton().css('display', this.current.showCloseButton ? '' : 'none');

			this.$modal.removeClass('fw-modal-closing');
			this.$modal.addClass('fw-modal-open');

			this.$modal.css('display', '');

			// set size
			{
				var $size = this.$modal.find('> .media-modal');

				if (
					$size.height() != this.current.height
					||
					$size.width() != this.current.width
				) {
					$size.animate({
						'height': this.current.height +'px',
						'width': this.current.width +'px'
					}, this.animationTime);
				}

				$size = undefined;
			}

			this.currentMethodTimeoutId = setTimeout(_.bind(function() {
				this.currentMethod = '';

				if (this.runPendingMethod()) {
					return;
				}

				if (this.current.autoHide > 0) {
					this.currentMethod = 'auto-hide';
					this.currentMethodTimeoutId = setTimeout(_.bind(function () {
						this.currentMethod = '';
						this.hide();
					}, this), this.current.autoHide);
				}
			}, this), this.animationTime * 2);
		},
		/**
		 * @param {String} [id]
		 * @returns {boolean}
		 */
		hide: function(id) {
			if (typeof id != 'undefined') {
				if (this.current && this.current.id == id) {
					// this id is currently displayed, hide it
				} else {
					// remove id from queue
					this.queue = _.filter(this.queue, function (item) {
						return item.id != id;
					});
					return true;
				}
			}

			if (this.currentMethod) {
				if (this.currentMethod == 'hide') {
					return false;
				} else if (this.currentMethod == 'auto-hide') {
					clearTimeout(this.currentMethodTimeoutId);
				} else {
					this.pendingMethod = 'hide';
					return true;
				}
			}

			this.currentMethod = '';

			if (!this.current) {
				// nothing to hide
				return this.runPendingMethod();;
			}

			this.currentMethod = 'hide';

			if (this.queue.length && !this.queue[0].hidePrevious) {
				// replace content
				this.$getContent().fadeOut('fast', _.bind(function(){
					this.currentMethod = '';
					this.current = null;
					this.show();
					this.$getContent().fadeIn('fast');
				}, this));

				return true;
			}

			this.$modal.addClass('fw-modal-closing');

			this.currentMethodTimeoutId = setTimeout(_.bind(function(){
				this.currentMethod = '';

				this.$modal.css('display', 'none');

				this.$modal.removeClass('fw-modal-open');
				this.$modal.removeClass('fw-modal-closing');

				this.setContent('');

				this.current = null;

				this.runPendingMethod();
			}, this), this.animationTime);
		}
	};

	return {
		show: function(id, html, opts) {
			inst.show(id, html, opts);
		},
		hide: function(id){
			inst.hide(id);
		},
		/**
		 * Generate flash messages html for soleModal content
		 */
		renderFlashMessages: function (flashMessages) {
			var html = [],
				typeHtml = [],
				typeMessageClass = '',
				typeIconClass = '',
				typeTitle = '';

			jQuery.each(flashMessages, function(type, messages){
				typeHtml = [];

				switch (type) {
					case 'error':
						typeMessageClass = 'fw-text-danger';
						typeIconClass = 'dashicons dashicons-dismiss';
						typeTitle = _fw_localized.l10n.ah_sorry;
						break;
					case 'warning':
						typeMessageClass = 'fw-text-warning';
						typeIconClass = 'dashicons dashicons-no-alt';
						typeTitle = _fw_localized.l10n.ah_sorry;
						break;
					case 'success':
						typeMessageClass = 'fw-text-success';
						typeIconClass = 'dashicons dashicons-star-filled';
						typeTitle = _fw_localized.l10n.done;
						break;
					case 'info':
						typeMessageClass = 'fw-text-info';
						typeIconClass = 'dashicons dashicons-info';
						typeTitle = _fw_localized.l10n.done;
						break;
					default:
						typeMessageClass = typeIconClass = typeTitle = '';
				}

				jQuery.each(messages, function(messageId, message){
					typeHtml.push(
						'<li>'+
							'<h2 class="'+ typeMessageClass +'"><span class="'+ typeIconClass +'"></span> '+ typeTitle +'</h2>'+
							'<p class="fw-text-muted"><em>'+ message +'</em></p>'+
						'</li>'
					);
				});

				if (typeHtml.length) {
					html.push(
						'<ul>'+ typeHtml.join('</ul><ul>') +'</ul>'
					);
				}
			});

			return html.join('');
		}
	};
})();