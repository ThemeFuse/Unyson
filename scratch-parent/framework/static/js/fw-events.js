/**
 * Listen and trigger custom events to communicate between javascript components
 */
var fwEvents = new (function(){
	{
		var eventsBox = _.extend({}, Backbone.Events);

		var debug = false;

		var log = function(message, data) {
			if (!debug) {
				return;
			}

			if (typeof data != 'undefined') {
				console.log('[Event] ' + getIndentation() + message, '─', data);
			} else {
				console.log('[Event] ' + getIndentation() + message);
			}
		};

		/**
		 * Indent logs that happens inside another event
		 */
		{
			var getIndentation = function() {
				return new Array(currentIndentation).join('│ ');
			};

			var currentIndentation = 1;

			var changeIndentation  = function(increment) {
				if (typeof increment != 'undefined') {
					currentIndentation += (increment > 0 ? +1 : -1);
				}

				if (currentIndentation < 0) {
					currentIndentation = 0;
				}
			};
		}
	}

	/**
	 * Enable/Disable Debug
	 * @param {Boolean} enabled
	 */
	this.debug = function(enabled) {
		debug = Boolean(enabled);
	};

	/**
	 * Add event listener
	 */
	this.on = function(event, callback, context) {
		eventsBox.on(event, callback, context);

		if (!debug) {
			return;
		}

		if (typeof event == 'string') {
			// .on('event:name', callback)
			log('✚ '+ event);
		} else {
			// .on({'event:name': callback})
			_.each(event, function(_callback, _event){
				log('✚ '+ _event);
			});
		}
	};

	/**
	 * Same as .on(), but callback will executed only once
	 */
	this.one = function(event, callback, context) {
		eventsBox.once(event, callback);

		if (!debug) {
			return;
		}

		if (typeof event == 'string') {
			// .one('event:name', callback)
			log('✚ ['+ event +']');
		} else {
			// .one({'event:name': callback})
			_.each(event, function(_callback, _event){
				log('✚ ['+ _event +']');
			});
		}
	};

	/**
	 * Remove event listener
	 */
	this.off = function(event, callback, context) {
		eventsBox.off(event, callback, context);

		if (!debug) {
			return;
		}

		log('✖ '+ event);
	};

	/**
	 * Trigger event
	 *
	 * @public
	 * @param {String} event
	 * @param {Object} [data]
	 */
	this.trigger = function(event, data) {
		log('╭─ '+ event, data);

		changeIndentation(+1);

		try {
			eventsBox.trigger(event, data);
		} catch (e) {
			console.log('[Events] Exception ', {exception: e});

			if (console.trace) {
				console.trace();
			}
		}

		changeIndentation(-1);

		log('╰─ '+ event, data);
	};

	/**
	 * Check if an event has listeners
	 * @param {String} [event]
	 * @return {Boolean}
	 */
	this.hasListeners = function(event) {
		if (!eventsBox._events) {
			return false;
		}

		return !!eventsBox._events[event];
	};
})();
