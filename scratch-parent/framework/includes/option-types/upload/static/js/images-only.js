(function($, _, fwe) {

	var init = function() {
		var $this = $(this),
			elements = {
				$container: $this,
				$input: $this.find('input[type="hidden"]'),
				$uploadButton: $this.find('p a'),
				$thumb: $this.find('.thumb')
			},
			templates = {
				thumb: {
					empty: $this.find('.thumb-template-empty').attr('data-template'),
					notEmpty: $this.find('.thumb-template-not-empty').attr('data-template')
				}
			},
			l10n = {
				buttonAdd: elements.$container.attr('data-l10n-button-add'),
				buttonEdit: elements.$container.attr('data-l10n-button-edit')
			},
			frame,
			createFrame = function() {
				frame = wp.media({
					library: {
						type: 'image'
					}
				});

				frame.on('ready', function() {
					frame.modal.$el.addClass('fw-option-type-upload');
				});

				// opens the modal with the correct attachment selected
				frame.on('open', function() {
					var selection = frame.state().get('selection'),
						attatchmentId = elements.$input.val(),
						attachment = wp.media.attachment(attatchmentId);

					frame.reset();
					if (attachment.id) {
						selection.add(attachment);
					}
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first(),

						// if the image is large enough it will
						// have a 'thumbnail' size and we display the thumb
						// if it isn't then we display the full image
						url = attachment.get('sizes').thumbnail
								? attachment.get('sizes').thumbnail.url
								: attachment.get('sizes').full.url,

						filename = attachment.get('filename'),
						compiled = _.template(
							templates.thumb.notEmpty,
							{src: url, alt: filename},
							{variable: 'data'}
						);

					elements.$input.val(attachment.id);
					elements.$uploadButton.text(l10n.buttonEdit);
					elements.$thumb
								.html(compiled)
								.attr({
									'data-attid': attachment.id,
									'data-origsrc': attachment.get('url')
								});
					elements.$container.removeClass('empty');

					fwe.trigger('fw:option-type:upload:change', {
						$element: elements.$container,
						attachment: attachment
					});
					elements.$container.trigger('fw:option-type:upload:change', {
						attachment: attachment
					});
				});
			};

		elements.$uploadButton.on('click', function(e) {
			e.preventDefault();

			if (!frame) {
				createFrame();
			}
			frame.open();
		});

		elements.$container.on('click', '.no-image-img', function() {
			elements.$uploadButton.trigger('click');
		});

		elements.$thumb.on('click', '.clear-uploads-thumb', function(e) {
			elements.$input.val('');
			elements.$uploadButton.text(l10n.buttonAdd);
			elements.$thumb
						.html(templates.thumb.empty)
						.removeAttr('data-attid data-origsrc');
			elements.$container.addClass('empty');

			fwe.trigger('fw:option-type:upload:clear', {$element: elements.$container});
			elements.$container.trigger('fw:option-type:upload:clear');

			e.preventDefault();
		});
	};

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-upload.images-only:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, _, fwEvents);
