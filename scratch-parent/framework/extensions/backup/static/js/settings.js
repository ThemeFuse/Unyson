jQuery(function ($) {

	$('[data-action=backup-settings]').click(function (event) {

		var modal;
		modal = new fw.OptionsModal({
			title: backup_settings_i10n.title,
			options: backup_settings_i10n.options,
			values: backup_settings_i10n.values,
			size: 'small'
		});

		modal.on('change:values', function (modal, values) {

			// http://api.jquery.com/jQuery.ajax/
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'backup-settings-save',
					values: values
				},
				complete: function () {
					window.location.reload();
				}
			})

		});

		modal.open();

	});

	fwEvents.on('fw:options:init', function (param) {

		$('[data-html-after]').each(function () {
			$(this).after($(this).data('html-after'));
		});

		function update() {
			$(this).closest('[data-container=backup-settings]').toggleClass('disabled', $(this).val() == 'disabled');
		}

		param.$elements.find('[data-type=backup-schedule]').change(update).each(update);

	});

});
