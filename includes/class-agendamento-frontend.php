<?php

if (!defined('ABSPATH')) {
    exit;
}

class Agendamento_Frontend {

    public function __construct() {
        // Registrar shortcode e enfileirar assets
        add_shortcode('agendamento_disponivel', [$this, 'render_agendamento_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Remove hooks duplicados
        add_action('woocommerce_single_product_summary', [$this, 'adicionar_campo_horario'], 15);
        add_filter('woocommerce_add_cart_item_data', [$this, 'salvar_horario_no_pedido'], 10, 2);
        add_filter('woocommerce_get_item_data', [$this, 'exibir_horario_no_checkout'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'salvar_horario_no_pedido_meta'], 10, 4);
    }

    /**
     * Enfileira scripts e estilos
     */
    public function enqueue_assets() {
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);

        wp_enqueue_style('agendamento-css', AGENDAMENTO_PLUGIN_URL . 'assets/css/agendamento-frontend.css');
        wp_enqueue_script('agendamento-js', AGENDAMENTO_PLUGIN_URL . 'assets/js/agendamento-frontend.js', ['jquery', 'flatpickr-js'], null, true);

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
        global $product;

        if (!$product || $product->get_type() !== 'agendamento') return;

        $horarios_disponiveis = get_post_meta($product->get_id(), '_dias_horarios_disponiveis', true);
        $preco_agendamento = get_post_meta($product->get_id(), '_preco_agendamento', true);

        if (!empty($horarios_disponiveis)) {
            echo '<form class="cart" method="post" enctype="multipart/form-data">';
            echo '<div class="agendamento-opcoes">';
            echo '<p><strong>' . __('Escolha uma data e horário:', 'agendamento-otimizado') . '</strong></p>';
            echo '<div class="agendamento-botoes">';

            foreach ($horarios_disponiveis as $horario) {
                echo '<button type="button" class="botao-horario" data-horario="' . esc_attr($horario) . '">';
                echo esc_html($horario);
                echo '</button>';
            }

            echo '</div>';
            
            // Campos ocultos necessários
            echo '<input type="hidden" name="horario_agendamento" id="horario_agendamento" value="">';
            echo '<input type="hidden" name="add-to-cart" value="' . esc_attr($product->get_id()) . '">';
            echo '<input type="hidden" name="quantity" value="1">';
            
            // Botão Comprar
            echo '<button type="submit" class="single_add_to_cart_button button alt" disabled>' 
                 . __('Adicionar ao Carrinho', 'agendamento-otimizado') . '</button>';
            
            echo '</div>';
            echo '</form>';

            $this->adicionar_scripts_estilos();
        }
    }

    private function adicionar_scripts_estilos() {
        ?>
        <script>
            jQuery(document).ready(function($) {
                $(".botao-horario").on("click", function() {
                    $(".botao-horario").removeClass("selecionado");
                    $(this).addClass("selecionado");
                    $("#horario_agendamento").val($(this).data("horario"));
                    $(".single_add_to_cart_button").prop("disabled", false);
                });
            });
        </script>
        <style>
            .agendamento-opcoes {
                margin: 20px 0;
            }
            .agendamento-botoes {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 20px;
            }
            .botao-horario {
                padding: 8px 16px;
                background: #f8f8f8;
                border: 1px solid #ddd;
                border-radius: 4px;
                cursor: pointer;
            }
            .botao-horario.selecionado {
                background: #0073aa;
                color: white;
                border-color: #0073aa;
            }
            .single_add_to_cart_button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
        </style>
        <?php
    }

    /**
     * Valida se um horário foi selecionado antes de adicionar ao carrinho
     */
    public function validar_horario_selecionado($passed, $product_id, $quantity) {
        if (isset($_POST['horario_agendamento']) && empty($_POST['horario_agendamento'])) {
            wc_add_notice(__('Por favor, selecione um horário para o agendamento.', 'agendamento-otimizado'), 'error');
            $passed = false;
        }
        return $passed;
    }

    /**
     * Salva o horário selecionado nos dados do item do carrinho
     */
    public function salvar_horario_no_pedido($cart_item_data, $product_id) {
        if (!empty($_POST['horario_agendamento'])) {
            $cart_item_data['horario_agendamento'] = sanitize_text_field($_POST['horario_agendamento']);
            
            // Garante que o item seja único no carrinho
            $cart_item_data['unique_key'] = md5(microtime() . $product_id);
        }
        return $cart_item_data;
    }

    /**
     * Exibe o horário agendado no checkout
     */
    public function exibir_horario_no_checkout($item_data, $cart_item) {
        if (!empty($cart_item['horario_agendamento'])) {
            $item_data[] = array(
               // 'key' => __('Horário', 'agendamento-otimizado'),
                //'value' => wc_clean($cart_item['horario_agendamento'])
            );
        }
        return $item_data;
    }

    /**
     * Salva o horário agendado nos metadados do pedido
     */
    public function salvar_horario_no_pedido_meta($item, $cart_item_key, $values, $order) {
        if (!empty($values['horario_agendamento'])) {
            $item->add_meta_data(
                __('Horário', 'agendamento-otimizado'),
                wc_clean($values['horario_agendamento'])
            );
        }
    }

    /**
     * Exibe o horário agendado na página de administração do pedido
     */
    public function exibir_horario_no_admin_pedido($item_id, $item, $product) {
        if ($item->get_meta('Data e Horário Agendados')) {
            echo '<p><strong>' . __('Data e Horário Agendados', 'agendamento-otimizado') . ':</strong> ' . esc_html($item->get_meta('Data e Horário Agendados')) . '</p>';
        }
    }

    /**
     * Exibe as opções de agendamento
     */
    public function exibir_opcoes_agendamento($product_id) {
        $tipo_agendamento = get_post_meta($product_id, '_tipo_agendamento', true);
        $horarios_disponiveis = get_post_meta($product_id, '_horarios_disponiveis', true);

        if ($tipo_agendamento === 'dia_inteiro') {
            echo '<p>' . __('Este produto está disponível para agendamento o dia inteiro.', 'agendamento-otimizado') . '</p>';
        } elseif ($tipo_agendamento === 'horarios') {
            echo '<p>' . __('Horários disponíveis:', 'agendamento-otimizado') . '</p>';
            echo '<ul>';
            $horarios = explode(',', $horarios_disponiveis);
            foreach ($horarios as $horario) {
                echo '<li>' . esc_html($horario) . '</li>';
            }
            echo '</ul>';
        }
    }
}