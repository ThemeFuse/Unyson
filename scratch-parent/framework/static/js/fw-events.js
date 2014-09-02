/**
 * Events utility
 * provides useful functionality to work with custom events and to communicate between javascript components
 */
var fwEvents = new (function()
{
	/**
	 * Element on what is added, removed and triggered custom events
	 */
	var eventsBox = {}; _.extend(eventsBox, Backbone.Events);

	/**
	 * Enable logging of what's happening inside class
	 * if you want to debug, change this to true
	 */
	var logsEnabled = true;

	var log = function(message, data) {
		if (!logsEnabled) {
			return;
		}

		if (data !== undefined) {
			console.log('[Event] ' + getIndentation() + message, '◼', data);
		} else {
			console.log('[Event] ' + getIndentation() + message);
		}
	};

	this.enableLogs = function ()
	{
		logsEnabled = true;
	};

	this.disableLogs = function ()
	{
		logsEnabled = false;
	};

	/**
	 * Add event listener
	 */
	this.on = function(event, callback, context)
	{
		eventsBox.on(event, callback, context);

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
	 * Same as .on(), but executed only once
	 */
	this.one = function(event, callback, context)
	{
		eventsBox.once(event, callback);

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
	this.off = function(event, callback, context)
	{
		eventsBox.off(event, callback, context);

		log('✖ '+ event);
	};

	/**
	 * Trigger event
	 *
	 * @public
	 * @param event
	 * @param [data]
	 */
	this.trigger = function(event, data)
	{
		log('╭╼▓ '+ event, data);

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

		log('╰╼░ '+ event, data);
	};

	/**
	 * If event triggered in process of triggering of another event, logs will be indented
	 */
	{
		var getIndentation = function()
		{
			return new Array(currentIndentation).join('│   ');
		};
		var currentIndentation = 1;
		var changeIndentation  = function(increment)
		{
			if (increment !== undefined) {
				currentIndentation += (increment > 0 ? +1 : -1);
			}

			if (currentIndentation < 0) {
				currentIndentation = 0;
			}
		};
	}
})();