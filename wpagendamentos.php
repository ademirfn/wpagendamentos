<?php
/**
 * Plugin Name: WooCommerce Agendamentos WMatrix
 * Plugin URI:  https://wnatrix.in
 * Description: Plugin de agendamento para WooCommerce, permitindo reservas de horários.
 * Version:     1.0.0
 * Author:      Seu Nome
 * Author URI:  https://wmatrix.in
 * License:     GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Adicionar um novo tipo de produto "Agendamento"
function wc_register_booking_product_type($types) {
    $types['booking'] = __('Agendamento', 'woocommerce');
    return $types;
}
add_filter('product_type_selector', 'wc_register_booking_product_type');

// Adicionar opções personalizadas ao produto "Agendamento"
function wc_add_booking_product_fields() {
    global $post;
    
    echo '<div class="options_group">';
    
    // Campo para definir duração do agendamento
    woocommerce_wp_text_input([
        'id' => '_booking_duration',
        'label' => __('Duração do Agendamento (em horas)', 'woocommerce'),
        'desc_tip' => true,
        'description' => __('Defina o intervalo de duração do agendamento em horas. Para um dia inteiro, insira 24.', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => [
            'step' => '1',
            'min' => '1',
            'max' => '24'
        ]
    ]);
    
    // Campo para ativar/desativar pagamento parcial
    woocommerce_wp_checkbox([
        'id' => '_booking_partial_payment',
        'label' => __('Habilitar Pagamento Parcial?', 'woocommerce'),
        'description' => __('Se ativado, o cliente pagará 50% no agendamento e o restante pessoalmente.', 'woocommerce'),
        'desc_tip' => true
    ]);
    
    // Campo para definir horários disponíveis
    woocommerce_wp_textarea_input([
        'id' => '_booking_available_times',
        'label' => __('Horários Disponíveis', 'woocommerce'),
        'desc_tip' => true,
        'description' => __('Insira os horários disponíveis separados por vírgula. Exemplo: 09:00, 11:00, 14:00', 'woocommerce')
    ]);
    
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'wc_add_booking_product_fields');

// Salvar os dados personalizados
function wc_save_booking_product_fields($post_id) {
    $duration = isset($_POST['_booking_duration']) ? sanitize_text_field($_POST['_booking_duration']) : '';
    update_post_meta($post_id, '_booking_duration', $duration);
    
    $partial_payment = isset($_POST['_booking_partial_payment']) ? 'yes' : 'no';
    update_post_meta($post_id, '_booking_partial_payment', $partial_payment);
    
    $available_times = isset($_POST['_booking_available_times']) ? sanitize_text_field($_POST['_booking_available_times']) : '';
    update_post_meta($post_id, '_booking_available_times', $available_times);
}
add_action('woocommerce_process_product_meta', 'wc_save_booking_product_fields');

// Verificar disponibilidade antes de adicionar ao carrinho
function wc_booking_check_availability($passed, $product_id, $quantity) {
    $available_times = get_post_meta($product_id, '_booking_available_times', true);
    if (!$available_times) return $passed;
    
    $times_array = array_map('trim', explode(',', $available_times));
    $booked_times = get_post_meta($product_id, '_booked_times', true) ?: [];
    
    if (count($booked_times) >= count($times_array)) {
        wc_add_notice(__('Este produto já está totalmente reservado.', 'woocommerce'), 'error');
        return false;
    }
    
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'wc_booking_check_availability', 10, 3);

// Implementação do pagamento parcial
function wc_booking_custom_price($cart_object) {
    foreach ($cart_object->cart_contents as $key => $value) {
        $partial_payment_enabled = get_post_meta($value['product_id'], '_booking_partial_payment', true);
        if ($partial_payment_enabled === 'yes') {
            $price = floatval($value['data']->get_price()) / 2;
            $value['data']->set_price($price);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'wc_booking_custom_price');
