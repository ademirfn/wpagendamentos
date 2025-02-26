jQuery(document).ready(function ($) {
    const calendarEl = document.getElementById('agendamento-calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: function (fetchInfo, successCallback, failureCallback) {
            $.post(
                agendamento_ajax.url,
                { action: 'get_agendamentos' },
                function (response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        failureCallback();
                    }
                }
            );
        },
    });

    calendar.render();
});
$('#export-google-calendar').on('click', function () {
    window.open(agendamento_ajax.url + '?action=export_google_calendar', '_blank');
});