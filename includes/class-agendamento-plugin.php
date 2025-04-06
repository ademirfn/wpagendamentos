<?php

if (!defined('ABSPATH')) {
    exit;
}

// Declaração da classe WC_Product_Agendamento fora da classe Agendamento_Plugin
class WC_Product_Agendamento extends WC_Product {
    public function __construct($product) {
        $this->product_type = 'agendamento';
        parent::__construct($product);
    }
}

class Agendamento_Plugin {

    public function __construct() {
        // Carregar arquivos necessários
        $this->includes();

        // Hooks de inicialização
        add_action('init', [$this, 'init_plugin']);
        add_action('init', [$this, 'adicionar_tipo_produto_agendamento']);

    }

    public function includes() {
        require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-admin.php';
        require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-frontend.php';
        require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-database.php';
    }

    public function init_plugin() {
        // Inicializar módulos
        new Agendamento_Admin();
        new Agendamento_Frontend();
        new Agendamento_Database();

        add_action('wp_ajax_get_agendamentos', [$this, 'get_agendamentos']);
        add_action('wp_ajax_export_google_calendar', [$this, 'export_google_calendar']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_processar_agendamento', [$this, 'processar_agendamento']);
        add_action('wp_ajax_nopriv_processar_agendamento', [$this, 'processar_agendamento']);
    }

    public function export_google_calendar() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'agendamento_disponibilidade';

        $agendamentos = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        $google_calendar_url = 'https://calendar.google.com/calendar/u/0/r/eventedit?';
        foreach ($agendamentos as $agendamento) {
            $google_calendar_url .= 'text=' . urlencode(get_the_title($agendamento['product_id'])) .
                '&dates=' . urlencode($agendamento['data_disponivel'] . 'T' . $agendamento['horario_disponivel']) .
                '&details=' . urlencode('Agendamento gerado pelo sistema') .
                '&location=Online' .
                '&sf=true&output=xml';
        }

        wp_redirect($google_calendar_url);
        exit;
    }

    public function add_admin_menu() {
        add_menu_page(
            'Gerenciar Agendamentos',
            'Agendamentos',
            'manage_options',
            'agendamentos',
            [$this, 'render_agendamento_page'],
            'dashicons-calendar-alt',
            20
        );
    }

    public function render_calendar_page() {
        include AGENDAMENTO_PLUGIN_PATH . 'templates/admin-calendar.php';
    }

    public function processar_agendamento() {
        $data = sanitize_text_field($_POST['data']);
        $hora = sanitize_text_field($_POST['hora']);
        $product_id = intval($_POST['product_id']);

        if (empty($data) || empty($hora) || empty($product_id)) {
            wp_send_json_error(['message' => 'Dados incompletos. Preencha todos os campos.']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'agendamento_disponibilidade';
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE data_disponivel = %s AND horario_disponivel = %s",
                $data,
                $hora
            )
        );

        if ($exists) {
            wp_send_json_error(['message' => 'Horário já reservado. Escolha outro.']);
        }

        $wpdb->insert(
            $table_name,
            [
                'product_id' => $product_id,
                'data_disponivel' => $data,
                'horario_disponivel' => $hora,
            ],
            ['%d', '%s', '%s']
        );

        wp_send_json_success(['message' => 'Agendamento realizado com sucesso!']);
    }

    public function get_agendamentos() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'agendamento_disponibilidade';

        $agendamentos = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        $events = [];
        foreach ($agendamentos as $agendamento) {
            $events[] = [
                'title' => get_the_title($agendamento['product_id']),
                'start' => $agendamento['data_disponivel'] . 'T' . $agendamento['horario_disponivel'],
            ];
        }

        wp_send_json_success($events);
    }

    public function adicionar_tipo_produto_agendamento() {
        // Adiciona o tipo de produto personalizado ao WooCommerce
        add_filter('woocommerce_product_class', function ($classname, $product_type) {
            if ($product_type === 'agendamento') {
                return 'WC_Product_Agendamento';
            }
            return $classname;
        }, 10, 2);

        // Adiciona o tipo de produto à lista de tipos no admin
        add_filter('product_type_selector', function ($types) {
            $types['agendamento'] = __('Agendamento', 'agendamento-otimizado');
            return $types;
        });
    }

    public function registrar_menu_agendamentos() {
        add_menu_page(
            __('Agendamentos', 'agendamento-otimizado'),
            __('Agendamentos', 'agendamento-otimizado'),
            'manage_options',
            'agendamentos',
            [$this, 'render_agendamento_page'],
            'dashicons-calendar-alt',
            6
        );
    }

    public function render_agendamento_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('Agendamentos', 'agendamento-otimizado') . '</h1>';
        echo '<p>' . __('Bem-vindo à página de gerenciamento de agendamentos.', 'agendamento-otimizado') . '</p>';
        echo '</div>';
    }
}