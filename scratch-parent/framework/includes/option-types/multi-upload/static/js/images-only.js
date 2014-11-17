(function($, _, fwe) {

	var init = function() {
		var $this = $(this),
			elements = {
				$container: $this,
				$input: $this.find('input[type="hidden"]'),
				$uploadButton: $this.find('p a'),
				$thumbsContainer: $this.find('.thumbs-container')
			},
			templates = {
				thumb: {
					empty: $this.find('.thumb-template-empty').attr('data-template'),
					notEmpty: $this.find('.thumb-template-not-empty').attr('data-template')
				}
			},
			collectThumbsIds = function() {
				var ids = [];
				elements.$thumbsContainer.find('.thumb').each(function() {
					ids.push($(this).data('attid'));
				});
				return ids;
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
					},
					multiple: true
				});

				frame.on('ready', function() {
					frame.modal.$el.addClass('fw-option-type-multi-upload');
				});

				// opens the modal with the correct attachments already selected
				frame.on('open', function() {
					var selection = frame.state().get('selection'),
						ids = elements.$input.val(),
						parsedIds,
						attachment;

					frame.reset();
					try {
						parsedIds = JSON.parse(ids);
						$.each(parsedIds, function(index, id) {
							attachment = wp.media.attachment(id);
							if (attachment.id) {
								selection.add(attachment);
							}
						});
					} catch(e) {

					}
				});

				frame.on('select', function() {
					var attachments = frame.state().get('selection'),
						ids = [],
						compiledTemplates = [];

					attachments.each(function(attachment) {
						ids.push(attachment.id);
						compiledTemplates.push(_.template(
							templates.thumb.notEmpty,
							{
								// if the image is large enough it will
								// have a 'thumbnail' size and we display the thumb
								// if it isn't then we display the full image
								src: attachment.get('sizes').thumbnail
										? attachment.get('sizes').thumbnail.url
										: attachment.get('sizes').full.url,
								alt: attachment.get('filename'),
								id: attachment.id,
								originalSrc: attachment.get('url')
							},
							{variable: 'data'}
						));
					});

					elements.$input.val(JSON.stringify(ids));
					elements.$uploadButton.text(l10n.buttonEdit);
					elements.$thumbsContainer.html(compiledTemplates.join(''));
					elements.$container.removeClass('empty');

					fwe.trigger('fw:option-type:multi-upload:change', {
						$element: elements.$container,
						attachments: attachments
					});
					elements.$container.trigger('fw:option-type:multi-upload:change', {
						attachments: attachments
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

		elements.$thumbsContainer.on('click', '.clear-uploads-thumb', function(e) {
			var ids;

			$(this).closest('.thumb').remove();
			ids = collectThumbsIds();

			if (ids.length) {
				elements.$input.val(JSON.stringify(ids));
				fwe.trigger('fw:option-type:multi-upload:remove', {$element: elements.$container}); // TODO: think what other data would be usefull
				elements.$container.trigger('fw:option-type:multi-upload:remove'); // TODO: think what other data would be usefull
			} else {
				elements.$input.val('[]');
				elements.$uploadButton.text(l10n.buttonAdd);
				elements.$thumbsContainer.html(templates.thumb.empty);
				elements.$container.addClass('empty');
				fwe.trigger('fw:option-type:multi-upload:clear', {$element: elements.$container});
				elements.$container.trigger('fw:option-type:multi-upload:clear');
			}

			e.preventDefault();
		});

		elements.$thumbsContainer.sortable({
			cancel: '.no-image',
			update: function() {
				var ids = collectThumbsIds();
				elements.$input.val(JSON.stringify(ids));
			}
		});
	};

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-multi-upload.images-only:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, _, fwEvents);