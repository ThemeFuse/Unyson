/**
 * FW_Form helpers
 * Dependencies: jQuery
 * Note: You can include this script in frontend (for e.g. to make you contact forms ajax submittable)
 */

var fwForm = {
	/**
	 * Make forms ajax submittable
	 * @param {Object} [opts] You can overwrite any
	 */
	initAjaxSubmit: function(opts) {
		var opts = jQuery.extend({
			selector: 'form[data-fw-form-id]',
			ajaxUrl: (typeof ajaxurl != 'undefined')
				? ajaxurl
				: ((typeof fwAjaxUrl != 'undefined')
					? fwAjaxUrl // wp_localize_script('fw-form-helpers', 'fwAjaxUrl', admin_url( 'admin-ajax.php', 'relative' ));
					: '/wp-admin/admin-ajax.php'
				),
			loading: function (elements, show) {
				elements.$form.css('position', 'relative');
				elements.$form.find('> .fw-form-loading').remove();

				if (show) {
					elements.$form.append(
						'<div'+
						' class="fw-form-loading"'+
						' style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.1);"'+
						'></div>'
					);
				}
			},
			afterSubmitDelay: function(elements){},
			onErrors: function (elements, data) {
				if (isAdmin) {
					fwForm.backend.showFlashMessages(
						fwForm.backend.renderFlashMessages({error: data.errors})
					);
				} else {
					// Frontend
					jQuery.each(data.errors, function (inputName, message) {
						message = '<p class="form-error" style="color: #9b2922;">{message}</p>'
							.replace('{message}', message);

						var $input = elements.$form.find('[name="' + inputName + '"]').last();

						if (!$input.length) {
							// maybe input name has array format, try to find by prefix: name[
							$input = elements.$form.find('[name^="'+ inputName +'["]').last();
						}

						if ($input.length) {
							// error message under input
							$input.parent().after(message);
						} else {
							// if input not found, show message in form
							elements.$form.prepend(message);
						}
					});
				}
			},
			hideErrors: function (elements) {
				elements.$form.find('.form-error').remove();
			},
			onAjaxError: function(elements, data) {
				console.error(data.jqXHR, data.textStatus, data.errorThrown);
				alert('Ajax error (more details in console)');
			},
			onSuccess: function (elements, ajaxData) {
				if (isAdmin) {
					fwForm.backend.showFlashMessages(
						fwForm.backend.renderFlashMessages(ajaxData.flash_messages)
					);
				} else {
					var html = fwForm.frontend.renderFlashMessages(ajaxData.flash_messages);

					if (!html.length) {
						html = '<p>Success</p>';
					}

					elements.$form.fadeOut(function(){
						elements.$form.html(html).fadeIn();
					});

					// prevent multiple submit
					elements.$form.on('submit', function(e){ e.preventDefault(); e.stopPropagation(); });
				}
			}
		}, opts || {}),
		isAdmin = (typeof adminpage != 'undefined' && jQuery(document.body).hasClass('wp-admin')),
		isBusy = false;

		jQuery(document.body).on('submit', opts.selector, function(e){
			e.preventDefault();

			if (isBusy) {
				console.warn('Working... Try again later.');
				return;
			}

			var $form = jQuery(this);

			if (!$form.is('form[data-fw-form-id]')) {
				console.error('This is not a FW_Form', 'Selector:'. opts.selector, 'Form:', $form);
				return;
			}

			// get submit button
			{
				var $submitButton = $form.find(':submit:focus');

				if (!$submitButton.length) {
					// in case you use this solution http://stackoverflow.com/a/5721762
					$submitButton = $form.find('[clicked]:submit');
				}

				// make sure to remove the "clicked" attribute to prevent accidental settings reset
				$form.find('[clicked]:submit').removeAttr('clicked');
			}

			var elements = {
				$form: $form,
				$submitButton: $submitButton
			};

			opts.hideErrors(elements);

			var delaySubmit = parseInt(
				opts.loading(
					elements,
					/**
					 * If you want to submit your ajaxified Theme Settings form without
					 * any notification for the user add class fw-silent-submit to
					 * the form element itself. This class will be removed
					 * automatically after this particular submit, so that popup will
					 * show when the user will press Submit button next time.
					 */
					! $form.hasClass('fw-silent-submit')
				)
			);
			delaySubmit = (isNaN(delaySubmit) || delaySubmit < 0) ? 0 : delaySubmit;

			$form.removeClass('fw-silent-submit');

			isBusy = true;

			setTimeout(function(){
				if (delaySubmit) {
					opts.afterSubmitDelay(elements);
				}

				jQuery.ajax({
					type: "POST",
					url: opts.ajaxUrl,
					data: $form.serialize() + (
						$submitButton.length
						? '&'+ $submitButton.attr('name') +'='+ $submitButton.attr('value')
						: ''
					),
					dataType: 'json'
				}).done(function(r){
					isBusy = false;
					opts.loading(elements, false);

					if (r.success) {
						opts.onSuccess(elements, r.data);
					} else {
						opts.onErrors(elements, r.data);
					}
				}).fail(function(jqXHR, textStatus, errorThrown){
					isBusy = false;
					opts.loading(elements, false);
					opts.onAjaxError(elements, {
						jqXHR: jqXHR,
						textStatus: textStatus,
						errorThrown: errorThrown
					});
				});
			}, delaySubmit);
		});
	},
	backend: {
		showFlashMessages: function(messagesHtml) {
			var $pageTitle = jQuery('.wrap h2:first');

			while ($pageTitle.next().is('.fw-flash-messages, .fw-flash-message, .updated, .update-nag, .error')) {
				$pageTitle.next().remove();
			}

			$pageTitle.after('<div class="fw-flash-messages">'+ messagesHtml +'</div>');

			jQuery(document.body).animate({scrollTop: 0}, 300);
		},
		/**
		 * Html structure should be the same as generated by FW_Flash_Messages::_print_backend()
		 * @param {Object} flashMessages
		 * @returns {string}
		 */
		renderFlashMessages: function(flashMessages) {
			var html = [],
				typeHtml = [],
				messageClass = '';

			jQuery.each(flashMessages, function(type, messages){
				typeHtml = [];

				switch (type) {
					case 'error':
						messageClass = 'error';
						break;
					case 'warning':
						messageClass = 'update-nag';
						break;
					default:
						messageClass = 'updated';
				}

				jQuery.each(messages, function(messageId, message){
					typeHtml.push('<div class="'+ messageClass +' fw-flash-message"><p>'+ message +'</p></div>');
				});

				if (typeHtml.length) {
					html.push(
						'<div class="fw-flash-type-'+ type +'">'+ typeHtml.join('</div><div class="fw-flash-type-'+ type +'">') +'</div>'
					);
				}
			});

			return html.join('');
		}
	},
	frontend: {
		/**
		 * Html structure is the same as generated by FW_Flash_Messages::_print_frontend()
		 * @param {Object} flashMessages
		 * @returns {string}
		 */
		renderFlashMessages: function(flashMessages) {
			var html = [],
				typeHtml = [],
				messageClass = '';

			jQuery.each(flashMessages, function(type, messages){
				typeHtml = [];

				jQuery.each(messages, function(messageId, message){
					typeHtml.push('<li class="fw-flash-message">'+ message +'</li>');
				});

				if (typeHtml.length) {
					html.push(
						'<ul class="fw-flash-type-'+ type +'">'+ typeHtml.join('</ul><ul class="fw-flash-type-'+ type +'">') +'</ul>'
					);
				}
			});

			return html.join('');
		}
	}
};

// Usage example
if (false) {
	jQuery(function ($) {
		fwForm.initAjaxSubmit({
			selector: 'form[data-fw-form-id][data-fw-ext-forms-type="contact-forms"]',
			ajaxUrl: ajaxurl
		});
	});
}
