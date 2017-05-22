/**
 * Basic options registry
 */
fw.options = (function ($, currentFwOptions) {
	/**
	 * An object of hints
	 */
	var allOptionTypes = {};

	currentFwOptions.get = get;
	currentFwOptions.getAll = getAll;
	currentFwOptions.register = register;
	currentFwOptions.getOptionDescriptor = getOptionDescriptor;
	currentFwOptions.startListeningToEvents = startListeningToEvents;

	/**
	 * fw.options.getValueForEl(element)
	 *   .then(function (values, optionDescriptor) {
	 *     // current values for option type
	 *     console.log(values)
	 *   })
	 *   .fail(function () {
	 *     // value extraction failed for some reason
	 *   });
	 */
	currentFwOptions.getValueForEl = getValueForEl;

	return currentFwOptions;

	/**
	 * get hint object for a specific type
	 */
	function get (type) {
		return allOptionTypes[type] || allOptionTypes['fw-undefined'];
	}

	function getAll () {
		return allOptionTypes;
	}

	/**
	 * Returns:
	 *   el
	 *   ID
	 *   type
	 *   isRootOption
	 *   context
	 *   nonOptionContext
	 */
	function getOptionDescriptor (el) {
		var data = {};

		data.context = detectDOMContext(el);

		data.el = findOptionDescriptorEl(el);
		data.id = $(data.el).attr('data-fw-option-id');
		data.type = $(data.el).attr('data-fw-option-type');
		data.isRootOption = isRootOption(data.el, findNonOptionContext(data.el));
		data.hasNestedOptions = hasNestedOptions(data.el);

		data.pathToTheTopContext = data.isRootOption
									? []
									: findPathToTheTopContext(
										data.el,
										findNonOptionContext(data.el)
									);

		return data;
	}

	function getValueForEl (el) {
		var optionDescriptor = getOptionDescriptor(el);

		return get(optionDescriptor.type).getValue(
			optionDescriptor
		);
	}

	/**
	 * You are not registering here a full fledge class definition for an
	 * option type just like we have on backend. It is more of a hint on how
	 * to treat the option type on frontend. Everything should be working
	 * almost fine even if you don't provide any hints.
	 *
	 * interface:
	 *
	 *   startListeningForChanges
	 *   getValue
	 */
	function register (type, hintObject) {
		// TODO: maybe start triggering events on option type register

		if (allOptionTypes[type]) {
			throw "Can't re-register an option type again";
		}

		allOptionTypes[type] = jQuery.extend(
			{}, defaultHintObject(),
			hintObject || {}
		);
	}

	/**
	 * This will be automatically called at each fw:options:init event.
	 * This will make each option type start listening to events
	 */
	function startListeningToEvents (el) {
		// TODO: compute path up untill non-option context
		el = (el instanceof jQuery) ? el[0] : el;

		[].map.call(
			el.querySelectorAll(
				'.fw-backend-option-descriptor[data-fw-option-type]'
			),
			function (el) {
				startListeningToEventsForSingle(
					getOptionDescriptor(el)
				);
			}
		);
	}

	function startListeningToEventsForSingle (optionDescriptor) {
		var hints = get(optionDescriptor.type);

		hints.startListeningForChanges(optionDescriptor);
	}

	/**
	 * We rely on the fact that by default, when we try to register some option
	 * type -- the undefined and default one will be already registered.
	 */
	function defaultHintObject () {
		return get('fw-undefined');
	}

	function detectDOMContext (el) {
		el = findOptionDescriptorEl(el);

		var nonOptionContext = findNonOptionContext(el);

		return isRootOption(el, nonOptionContext)
			? nonOptionContext
			: findOptionDescriptorEl(el.parentElement);
	}

	function findOptionDescriptorEl (el) {
		el = (el instanceof jQuery) ? el[0] : el;

		return el.classList.contains('fw-backend-option-descriptor')
			? el
			: $(el).closest('.fw-backend-option-descriptor')[0];
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

	function findPathToTheTopContext (el, nonOptionContext) {
		var parent;

		var result = [];

		// traverse parents
		while (el) {
			parent = el.parentElement;

			if (parent === nonOptionContext) {
				return result;
			}

			if (parent && elementMatches(parent, '.fw-backend-option-descriptor')) {
				// result.push(parent.getAttribute('data-fw-option-type'));
				result.push(parent);
			}

			el = parent;
		}

		return result.reverse();
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

	function hasNestedOptions (el) {
		// exclude nested options within a virtual context

		var optionDescriptor = findOptionDescriptorEl(el);

		var hasVirtualContext = optionDescriptor.querySelector(
			'.fw-backend-options-virtual-context'
		);

		if (! hasVirtualContext) {
			return !! optionDescriptor.querySelector(
				'.fw-backend-option-descriptor'
			);
		}

		// check if we have options which are not in the virtual context
		return optionDescriptor.querySelectorAll(
			'.fw-backend-option-descriptor'
		).length > optionDescriptor.querySelectorAll(
			'.fw-backend-options-virtual-context .fw-backend-option-descriptor'
		).length;
	}

	function elementMatches (element, selector) {
		var matchesFn;

		// find vendor prefix
		[
			'matches','webkitMatchesSelector','mozMatchesSelector',
			'msMatchesSelector','oMatchesSelector'
		].some(function(fn) {
			if (typeof document.body[fn] == 'function') {
				matchesFn = fn;
				return true;
			}

			return false;
		})

		return element[matchesFn](selector);
	}

})(jQuery, (fw.options || {}));

