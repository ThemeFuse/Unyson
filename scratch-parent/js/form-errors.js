/**
 * Display FW_Form errors
 */
(function(localized){
	jQuery(function($){
		var $form = $('form#'+ localized.form_attr.id);

		if (!$form.length) {
			return;
		}

		$.each(localized.errors, function(name, error){
			var $error = $('<div class="form-error"></div>');

			$error.text(error);

			var $input = $form.find('[name="'+ name +'"]');

			if ($input.length) {
				$error.insertAfter($input);

				$input.one('focus', function(){
					$(this).next().slideUp(function(){ $(this).remove() });
				});
			} else {
				$form.prepend($error);
			}
		});
	});
})(_localized_form_errors);
