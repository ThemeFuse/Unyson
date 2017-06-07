/**
 * Basic mechanism that allows option types to notify the rest of the world
 * about the fact that they was changed by the user.
 *
 * Each option type is responsible for triggering such events and they should
 * at least try to supply a reasonable current value for it. Additional meta
 * data about the particular option type is infered automatically and can be
 * overrided by the option which triggers the event.
 *
 * In theory (and also in practice), options can and should trigger events
 * which are different than the `change`, because complicated option types
 * has many lifecycle events which everyone should be aware about and be able
 * to hook into. A lot of options already trigger such events but they do that
 * in an inconsistent manner which leads to poorly named and poorly namespaced
 * events.
 *
 * TODO: document this better
 */
fw.options = (function ($, currentFwOptions) {
	currentFwOptions.on = on;
	currentFwOptions.off = off;
	currentFwOptions.trigger = trigger;

	/**
	 * Allows:
	 *   fw.options.trigger(...)
	 *   fw.options.trigger.change(...)
	 *   fw.options.trigger.forEl(...)
	 *   fw.options.trigger.changeForEl(...)
	 *   fw.options.trigger.scopedByType(...)
	 */
	currentFwOptions.trigger.change = triggerChange;
	currentFwOptions.trigger.forEl = triggerForEl;
	currentFwOptions.trigger.changeForEl = triggerChangeForEl;
	currentFwOptions.trigger.scopedByType = triggerScopedByType;

	/**
	 * Allows:
	 *   fw.options.on(...)
	 *   fw.options.on.one(...)
	 *   fw.options.on.change(...)
	 *   fw.options.on.changeByContext(...)
	 */
	currentFwOptions.on.one = one;
	currentFwOptions.on.change = onChange;
	currentFwOptions.on.changeByContext = onChangeByContext;

	/**
	 * Allows:
	 *   fw.options.off(...)
	 *   fw.options.off.change(...)
	 */
	currentFwOptions.off.change = offChange;


	return currentFwOptions;

	function onChange (listener) {
		on('change', listener);
	}

	/**
	 * Please note that you won't be able to off that listener easily because
	 * it rewrites the listener which gets passed to fwEvents.
	 *
	 * If you want to be able to off the listener you should attach it with
	 * onChange and filter based on context by yourself.
	 */
	function onChangeByContext (context, listener) {
		onChange(function (data) {
			if (data.context === findOptionDescriptorEl(context)) {
				listener(data);
			}
		});
	}

	function on(eventName, listener) {
		fwEvents.on('fw:options:' + eventName, listener);
	}

	function one(eventName, listener) {
		fwEvents.one('fw:options:' + eventName, listener);
	}

	function off (eventName, listener) {
		fwEvents.off('fw:options:' + eventName, listener);
	}

	function offChange (listener) {
		off('change', listener);
	}

	/**
	 * data:
	 *  optionId
	 *  type
	 *  value
	 *  context
	 *  el
	 */
	function trigger(eventName, data) {
		fwEvents.trigger('fw:options:' + eventName, data);
	}

	function triggerForEl(eventName, el, data) {
		trigger(eventName, getActualData(el, data));
	}

	function triggerChange (data) {
		trigger('change', data);
	}

	function triggerChangeForEl (el, data) {
		triggerChange(getActualData(el, data));
	}

	/**
	 * Trigger a scoped event for a specific option type, has the form:
	 *   fw:options:{type}:{eventName}
	 */
	function triggerScopedByType (eventName, el, data) {
		data = getActualData(el, data);

		trigger(data.type + ':' + eventName, data);
	}

	function getActualData (el, data) {
		return $.extend(
			{}, currentFwOptions.getOptionDescriptor(el), data
		);
	}

	function findOptionDescriptorEl (el) {
		el = (el instanceof jQuery) ? el[0] : el;

		return el.classList.contains('fw-backend-option-descriptor')
			? el
			: $(el).closest('.fw-backend-option-descriptor')[0];
	}
})(jQuery, (fw.options || {}));


