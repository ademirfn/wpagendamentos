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
    $horarios = get_post_meta($post->ID, '_horarios_disponiveis', true);
    ?>
    <label for="horarios_disponiveis">Horários disponíveis (separados por vírgula):</label>
    <input type="text" name="horarios_disponiveis" id="horarios_disponiveis" value="<?php echo esc_attr($horarios); ?>" style="width: 100%;" />
    <?php
}

function salvar_metabox_agendamento($post_id) {
    if (isset($_POST['horarios_disponiveis'])) {
        update_post_meta($post_id, '_horarios_disponiveis', sanitize_text_field($_POST['horarios_disponiveis']));
    }
}
add_action('save_post', 'salvar_metabox_agendamento');
