<?php

if (!defined('ABSPATH')) {
    exit;
}

class Agendamento_Admin {

    public function __construct() {
        // Remove a action duplicada
        // add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('woocommerce_product_options_general_product_data', [$this, 'adicionar_metabox_agendamento']);
        add_action('woocommerce_process_product_meta', [$this, 'salvar_metabox_agendamento']);
    }

    public function register_admin_menu() {
        add_menu_page(
            'Agendamento',
            'Agendamento',
            'manage_options',
            'agendamento-otimizado',
            [$this, 'render_admin_page'],
            'dashicons-calendar-alt'
        );
    }

    public function render_admin_page() {
        echo '<div class="wrap"><h1>Gerenciar Agendamentos</h1>';
        echo '<p>Aqui você pode gerenciar as disponibilidades.</p>';
        // Futuro: Formulário interativo usando FullCalendar
        echo '</div>';
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css');
        wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js', [], null, true);
        wp_enqueue_script('admin-calendar-js', AGENDAMENTO_PLUGIN_URL . 'assets/js/admin-calendar.js', ['jquery', 'fullcalendar-js'], null, true);
    
        wp_localize_script('admin-calendar-js', 'agendamento_ajax', [
            'url' => admin_url('admin-ajax.php'),
        ]);
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

        // Campo para gerenciar estoque virtual
        woocommerce_wp_checkbox([
            'id' => '_manage_stock',
            'label' => __('Gerenciar estoque?', 'agendamento-otimizado'),
            'description' => __('Habilite para gerenciar o estoque dos horários disponíveis', 'agendamento-otimizado'),
        ]);

        // Campo para definir limite de agendamentos
        woocommerce_wp_text_input([
            'id' => '_stock',
            'label' => __('Limite de agendamentos', 'agendamento-otimizado'),
            'desc_tip' => true,
            'description' => __('Número máximo de agendamentos permitidos', 'agendamento-otimizado'),
            'type' => 'number',
            'custom_attributes' => [
                'step' => '1',
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

        // Salva as configurações de estoque
        if (isset($_POST['_manage_stock'])) {
            update_post_meta($post_id, '_manage_stock', 'yes');
        } else {
            update_post_meta($post_id, '_manage_stock', 'no');
        }

        if (isset($_POST['_stock'])) {
            update_post_meta($post_id, '_stock', absint($_POST['_stock']));
        }
    }
}
