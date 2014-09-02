jQuery(function ($) {

    var backup_restore_container = $('#backup-restore-container');
    var upgrade_button = $('#upgrade');

    if (upgrade_button.length == 0) {

        backup_restore_container.show().focus();
        $('<form method="POST"></form>').insertAfter($('body')).submit();

    }
    else {
        upgrade_button.click(function () {
            backup_restore_container.show().focus();
        });
    }

});
