(function ($, fwe) {

	var init = function () {
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
				buttonEdit: elements.$container.attr('data-l10n-button-edit'),
				filesOne: elements.$container.attr('data-l10n-files-one'),
				filesMore: elements.$container.attr('data-l10n-files-more')
			},
			frame;

		var haveFilesDetails = elements.$container.attr('data-files-details') !== undefined;

		if (haveFilesDetails) {
			var parsedFilesDetails = JSON.parse(elements.$container.attr('data-files-details'));
		}
		var createFrame = function () {
			var frameOpts = haveFilesDetails ?
			{
				library: {
					type: parsedFilesDetails.mime_types
				},
				multiple: true
			} : {
				multiple: true
			};

			frame = wp.media(frameOpts);

			if (haveFilesDetails) {
				frame.on('content:render', function () {
					var $view = this.first().frame.views.get('.media-frame-uploader')[0];

					if(parsedFilesDetails.extra_mime_types.length > 0  && _.isArray(parsedFilesDetails.extra_mime_types)){
						_.each(parsedFilesDetails.extra_mime_types, function(mime_type){
							mOxie.Mime.addMimeType(mime_type);
						});
					}

					$view.options.uploader.plupload = {
						filters: {
							mime_types: [
								{
									title: 'Files : ' + parsedFilesDetails.ext_files.join(','),
									extensions: parsedFilesDetails.ext_files.join(',')
								}
							]
						}
					};
				});
			}

			frame.on('ready', function () {
				frame.modal.$el.addClass('fw-option-type-multi-upload');
			});

			// opens the modal with the correct attachments already selected
			frame.on('open', function () {
				var selection = frame.state().get('selection'),
					ids = elements.$input.val(),
					parsedIds,
					attachment;

				frame.reset();
				try {
					parsedIds = JSON.parse(ids);
					$.each(parsedIds, function (index, id) {
						attachment = wp.media.attachment(id);
						if (attachment.id) {
							selection.add(attachment);
						}
					});
				} catch (e) {

				}
			});

			frame.on('select', function () {
				var attachments = frame.state().get('selection'),
					ids = attachments.map(function (attachment) {
						return attachment.id;
					}),
					selectedText = ids.length === 1
						? l10n.filesOne
						: l10n.filesMore.replace('%u', ids.length);

				elements.$input.val(JSON.stringify(ids));
				elements.$textField.text(selectedText);
				elements.$uploadButton.text(l10n.buttonEdit);
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

		elements.$uploadButton.on('click', function (e) {
			e.preventDefault();

			if (!frame) {
				createFrame();
			}
			frame.open();
		});

		elements.$deleteButton.on('click', function (e) {
			elements.$input.val('[]');
			elements.$textField.text('');
			elements.$uploadButton.text(l10n.buttonAdd);
			elements.$container.addClass('empty');

			fwe.trigger('fw:option-type:multi-upload:clear', {$element: elements.$container});
			elements.$container.trigger('fw:option-type:multi-upload:clear');

			e.preventDefault();
		});
	};

	fwe.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-multi-upload.any-files:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);