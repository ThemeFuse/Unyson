(function($, fwe) {

	var init = function() {
		var $this = $(this),
			elements = {
				$container: $this,
				$input: $this.find('input[type="hidden"]'),
				$uploadButton: $this.find('button'),
				$deleteButton: $this.find('a'), // it 'clears' the input
				$textField: $this.find('em') // for the name of the attachment
			},
			l10n = {
				buttonAdd: elements.$container.attr('data-l10n-button-add'),
				buttonEdit: elements.$container.attr('data-l10n-button-edit')
			},
			frame;

		var haveFilesDetails = elements.$container.attr('data-files-details') !== undefined;

		if (haveFilesDetails) {
			var parsedFilesDetails = JSON.parse(elements.$container.attr('data-files-details'));
		}

		var createFrame = function() {
			var frameOpts = haveFilesDetails ?
			{
				library: {
					type: parsedFilesDetails.mime_types
				}
			} : {};

			frame = wp.media(frameOpts);

			if (haveFilesDetails) {
				frame.on('content:render', function () {
					var $view = this.first().frame.views.get('.media-frame-uploader')[0];

					if (parsedFilesDetails.extra_mime_types.length > 0  && _.isArray(parsedFilesDetails.extra_mime_types)) {
						_.each(parsedFilesDetails.extra_mime_types, function(mime_type){
							mOxie.Mime.addMimeType(mime_type);
						});
					}

					$view.options.uploader.plupload = {
						filters: {
							mime_types: [
								{
									title: 'Files : '+parsedFilesDetails.ext_files.join(','),
									extensions: parsedFilesDetails.ext_files.join(',')
								}
							]
						}
					};
				});
			}

				frame.on('ready', function() {
					frame.modal.$el.addClass('fw-option-type-upload');
				});

				// opens the modal with the correct attachment already selected
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
					var attachment = frame.state().get('selection').first();
					elements.$input
						.val(attachment.id)
						.trigger('change'); // trigger Customizer update
					performSelection(attachment);
				});
			};

		elements.$uploadButton.on('click', function(e) {
			e.preventDefault();

			if (! frame) {
				createFrame();
			}

			frame.open();
		});

		elements.$deleteButton.on('click', function(e) {
			clearAttachment();
			elements
				.$input.val('')
				.trigger('change'); // trigger Customizer update
			e.preventDefault();
		});

		elements.$input.on('change', function () {
			if (! $(this).val()) {
				clearAttachment();
				return;
			}

			var attachment = wp.media.attachment($(this).val());

			if (! attachment.get('url')) {
				attachment.fetch().then(function (data) {
					performSelection(attachment);
				});

				return;
			}

			performSelection(attachment);
		})

		function clearAttachment () {
			elements.$textField.text('');
			elements.$uploadButton.text(l10n.buttonAdd);
			elements.$container.addClass('empty');

			fwe.trigger('fw:option-type:upload:clear', {$element: elements.$container});
			elements.$container.trigger('fw:option-type:upload:clear');
		}

		function performSelection (attachment) {
			elements.$textField.text(attachment.get('filename'));
			elements.$uploadButton.text(l10n.buttonEdit);

			elements.$container.removeClass('empty');

			fwe.trigger('fw:option-type:upload:change', {
				$element: elements.$container,
				attachment: attachment
			});

			elements.$container.trigger('fw:option-type:upload:change', {
				attachment: attachment
			});
		}
	};

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-upload.any-files:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);
