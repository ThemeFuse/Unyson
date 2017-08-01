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
fw.options = (function($, currentFwOptions) {
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

	/**
	 * A small little service for fetching HTML for option types from server.
	 * It will perform caching for results inside a key-value store.
	 *
	 * Allows:
	 *
	 * fw.options.fetchHtml(options, values)
	 * fw.options.fetchHtml.getCacheEntryFor(options, values)
	 * fw.options.fetchHtml.emptyCache();
	 *
	 * // TODO: provide a way to empty cache for a specific set of options???
	 */
	var htmlCache = {};

	fw.options.fetchHtml = fetchHtml;
	fw.options.fetchHtml.getCacheEntryFor = fetchHtmlGetCacheEntryFor;
	fw.options.fetchHtml.emptyCache = fetchHtmlEmptyCache;

	/**
	 * A helper for getting actual values for a set of options and values.
	 * Much better than fw.getValuesFromServer() because it doesn't require
	 * you to encode values as form data params. You just pass a valid JSON
	 * object and it just works.
	 *
	 * fw.options
	 *   .getActualValues({a: {type: 'text', value: 'Initial'})
	 *   .then(function (values) {
	 *     // {
	 *     //   a: 'Initial'
	 *     // }
	 *     console.log(values);
	 *   });
	 *
	 * fw.options
	 *   .getActualValues({a: {type: 'text', value: 'Initial'}, {a: 'Changed'})
	 *   .then(function (values) {
	 *     // {
	 *     //   a: 'Changed'
	 *     // }
	 *     console.log(values);
	 *   });
	 */
    fw.options.getActualValues = getActualValues;

	return currentFwOptions;

	function onChange(listener) {
		on('change', listener);
	}

	/**
	 * Please note that you won't be able to off that listener easily because
	 * it rewrites the listener which gets passed to fwEvents.
	 *
	 * If you want to be able to off the listener you should attach it with
	 * onChange and filter based on context by yourself.
	 */
	function onChangeByContext(context, listener) {
		onChange(function(data) {
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

	function off(eventName, listener) {
		fwEvents.off('fw:options:' + eventName, listener);
	}

	function offChange(listener) {
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

	function triggerChange(data) {
		trigger('change', data);
	}

	function triggerChangeForEl(el, data) {
		triggerChange(getActualData(el, data));
	}

	/**
	 * Trigger a scoped event for a specific option type, has the form:
	 *   fw:options:{type}:{eventName}
	 */
	function triggerScopedByType(eventName, el, data) {
		data = getActualData(el, data);

		trigger(data.type + ':' + eventName, data);
	}

	function getActualData(el, data) {
		return $.extend({}, currentFwOptions.getOptionDescriptor(el), data);
	}

	function findOptionDescriptorEl(el) {
		el = el instanceof jQuery ? el[0] : el;

		return el.classList.contains('fw-backend-option-descriptor')
			? el
			: $(el).closest('.fw-backend-option-descriptor')[0];
	}

	function fetchHtml(options, values, settings) {
		var promise = $.Deferred();

		if (!settings) settings = {};

		settings = _.extend({ name_prefix: 'fw_edit_options_modal' }, settings);

		var cacheId = fetchHtmlGetCacheId(options, values);

		if (typeof htmlCache[cacheId] !== 'undefined') {
			promise.resolve(htmlCache[cacheId]);
			return promise;
		}

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'fw_backend_options_render',
				options: JSON.stringify(options),
				values: JSON.stringify(
					typeof values == 'undefined' ? {} : values
				),
				data: {
					name_prefix: settings.name_prefix,
					id_prefix: settings.name_prefix.replace(/_/g, '-') + '-',
				},
			},
			dataType: 'json',
			success: function(response, status, xhr) {
				if (!response.success) {
					promise.reject('Error: ' + response.data.message);
					return;
				}

				htmlCache[cacheId] = response.data.html;

				promise.resolve(response.data.html, response, status, xhr);
			},
			error: function(xhr, status, error) {
				promise.reject(status + ': ' + String(error));
			},
		});

		return promise;
	}

	function fetchHtmlGetCacheEntryFor(options, values) {
		return htmlCache[fetchHtmlGetCacheId(options, values)];
	}

	function fetchHtmlEmptyCache() {
		htmlCache = {};
	}

	function fetchHtmlGetCacheId(options, values) {
		return fw.md5(
			JSON.stringify(options) +
				'~' +
				JSON.stringify(typeof values == 'undefined' ? {} : values)
		);
	}

	function getActualValues (options, values) {
		var promise = $.Deferred();

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'fw_backend_options_get_values_json',
				options: JSON.stringify(options),
				values: JSON.stringify(
					typeof values == 'undefined' ? {} : values
				)
			},
			dataType: 'json',
			success: function(response, status, xhr) {
				if (!response.success) {
					promise.reject('Error: ' + response.data.message);
					return;
				}

				promise.resolve(response.data.values, response, status, xhr);
			},
			error: function(xhr, status, error) {
				promise.reject(status + ': ' + String(error));
			},
		});

		return promise;
	}
})(jQuery, fw.options || {});
