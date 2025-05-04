<?php

if (!defined('ABSPATH')) {
    exit;
}



class Agendamento_Plugin {
    public function __construct() {
        // Carregar arquivos necessários
        $this->includes();

        // Hooks de inicialização
        add_action('init', [$this, 'init_plugin']);
        add_action('init', [$this, 'adicionar_tipo_produto_agendamento']);

        // Hooks relacionados ao WooCommerce
        add_filter('woocommerce_add_cart_item_data', [$this, 'salvar_horario_no_pedido'], 10, 2);
        add_filter('woocommerce_get_item_data', [$this, 'exibir_horario_no_checkout'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'salvar_horario_no_pedido_meta'], 10, 4);
        add_action('woocommerce_before_order_itemmeta', [$this, 'exibir_horario_no_admin_pedido'], 10, 3);

        // Menu de administração
        add_action('admin_menu', [$this, 'registrar_menu_agendamentos']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Adicione esta linha
        add_action('wp_ajax_get_agendamentos_aprovados', [$this, 'get_agendamentos_aprovados']);
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
        echo '<h1>' . __('Agenda de Atendimentos', 'agendamento-otimizado') . '</h1>';
        
        // Container para o calendário
        echo '<div id="calendario-agendamentos"></div>';
        
        // Adiciona o calendário
        $this->enqueue_calendar_assets();
        
        echo '<style>
            #calendario-agendamentos {
                margin-top: 20px;
                background: #fff;
                padding: 20px;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .fc-event {
                cursor: pointer;
            }
            .fc-event-title {
                font-weight: bold;
            }
            .fc-toolbar-title {
                font-size: 1.5em !important;
            }
        </style>';
        
        echo '</div>';
    }

    private function enqueue_calendar_assets() {
        // Enfileira os assets do FullCalendar
        wp_enqueue_style('flatpickr-css', AGENDAMENTO_PLUGIN_URL . 'assets/css/flatpickr.min.css');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
        wp_enqueue_style('fullcalendar-css', AGENDAMENTO_PLUGIN_URL . 'assets/css/main.min.css');
        wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar/main.min.js', [], null, true);
        wp_enqueue_script('fullcalendar-daygrid', 'https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.js', ['fullcalendar-js'], null, true);
        wp_enqueue_script('fullcalendar-timegrid', 'https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.min.js', ['fullcalendar-js'], null, true);
        wp_enqueue_script('fullcalendar-locale', 'https://cdn.jsdelivr.net/npm/fullcalendar/locales/pt-br.js', ['fullcalendar-js'], null, true);

        // Adiciona o JavaScript personalizado
        wp_add_inline_script('fullcalendar-js', $this->get_calendar_script());
    }

    private function get_calendar_script() {
        ob_start();
        ?>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendario-agendamentos');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br',
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: {
                    url: ajaxurl,
                    method: 'POST',
                    extraParams: {
                        action: 'get_agendamentos_aprovados'
                    }
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    week: 'Semana',
                    day: 'Dia'
                },
                eventClick: function(info) {
                    alert('Agendamento: ' + info.event.title + '\nHorário: ' + info.event.start.toLocaleString());
                }
            });
            calendar.render();
        });
        <?php
        return ob_get_clean();
    }

    // Adicione este método para buscar os agendamentos aprovados
    public function get_agendamentos_aprovados() {
        global $wpdb;
        
        // Busca pedidos com status 'completed' ou 'processing'
        $orders = wc_get_orders([
            'status' => ['completed', 'processing'],
            'limit' => -1
        ]);

        $events = [];
        
        foreach ($orders as $order) {
            foreach ($order->get_items() as $item) {
                $horario_agendado = $item->get_meta('Data e Horário Agendados');
                
                if ($horario_agendado) {
                    // Converte o formato do horário para o FullCalendar
                    $data_hora = DateTime::createFromFormat('d/m/Y H:i', $horario_agendado);
                    
                    if ($data_hora) {
                        $events[] = [
                            'title' => sprintf(
                                'Pedido #%s - %s',
                                $order->get_order_number(),
                                $item->get_name()
                            ),
                            'start' => $data_hora->format('Y-m-d\TH:i:s'),
                            'end' => $data_hora->modify('+1 hour')->format('Y-m-d\TH:i:s'),
                            'backgroundColor' => '#2271b1',
                            'borderColor' => '#2271b1'
                        ];
                    }
                }
            }
        }

        wp_send_json($events);
    }

    public function salvar_horario_no_pedido($cart_item_data, $product_id) {
        if (isset($_POST['horario_agendamento'])) {
            $cart_item_data['horario_agendamento'] = sanitize_text_field($_POST['horario_agendamento']);
        }
        return $cart_item_data;
    }

    public function exibir_horario_no_checkout($item_data, $cart_item) {
        if (isset($cart_item['horario_agendamento'])) {
            $item_data[] = [
                'name' => __('Data e Horário Agendados', 'agendamento-otimizado'),
                'value' => $cart_item['horario_agendamento'],
            ];
        }
        return $item_data;
    }

    public function salvar_horario_no_pedido_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['horario_agendamento'])) {
            $item->add_meta_data(__('Data e Horário Agendados', 'agendamento-otimizado'), $values['horario_agendamento'], true);
        }
    }

    public function exibir_horario_no_admin_pedido($item_id, $item, $product) {
        if ($item->get_meta('Data e Horário Agendados')) {
            echo '<p><strong>' . __('Data e Horário Agendados', 'agendamento-otimizado') . ':</strong> ' . esc_html($item->get_meta('Data e Horário Agendados')) . '</p>';
        }
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

        // Adiciona metabox personalizada para produtos do tipo "Agendamento"
        add_action('woocommerce_product_options_general_product_data', [$this, 'adicionar_metabox_agendamento']);
        add_action('woocommerce_process_product_meta', [$this, 'salvar_metabox_agendamento']);
    }

    public function adicionar_metabox_agendamento() {
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
                        // Formata os horários de início e fim
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

    public function salvar_metabox_agendamento($post_id) {
        // Salva o tipo de agendamento
        if (isset($_POST['_tipo_agendamento'])) {
            update_post_meta($post_id, '_tipo_agendamento', sanitize_text_field($_POST['_tipo_agendamento']));
        }

        // Salva os dias e horários disponíveis
        if (isset($_POST['_dias_horarios_disponiveis'])) {
            $dias_horarios = array_map('sanitize_text_field', explode(',', $_POST['_dias_horarios_disponiveis']));
            update_post_meta($post_id, '_dias_horarios_disponiveis', $dias_horarios);
        }
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

    public function enqueue_admin_assets() {
        // Enfileira os assets do FullCalendar
        wp_enqueue_style('flatpickr-css', AGENDAMENTO_PLUGIN_URL . 'assets/css/flatpickr.min.css');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
        wp_enqueue_style('fullcalendar-css', AGENDAMENTO_PLUGIN_URL . 'assets/css/main.min.css');
        wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar/main.min.js', [], null, true);
        wp_enqueue_script('fullcalendar-daygrid', 'https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.js', ['fullcalendar-js'], null, true);
        wp_enqueue_script('fullcalendar-timegrid', 'https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.min.js', ['fullcalendar-js'], null, true);
        wp_enqueue_script('fullcalendar-locale', 'https://cdn.jsdelivr.net/npm/fullcalendar/locales/pt-br.js', ['fullcalendar-js'], null, true);

        // Adiciona o JavaScript personalizado
        wp_add_inline_script('fullcalendar-js', $this->get_calendar_script());
    }

    public function exibir_dias_horarios_disponiveis($product_id) {
        $dias_horarios = get_post_meta($product_id, '_dias_horarios_disponiveis', true);

        if (!empty($dias_horarios)) {
            echo '<p><strong>' . __('Dias e Horários Disponíveis:', 'agendamento-otimizado') . '</strong></p>';
            echo '<ul>';
            $horarios = explode(',', $dias_horarios);
            foreach ($horarios as $horario) {
                echo '<li>' . esc_html($horario) . '</li>';
            }
            echo '</ul>';
        }
    }
}