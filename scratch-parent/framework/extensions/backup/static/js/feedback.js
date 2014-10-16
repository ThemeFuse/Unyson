jQuery(function ($) {

    var ping_timer_id;
	var backup_container = $('#backup-container');
	var backup_feedback_container = $('#backup-feedback-container');

    // Without it clicking on (x) sometimes doesn't work
    backup_container.on('click', 'a', function () {
        clearTimeout(ping_timer_id);
    });

    function ping() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'backup-feedback',
                subject: backup_feedback_container.data('subject')
            },
            success: function (response, status, xhr) {
                if (response.success) {
                    backup_feedback_container.html(response.data.html);
                    schedule_ping(500);
                }
                else {
                    location.reload();
                }
            },
            error: function (xhr, status, error) {
                schedule_ping(500);
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
