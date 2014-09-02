jQuery(function ($) {

    $('[data-action=backup-settings]').click(function (event) {

        event.preventDefault();

        var modal = new fw.OptionsModal({
            title: 'Backup Schedule',
            options: $(this).data('options'),
            values: $(this).data('values'),
            size: 'small'
        });

        modal.on('change:values', function(modal, values) {

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
        param.$elements.find('[data-type=backup-schedule]').change(function () {
            $(this).closest('[data-container=backup-settings]').toggleClass('disabled', $(this).val() == 'disabled');
        }).change();
    });

});
