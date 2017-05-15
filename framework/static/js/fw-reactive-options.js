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
fw.options = (function ($) {
	var service = {
		on: on,
		off: off,
		trigger: trigger
	};

	/**
	 * Allows:
	 *   fw.options.trigger(...)
	 *   fw.options.trigger.change(...)
	 *   fw.options.trigger.forEl(...)
	 *   fw.options.trigger.changeForEl(...)
	 *   fw.options.trigger.scopedByType(...)
	 */
	service.trigger.change = triggerChange;
	service.trigger.forEl = triggerForEl;
	service.trigger.changeForEl = triggerChangeForEl;
	service.trigger.scopedByType = triggerScopedByType;

	/**
	 * Allows:
	 *   fw.options.on(...)
	 *   fw.options.on.one(...)
	 *   fw.options.on.change(...)
	 *   fw.options.on.changeByContext(...)
	 */
	service.on.one = one;
	service.on.change = onChange;
	service.on.changeByContext = onChangeByContext;

	return service;

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

	/**
	 * data:
	 *  optionId
	 *  optionType
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
	function triggerScopedByType (eventName, data, el) {
		data = getActualData(el, data);

		trigger(data.optionType + ':' + eventName, data);
	}

	function getActualData (el, data) {
		return $.extend(
			{}, inferDataFromEl(el), data
		);
	}

	function inferDataFromEl(el) {
		var data = {};

		data.context = detectDOMContext(el);

		data.el = findOptionDescriptorEl(el);
		data.id = $(data.el).attr('data-fw-option-id');
		data.type = $(data.el).attr('data-fw-option-type');
		data.isRootOption = isRootOption(data.el, findNonOptionContext(data.el));

		return data;
	}

	function findOptionDescriptorEl (el) {
		el = (el instanceof jQuery) ? el[0] : el;

		return el.classList.contains('fw-backend-option-descriptor')
			? el
			: $(el).closest('.fw-backend-option-descriptor')[0];
	}

	function detectDOMContext (el) {
		el = findOptionDescriptorEl(el);

		var nonOptionContext = findNonOptionContext(el);

		return isRootOption(el, nonOptionContext)
			? nonOptionContext
			: findOptionDescriptorEl(el.parentElement);
	}

	/**
	 * A non-option context has two possible values:
	 * 
	 * - a form tag which encloses a list of root options
	 * - a virtual context is an el with `.fw-backend-options-virtual-context`
	 */
	function findNonOptionContext (el) {
		var parent;

		// traverse parents
		while (el) {
			parent = el.parentElement;

			if (parent && elementMatches(
				parent,
				'.fw-backend-options-virtual-context, form'
			)) {
				return parent;
			}

			el = parent;
		}

		return null;
	}

	function isRootOption(el, nonOptionContext) {
		var parent;

		// traverse parents
		while (el) {
			parent = el.parentElement;

			if (parent === nonOptionContext) {
				return true;
			}

			if (parent && elementMatches(parent, '.fw-backend-option-descriptor')) {
				return false;
			}

			el = parent;
		}
	}

	function elementMatches (element, selector) {
		var matchesFn;

		// find vendor prefix
		['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn) {
			if (typeof document.body[fn] == 'function') {
				matchesFn = fn;
				return true;
			}
			return false;
		})

		return element[matchesFn](selector);
	}
})(jQuery);


