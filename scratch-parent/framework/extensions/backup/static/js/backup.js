jQuery(function ($) {

    var templ = '<p><button class="button" data-action="backup-restore" disabled="disabled">Restore Backup</button></p>';
    var table = $('#posts-filter');

    // Insert Restore button before and after table with posts
    if (table.is(':visible')) {
        $(templ).insertBefore(table);
        $(templ).insertAfter(table);
    }

    $('[data-action=backup-spinner]').click(function (event) {
        // Do not allow to click on *Backup Now* button twice
        if ($(this).prop('disabled')) {
            event.preventDefault();
        }
        else {
	        // Disable all *Backup Now* buttons
	        $('[data-action=backup-spinner]').prop('disabled', true).attr('disabled', true);
	        // And display spinner
            $('<span>&nbsp;<i class="backup-spinner spinner"></i></span>').appendTo($(this).parent());
        }
    });

    $('[data-action=backup-restore]').click(function () {
        $('[data-action=backup-restore]').prop('disabled', true);
        var url = $('[name=backup-radio]:checked').val();
        if (url) {
            window.location = url;
        }
    });

    $('[name=backup-radio]').change(function () {
        $('[data-action=backup-restore]').prop('disabled', $('[name=backup-radio]:checked').val() === undefined);
    }).first().change();

    setTimeout(function () {
        var subtitle = $('#backup-subtitle');
        subtitle.insertAfter(subtitle.closest('.wrap').children('h2').first());
    }, 1);

});
