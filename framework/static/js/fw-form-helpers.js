/**
 * FW_Form helpers
 * Dependencies: jQuery
 * Note: You can include this script in frontend (for e.g. to make you contact forms ajax submittable)
 */

var fwForm = {
	/**
	 * @param {Object} [opts]
	 */
	initAjaxSubmit: function(opts) {
		var $ = jQuery,
			opts = $.extend({
				selector: 'form[data-fw-form-id]',
				ajaxUrl: '/wp-admin/admin-ajax.php',
				loading: function (show, $form) {
					$form.css('position', 'relative');
					$form.find('> .fw-form-loading').remove();

					if (show) {
						$form.append(
							'<div'+
							' class="fw-form-loading"'+
							' style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.5);"'+
							'></div>'
						);
					}
				},
				showErrors: function ($form, errors) {
					$.each(errors, function(inputName, message) {
						var $input = $form.find('[name="'+ inputName +'"]').last();
						message = '<p class="form-error" style="color: #9b4c4c;">{message}</p>'.replace('{message}', message);

						if ($input.length) {
							// error message under input
							$input.parent().after(message);
						} else {
							// if input not found, show message in form
							$form.prepend(message);
						}
					});
				},
				hideErrors: function ($form) {
					$form.find('.form-error').remove();
				},
				onAjaxError: function(jqXHR, textStatus, errorThrown) {
					console.error(jqXHR, textStatus, errorThrown);
				},
				onSuccess: function ($form, ajaxData) {
					var html = [], typeHtml = [];

					$.each(ajaxData.flash_messages, function(type, messages){
						typeHtml = [];

						$.each(messages, function(messageId, messageData){
							typeHtml.push(messageData.message);
						});

						if (typeHtml.length) {
							html.push(
								'<ul class="flash-messages-'+ type +'">'+
								'    <li>'+ typeHtml.join('</li><li>') +'</li>'+
								'</ul>'
							);
						}
					});

					if (html.length) {
						html = html.join('');
					} else {
						html = '<p>Success</p>';
					}

					$form.fadeOut(function(){
						$form.html(html).fadeIn();
					});
				}
			}, opts || {}),
			isBusy = false;

		$(document.body).on('submit', opts.selector, function(e){
			e.preventDefault();

			if (isBusy) {
				console.warn('Working... Try again later.')
				return;
			}

			var $form = $(this);

			opts.hideErrors($form);
			opts.loading(true, $form);
			isBusy = true;

			jQuery.ajax({
				type: "POST",
				url: opts.ajaxUrl,
				data: $form.serialize(),
				dataType: 'json'
			}).done(function(r){
				isBusy = false;
				opts.loading(false, $form);

				if (r.success) {
					// prevent multiple submit
					$form.on('submit', function(e){ e.preventDefault(); e.stopPropagation(); });

					opts.onSuccess($form, r.data);
				} else {
					opts.showErrors($form, r.data.errors);
				}
			}).fail(function(jqXHR, textStatus, errorThrown){
				isBusy = false;
				opts.loading(false, $form);
				opts.onAjaxError(jqXHR, textStatus, errorThrown);
			});
		});
	}
};

// Usage example
if (false) {
	jQuery(function ($) {
		fwForm.initAjaxSubmit({
			selector: 'form[data-fw-form-id][data-fw-ext-forms-type="contact-forms"]',
			ajaxUrl: ajaxurl || '/wp-admin/admin-ajax.php'
		});
	});
}