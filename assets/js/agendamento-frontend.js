jQuery(document).ready(function ($) {
    // Inicializa o Flatpickr no campo de data e hora
    $('.flatpickr-datetime').flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        minDate: "today", // Impede seleção de datas passadas
    });

    $('#agendamento-form').on('submit', function (e) {
        e.preventDefault();

        const data = {
            action: 'processar_agendamento',
            data: $('#data').val(),
            hora: $('#hora').val(),
            product_id: $('#product_id').val()
        };

        $.post(agendamento_ajax.url, data, function (response) {
            if (response.success) {
                $('#agendamento-feedback').text('Agendamento realizado com sucesso!');
            } else {
                $('#agendamento-feedback').text(response.data.message);
            }
        }).fail(function () {
            $('#agendamento-feedback').text('Erro ao processar o agendamento. Tente novamente.');
        });
    });
});
