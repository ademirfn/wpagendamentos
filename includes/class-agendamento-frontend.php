<?php

class Agendamento_Frontend {

    public function __construct() {
        // Registrar shortcode e enfileirar assets
        add_shortcode('agendamento_disponivel', [$this, 'render_agendamento_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Hooks do WooCommerce
        add_action('woocommerce_before_add_to_cart_button', [$this, 'adicionar_campo_horario']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'salvar_horario_no_pedido'], 10, 2);
        add_filter('woocommerce_get_item_data', [$this, 'exibir_horario_no_checkout'], 10, 2);
    }

    /**
     * Enfileira scripts e estilos
     */
    public function enqueue_assets() {
        wp_enqueue_style('agendamento-css', AGENDAMENTO_PLUGIN_URL . 'assets/css/agendamento-frontend.css');
        wp_enqueue_script('agendamento-js', AGENDAMENTO_PLUGIN_URL . 'assets/js/agendamento-frontend.js', ['jquery'], null, true);

        // Localiza o script para usar AJAX
        wp_localize_script('agendamento-js', 'agendamento_ajax', [
            'url' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Renderiza o formulário de agendamento via shortcode
     */
    public function render_agendamento_form($atts) {
        ob_start();
        include AGENDAMENTO_PLUGIN_PATH . 'templates/frontend-agendamento-form.php';
        return ob_get_clean();
    }

    /**
     * Adiciona um campo de seleção de horário na página do produto
     */
    public function adicionar_campo_horario() {
        global $post;

        // Verifica se é um produto
        if (get_post_type($post->ID) !== 'product') return;

        // Obtém os horários disponíveis
        $horarios = get_post_meta($post->ID, '_horarios_disponiveis', true);

        if (!empty($horarios)) {
            echo '<p><label for="horario_agendamento">Escolha um horário:</label>';
            echo '<select name="horario_agendamento" id="horario_agendamento">';
            foreach (explode(',', $horarios) as $horario) {
                echo '<option value="' . trim($horario) . '">' . trim($horario) . '</option>';
            }
            echo '</select></p>';
        }
    }

    /**
     * Salva o horário selecionado nos dados do item do carrinho
     */
    public function salvar_horario_no_pedido($cart_item_data, $product_id) {
        if (isset($_POST['horario_agendamento'])) {
            $cart_item_data['horario_agendamento'] = sanitize_text_field($_POST['horario_agendamento']);
        }
        return $cart_item_data;
    }

    /**
     * Exibe o horário agendado no checkout
     */
    public function exibir_horario_no_checkout($item_data, $cart_item) {
        if (isset($cart_item['horario_agendamento'])) {
            $item_data[] = array(
                'name' => 'Horário Agendado',
                'value' => $cart_item['horario_agendamento']
            );
        }
        return $item_data;
    }
}