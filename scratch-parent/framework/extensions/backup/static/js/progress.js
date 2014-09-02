jQuery(function ($) {

    var ping_timer_id;
    var backup_progress_container = $('#backup-progress-container');

    // Without it clicking on (x) sometimes doesn't work
    backup_progress_container.on('click', 'a', function () {
        clearTimeout(ping_timer_id);
    });

    function ping() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'backup-progress',
                post: backup_progress_container.data('post')
            },
            success: function (response, status, xhr) {
                if (response.success) {
                    backup_progress_container.html(response.data);
                    schedule_ping(100);
                }
                else {
                    location.reload();
                }
            },
            error: function (xhr, status, error) {
                schedule_ping(250);
            },
            complete: function (xhr, status) {
            }
        });
    }

    function schedule_ping(timeout) {
        clearTimeout(ping_timer_id);
        ping_timer_id = setTimeout(ping, timeout);
    }

    ping();

});
