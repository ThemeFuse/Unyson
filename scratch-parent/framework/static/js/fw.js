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
 */
fw.md5 = function(string)
{
	function RotateLeft(lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}

	function AddUnsigned(lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			} else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		} else {
			return (lResult ^ lX8 ^ lY8);
		}
	}

	function F(x,y,z) { return (x & y) | ((~x) & z); }
	function G(x,y,z) { return (x & z) | (y & (~z)); }
	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }

	function FF(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function GG(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function HH(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function II(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function ConvertToWordArray(string) {
		var lWordCount;
		var lMessageLength = string.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	};

	function WordToHex(lValue) {
		var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			WordToHexValue_temp = "0" + lByte.toString(16);
			WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
		}
		return WordToHexValue;
	};

	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	};

	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;

	string = Utf8Encode(string);

	x = ConvertToWordArray(string);

	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

	for (k=0;k<x.length;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=AddUnsigned(a,AA);
		b=AddUnsigned(b,BB);
		c=AddUnsigned(c,CC);
		d=AddUnsigned(d,DD);
	}

	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);

	return temp.toLowerCase();
};

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
			'background-image': 'url(' + fw.SITE_URI + '/wp-admin/images/spinner.gif)',
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
		defaultSize: 'small',
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

					$modalWrapper.addClass('fw-options-modal');

					/*
					 * if the modal has specified what size it wants to have
					 * we obey, if not then we set a default size in case it is
					 * the first modal in the stack, or scale it down if it isn't
					 */
					if (_.indexOf(['large', 'medium', 'small'], size) !== -1) {
						$modalWrapper.addClass('fw-options-modal-' + size);
					} else {
						var $topModal = modalsStack.peek();
						if ($topModal) {
							var topModalPositions = _.map(
								$topModal.css(['top', 'bottom', 'left', 'right']),
								parseFloat
							);
							$modal.css({
								top:    topModalPositions[0] + 30,
								bottom: topModalPositions[1] + 30,
								left:   topModalPositions[2] + 30,
								right:  topModalPositions[3] + 30
							});
						} else {
							$modalWrapper.addClass('fw-options-modal-' + modal.defaultSize);
						}
					}

					/*
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
							$modalWrapper.addClass('fw-options-modal-closing');

							closingTimeout = setTimeout(function(){
								closingTimeout = 0;

								// remove events that prevent original close
								$close.off(eventsNamespace);
								$backdrop.off(eventsNamespace);

								// fire original close process after animation effect finished
								$close.trigger('click');
								$backdrop.trigger('click');

								// remove animation class
								$modalWrapper.removeClass('fw-options-modal-closing');

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

					$modalWrapper.addClass('fw-options-modal-open');

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

					modal.frame.modal.$el.removeClass('fw-options-modal-open');

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
