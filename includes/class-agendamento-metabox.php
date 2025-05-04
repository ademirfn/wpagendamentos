<?php
function adicionar_metabox_agendamento() {
    add_meta_box(
        'agendamento_disponibilidade',
        'Datas e Horários Disponíveis',
        'renderizar_metabox_agendamento',
        'product',
        'side'
    );
}
add_action('add_meta_boxes', 'adicionar_metabox_agendamento');

function renderizar_metabox_agendamento($post) {
    global $post;

    echo '<div class="options_group">';

    // Campo para selecionar o tipo de agendamento
    woocommerce_wp_select([
        'id' => '_tipo_agendamento',
        'label' => __('Tipo de Agendamento', 'agendamento-otimizado'),
        'options' => [
            'dia_inteiro' => __('Dia Inteiro', 'agendamento-otimizado'),
            'horarios' => __('Horários em Determinado Dia', 'agendamento-otimizado'),
        ],
    ]);

    // Campo para definir os dias e horários disponíveis
    echo '<p class="form-field">
        <label for="_dias_horarios_disponiveis">' . __('Dias e Horários Disponíveis', 'agendamento-otimizado') . '</label>
        <input type="text" id="_dias_horarios_disponiveis" name="_dias_horarios_disponiveis" class="flatpickr-datetime" placeholder="' . __('Selecione os dias e horários', 'agendamento-otimizado') . '">
        <span class="description">' . __('Selecione os dias e horários disponíveis no formato: dd/mm/yyyy - 00:00 - 12:00.', 'agendamento-otimizado') . '</span>
    </p>';

    // Campo para definir o preço do agendamento
    woocommerce_wp_text_input([
        'id' => '_preco_agendamento',
        'label' => __('Preço do Agendamento', 'agendamento-otimizado'),
        'description' => __('Defina o preço para cada agendamento.', 'agendamento-otimizado'),
        'desc_tip' => true,
        'type' => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min' => '0',
        ],
    ]);

    // Exibe os horários já salvos
    $dias_horarios_salvos = get_post_meta($post->ID, '_dias_horarios_disponiveis', true);

    if (!empty($dias_horarios_salvos)) {
        echo '<div id="horarios-disponiveis-lista">';
        echo '<h4>' . __('Horários já disponibilizados:', 'agendamento-otimizado') . '</h4>';
        echo '<ul>';
        foreach ($dias_horarios_salvos as $horario) {
            echo '<li>' . esc_html($horario) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    echo '</div>';

    // Script para inicializar o Flatpickr
    echo '<script>
        jQuery(document).ready(function($) {
            $(".flatpickr-datetime").flatpickr({
                enableTime: true,
                dateFormat: "d/m/Y H:i",
                mode: "range",
                time_24hr: true,
                minDate: "today",
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const start = selectedDates[0];
                        const end = selectedDates[1];
                        const formatted = instance.formatDate(start, "d/m/Y H:i") + " - " + instance.formatDate(end, "H:i");
                        instance.input.value = formatted;
                    }
                }
            });
        });
    </script>';
}

function salvar_metabox_agendamento($post_id) {
    // Salva o tipo de agendamento
    if (isset($_POST['_tipo_agendamento'])) {
        update_post_meta($post_id, '_tipo_agendamento', sanitize_text_field($_POST['_tipo_agendamento']));
    }

    // Salva os dias e horários disponíveis
    if (isset($_POST['_dias_horarios_disponiveis'])) {
        $dias_horarios = array_map('sanitize_text_field', explode(',', $_POST['_dias_horarios_disponiveis']));
        update_post_meta($post_id, '_dias_horarios_disponiveis', $dias_horarios);
    }

    // Salva o preço do agendamento
    if (isset($_POST['_preco_agendamento'])) {
        update_post_meta($post_id, '_preco_agendamento', floatval($_POST['_preco_agendamento']));
    }
}
add_action('save_post', 'salvar_metabox_agendamento');
