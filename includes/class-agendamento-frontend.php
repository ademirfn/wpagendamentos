<?php

class Agendamento_Frontend {

    public function __construct() {
        add_shortcode('agendamento_disponivel', [$this, 'render_agendamento_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('agendamento-css', AGENDAMENTO_PLUGIN_URL . 'assets/css/agendamento-frontend.css');
        wp_enqueue_script('agendamento-js', AGENDAMENTO_PLUGIN_URL . 'assets/js/agendamento-frontend.js', ['jquery'], null, true);
        
        wp_localize_script('agendamento-js', 'agendamento_ajax', [
            'url' => admin_url('admin-ajax.php'),
        ]);
    }
    

    public function render_agendamento_form($atts) {
        ob_start();
        include AGENDAMENTO_PLUGIN_PATH . 'templates/frontend-agendamento-form.php';
        return ob_get_clean();
    }
    /*
    function adicionar_campo_horario() {
        global $post;
        
        if (get_post_type($post->ID) !== 'product') return;
    
        $horarios = get_post_meta($post->ID, '_horarios_disponiveis', true);
        
        if (!empty($horarios)) {
            echo '<p><label for="horario_agendamento">Escolha um horário:</label>';
            echo '<select name="horario_agendamento" id="horario_agendamento">';
            foreach (explode(',', $horarios) as $horario) {
                echo '<option value="'.trim($horario).'">'.trim($horario).'</option>';
            }
            echo '</select></p>';
        }
    }
    add_action('woocommerce_before_add_to_cart_button', 'adicionar_campo_horario');
    
    function salvar_horario_no_pedido($cart_item_data, $product_id) {
        if (isset($_POST['horario_agendamento'])) {
            $cart_item_data['horario_agendamento'] = sanitize_text_field($_POST['horario_agendamento']);
        }
        return $cart_item_data;
    }
    add_filter('woocommerce_add_cart_item_data', 'salvar_horario_no_pedido', 10, 2);
    
    function exibir_horario_no_checkout($item_data, $cart_item) {
        if (isset($cart_item['horario_agendamento'])) {
            $item_data[] = array(
                'name' => 'Horário Agendado',
                'value' => $cart_item['horario_agendamento']
            );
        }
        return $item_data;
    }
    add_filter('woocommerce_get_item_data', 'exibir_horario_no_checkout', 10, 2);
    */

    
}
