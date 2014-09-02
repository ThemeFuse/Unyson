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
			frame,
			createFrame = function() {
				frame = wp.media();

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

					elements.$input.val(attachment.id);
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
				});
			};

		elements.$uploadButton.on('click', function(e) {
			e.preventDefault();

			if (!frame) {
				createFrame();
			}
			frame.open();
		});

		elements.$deleteButton.on('click', function(e) {
			elements.$input.val('');
			elements.$textField.text('');
			elements.$uploadButton.text(l10n.buttonAdd);
			elements.$container.addClass('empty');

			fwe.trigger('fw:option-type:upload:clear', {$element: elements.$container});
			elements.$container.trigger('fw:option-type:upload:clear');

			e.preventDefault();
		});
	};

	fwe.on('fw:options:init', function(data) {
		data.$elements
			.find('.fw-option-type-upload.any-files:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);