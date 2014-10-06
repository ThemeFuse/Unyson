/**
 * Display FW_Form errors
 */
(function(localized){
	jQuery(function($){
		var $form = $('form#'+ localized.form_attr.id);

		if (!$form.length) {
			return;
		}

		var eventsNamespace = '.fw-form-errors';

		$.each(localized.errors, function(name, error){
			var $error = $('<div class="form-error"></div>');

			$error.text(error);

			var $input = $form.find('[name="'+ name +'"]');

			if (!$input.length) {
				// maybe input name has array format, try to find by prefix: name[
				$input = $form.find('[name^="'+ name +'["]');
			}

			if ($input.length) {
				if ($input.length == 1) {
					// there is only one input with the same name, attach error to it
					$error.insertAfter($input);
				} else {
					// there are many inputs with the same name, attach error to parent container
					$error.insertAfter( $input.first().closest('div') );
				}

				var errorId = ('form-error-'+ Math.random()).replace(/[^a-z0-9\-\_]/g, '');

				$error.attr('id', errorId);

				$input
					.off('focus'+ eventsNamespace)
					.one('focus'+ eventsNamespace, function(){
						$('#'+ errorId).slideUp(function(){ $(this).remove() });
					});
			} else {
				$form.prepend($error);
			}
		});
	});
})(_localized_form_errors);
