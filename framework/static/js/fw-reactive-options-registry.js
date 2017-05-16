/**
 * Basic options registry
 */
fw.options = (function ($, currentFwOptions) {
	currentFwOptions.get = get;
	currentFwOptions.register = register;

	return currentFwOptions;

	function get (type) {
	}

	function register (type, hintObject) {
	}
})(jQuery, (fw.options || {}));

