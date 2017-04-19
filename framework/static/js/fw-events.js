/**
 * Listen and trigger custom events to communicate between javascript components
 */
var fwEvents = new (function(){
	var _events = Object.create(null);

	{
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
	 * Make log helper public
	 *
	 * @param {String} [message]
	 * @param {Object} [data]
	 */
	this.log = log;

	/**
	 * Enable/Disable Debug
	 * @param {Boolean} enabled
	 */
	this.debug = function(enabled) {
		debug = Boolean(enabled);

		return this;
	};

	/**
	 * Add event listener
	 *
	 * @param event {String | Object}
	 *   Can be a:
	 *     - single event: 'event1'
	 *     - space separated event list: 'event1 event2 event2'
	 *     - an object: {event1: function () {}, event2: function () {}}
	 *
	 * @param callback {Function}
	 * @param context {Object | null} 
	 *   This is an object which would be the this inside the callback
	 */
	this.on = function(event, callback, context) {
		on(event, callback, context);

		if (debug) {
			if (typeof event == 'string') {
				// .on('event:name', callback)
				log('✚ '+ event);
			} else {
				// .on({'event:name': callback})
				_.each(event, function(_callback, _event){
					log('✚ '+ _event);
				});
			}
		}

		return this;
	};

	/**
	 * Same as .on(), but callback will executed only once
	 */
	this.one = function(event, callback, context) {
		once(event, callback);

		if (debug) {
			if (typeof event == 'string') {
				// .one('event:name', callback)
				log('✚ ['+ event +']');
			} else {
				// .one({'event:name': callback})
				_.each(event, function(_callback, _event){
					log('✚ ['+ _event +']');
				});
			}
		}

		return this;
	};

	/**
	 * Remove event listener
	 */
	this.off = function(event, callback, context) {
		off(event, callback, context);

		if (debug) {
			log('✖ '+ event);
		}

		return this;
	};

	/**
	 * Trigger event
	 *
	 * @public
	 * @param {String} event
	 * @param {Object} [data]
	 */
	this.trigger = function(eventName, data) {
		log('╭─ '+ eventName, data);

		changeIndentation(+1);

		try {
			trigger(eventName, data);
		} catch (e) {
			console.log('[Events] Exception ', {exception: e});

			if (console.trace) {
				console.trace();
			}
		}

		changeIndentation(-1);

		log('╰─ '+ eventName, data);

		return this;
	};

	/**
	 * Check if an event has listeners
	 * @param {String} [event]
	 * @return {Boolean}
	 */
	this.hasListeners = function(eventName) {
		if (! _events) {
			return false;
		}

		return (_events[eventName] || []).length > 0;
	};

	/**
	 * @param topicStringOrObject {String | Object}
	 * @param listener {Function}
	 */
	function on(topicStringOrObject, listener, context) {
		objectMap(
			splitTopicStringOrObject(topicStringOrObject, listener),
			function (eventName, listener) {
				(_events[eventName] || (_events[eventName] = [])).push({
					listener: listener,
					context: context
				});
			}
		);
	}

	/**
	 * Add a listener to an event which will get executed only once.
	 *
	 * @param topicStringOrObject {String | Object}
	 * @param listener {Function}
	 */
	function once(topicStringOrObject, listener, context) {
		objectMap(
			splitTopicStringOrObject(topicStringOrObject, listener),
			function (eventName, listener) {
				(_events[eventName] || (_events[eventName] = [])).push({
					listener: function executeOnce () {
						listener.apply(null, arguments);
						off(eventName, executeOnce);
					},

					context: context
				});
			}
		);
	}

	/**
	 * In order to remove one single listener you should give as an argument
	 * the same callback function. If you want to remove *all* listeners from
	 * a particular event you should not pass the second argument.
	 *
	 * @param topicStringOrObject {String | Object}
	 * @param listener {Function | false}
	 */
	function off(topicStringOrObject, listener) {
		objectMap(
			splitTopicStringOrObject(topicStringOrObject, listener),
			function (eventName, listener) {
				if (_events[eventName]) {
					if (listener) {
						_events[eventName].splice(
							_events[eventName].map(function (eventDescriptor) {
								return eventDescriptor.listener;
							}).indexOf(listener) >>> 0,
							1
						);
					} else {
						_events[eventName] = [];
					}
				}
			}
		);
	}

	/**
	 * Trigger an event. In case you provide multiple events via space-separated
	 * string or an object of events it will execute listeners for each event
	 * separatedly. You can use the "all" event to trigger all events.
	 *
	 * @param topicStringOrObject {String | Object}
	 * @param data {Object}
	 */
	function trigger(topicStringOrObject, data) {
		objectMap(
			splitTopicStringOrObject(topicStringOrObject, false),
			function (eventName) {
				(_events[eventName] || []).map(function (listenerDescriptor) {
					listenerDescriptor.listener.call(
						listenerDescriptor.context || null,
						data
					);
				});

				(_events['all'] || []).map(function (listenerDescriptor) {
					listenerDescriptor.listener.call(
						listenerDescriptor.context || null,
						eventName,
						data
					);
				});
			}
		);
	}

	/**
	 * Probably split string into general purpose object representation for
	 * event names and listeners. This function leaves objects un-modified.
	 *
	 * @param topicStringOrObject {String | Object}
	 * @param listener {Function | false}
	 *
	 * @returns {Object} {
	 *    eventname: listener,
	 *    otherevent: listener
	 * }
	 */
	function splitTopicStringOrObject (topicStringOrObject, listener) {
		if (typeof topicStringOrObject !== 'string') {
			return topicStringOrObject;
		}

		topicStringOrObject = topicStringOrObject.replace(
			/\s\s+/g, ' '
		).trim().split(' ');

		var len = topicStringOrObject.length;

		var listenerDescriptor = Object.create(null);

		for (var i = 0; i < len; i++) {
			listenerDescriptor[topicStringOrObject[i]] = listener;
		}

		return listenerDescriptor;
	}

	/**
	 * returns a new object with the predicate applied to each value
	 * objectMap({a: 3, b: 5, c: 9}, (key, value) => value + 1); // {a: 4, b: 6, c: 10}
	 * objectMap({a: 3, b: 5, c: 9}, (key, value) => key); // {a: 'a', b: 'b', c: 'c'}
	 * objectMap({a: 3, b: 5, c: 9}, (key, value) => key + value); // {a: 'a3', b: 'b5', c: 'c9'}
	 *
	 * https://github.com/angus-c/just/tree/master/packages/object-map
	 */
	function objectMap(obj, predicate) {
		var result = {};
		var keys = Object.keys(obj);
		var len = keys.length;

		for (var i = 0; i < len; i++) {
			var key = keys[i];
			result[key] = predicate(key, obj[key]);
		}

		return result;
	}
})();
