/**
 * Listen and trigger custom events to communicate between javascript components
 */
var fwEvents = new (function(){
	var _events = {};
	var currentIndentation = 1;
	var debug = false;

	this.countAll = function (topic) {
		return _events[topic];
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
	 */
	this.on = function(topicStringOrObject, listener) {
		objectMap(
			splitTopicStringOrObject(topicStringOrObject, listener),
			function (eventName, listener) {
				(_events[eventName] || (_events[eventName] = [])).push(
					listener
				);

				debug && log('✚ ' + eventName);
			}
		);

		return this;
	};

	/**
	 * Same as .on(), but callback will executed only once
	 */
	this.one = function(topicStringOrObject, listener) {
		objectMap(
			splitTopicStringOrObject(topicStringOrObject, listener),
			function (eventName, listener) {
				(_events[eventName] || (_events[eventName] = [])).push(
					once(listener)
				);

				debug && log('✚ [' + eventName +']');
			}
		);

		return this;

		// https://github.com/jashkenas/underscore/blob/8fc7032295d60aff3620ef85d4aa6549a55688a0/underscore.js#L946
		function once(func) {
			var memo;

			var times = 2;

			return function() {
				if (--times > 0) {
					memo = func.apply(this, arguments);
				}

				if (times <= 1) func = null;

				return memo;
			};
		};
	};

	/**
	 * In order to remove one single listener you should give as an argument
	 * the same callback function. If you want to remove *all* listeners from
	 * a particular event you should not pass the second argument.
	 *
	 * @param topicStringOrObject {String | Object}
	 * @param listener {Function | false}
	 */
	this.off = function(topicStringOrObject, listener) {
		objectMap(
			splitTopicStringOrObject(topicStringOrObject, listener),
			function (eventName, listener) {
				if (_events[eventName]) {
					if (listener) {
						_events[eventName].splice(
							_events[eventName].indexOf(listener) >>> 0,
							1
						);
					} else {
						_events[eventName] = [];
					}

					debug && log('✖ ' + eventName);
				}
			}
		);

		return this;
	};

	/**
	 * Trigger an event. In case you provide multiple events via space-separated
	 * string or an object of events it will execute listeners for each event
	 * separatedly. You can use the "all" event to trigger all events.
	 *
	 * @param topicStringOrObject {String | Object}
	 * @param data {Object}
	 */
	this.trigger = function(eventName, data) {
		objectMap(
			splitTopicStringOrObject(eventName),
			function (eventName) {
				log('╭─ '+ eventName, data);

				changeIndentation(+1);

				try {
					// TODO: REFACTOR THAT!!!!!!!!!
					// Maybe this is an occasion for using 'all' event???
					if (eventName === 'fw:options:init') {
						fw.options.startListeningToEvents(
							data.$elements || document.body
						)
					}

					(_events[eventName] || []).map(dispatchSingleEvent);
					(_events['all'] || []).map(dispatchSingleEvent);
				} catch (e) {
					console.log(
						"%c [Events] Exception raised. Please contact support in https://github.com/ThemeFuse/Unyson/issues/new. Don't forget to attach this stack trace to the issue.",
						"color: red; font-weight: bold;"
					);

					if (typeof console !== 'undefined') {
						console.error(e)
					} else {
						throw e;
					}
				}

				changeIndentation(-1);

				log('╰─ '+ eventName, data);

				function dispatchSingleEvent (listenerDescriptor) {
					if (! listenerDescriptor) return;

					listenerDescriptor.call(
						window,
						data
					);
				}
			}
		);

		return this;

		function changeIndentation(increment) {
			if (typeof increment != 'undefined') {
				currentIndentation += (increment > 0 ? +1 : -1);
			}

			if (currentIndentation < 0) {
				currentIndentation = 0;
			}
		}
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

		var arrayOfEvents = topicStringOrObject.replace(
			/\s\s+/g, ' '
		).trim().split(' ');

		var len = arrayOfEvents.length;

		var listenerDescriptor = Object.create(null);

		for (var i = 0; i < len; i++) {
			listenerDescriptor[arrayOfEvents[i]] = listener;
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

	function log(message, data) {
		if (! debug) {
			return;
		}

		if (typeof data != 'undefined') {
			console.log('[Event] ' + getIndentation() + message, '─', data);
		} else {
			console.log('[Event] ' + getIndentation() + message);
		}

		function getIndentation() {
			return new Array(currentIndentation).join('│ ');
		}
	}
})();
