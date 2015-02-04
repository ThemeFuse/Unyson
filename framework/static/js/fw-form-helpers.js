/**
 * FW_Form helpers
 * Dependencies: jQuery
 * Note: You can include this script in frontend (for e.g. to make you contact forms ajax submittable)
 */

var fwForm = {
	/**
	 * @type {Boolean}
	 */
	isAdminPage: function() {
		return typeof ajaxurl != 'undefined'
			&& typeof adminpage != 'undefined'
			&& typeof pagenow != 'undefined'
			&& jQuery(document.body).hasClass('wp-admin');
	},
	/**
	 * Make forms ajax submittable
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
					// Frontend
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
					if (fwForm.isAdminPage()) {
						var html = [],
							typeHtml = [],
							messageClass = '';

						$.each(ajaxData.flash_messages, function(type, messages){
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

							$.each(messages, function(messageId, messageData){
								typeHtml.push('<p>'+ messageData.message +'</p>');
							});

							if (typeHtml.length) {
								html.push(
									'<div class="fw-flash-messages '+ messageClass +'">'+ typeHtml.join('</div><div class="fw-flash-messages '+ messageClass +'">') +'</div>'
								);
							}

							var $pageTitle = jQuery('.wrap h2:first');

							while ($pageTitle.next().is('.fw-flash-message, .fw-flash-messages, .updated, .update-nag, .error')) {
								$pageTitle.next().remove();
							}

							var scrollTop = jQuery(document.body).scrollTop();

							$pageTitle.after(
								'<div class="fw-flash-messages">'+
									html.join('') +
									(scrollTop > 300
										? '<p><a href="#" onclick="jQuery(document.body).animate({scrollTop: '+ scrollTop +'}, 300); jQuery(this).parent().remove();">Go back</a></p>'
										: ''
									)+
								'</div>');

							jQuery(document.body).animate({scrollTop: 0}, 300);
						});
					} else {
						var html = [],
							typeHtml = [];

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

						// prevent multiple submit
						$form.on('submit', function(e){ e.preventDefault(); e.stopPropagation(); });
					}
				}
			}, opts || {}),
			isBusy = false;

		$(document.body).on('submit', opts.selector, function(e){
			e.preventDefault();

			if (isBusy) {
				console.warn('Working... Try again later.')
				return;
			}

			var $form = $(this),
				$submitButton = $form.find('input[type="submit"][name]:focus');

			if (!$submitButton.length) {
				// in case you use this solution http://stackoverflow.com/a/5721762
				$submitButton = $form.find('input[type="submit"][name][clicked]');
			}

			if (!$form.is('form[data-fw-form-id]')) {
				console.error('This is not a FW_Form');
				return;
			}

			opts.hideErrors($form);
			opts.loading(true, $form);
			isBusy = true;

			jQuery.ajax({
				type: "POST",
				url: opts.ajaxUrl,
				data: $form.serialize() + ($submitButton.length ? '&'+ $submitButton.attr('name') +'='+ $submitButton.attr('value') : ''),
				dataType: 'json'
			}).done(function(r){
				isBusy = false;
				opts.loading(false, $form);

				if (r.success) {
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