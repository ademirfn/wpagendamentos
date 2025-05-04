<?php

if (!defined('ABSPATH')) {
    exit;
}

class Agendamento_Admin {

    public function __construct() {
        add_action('woocommerce_product_options_general_product_data', [$this, 'adicionar_metabox_agendamento']);
        add_action('woocommerce_process_product_meta', [$this, 'salvar_metabox_agendamento']);
    }

    public function adicionar_metabox_agendamento() {
        global $post;

        echo '<div class="options_group">';

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

        echo '</div>';
    }

    public function salvar_metabox_agendamento($post_id) {
        // Salva o preço do agendamento
        if (isset($_POST['_preco_agendamento'])) {
            update_post_meta($post_id, '_preco_agendamento', floatval($_POST['_preco_agendamento']));
        }
    }
}
