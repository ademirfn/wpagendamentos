<?php
/*
Plugin Name: Agendamento Otimizado WooCommerce
Description: Sistema de agendamento otimizado para serviços no WooCommerce.
Version: 1.01
Author: Ademir Neissinger
*/

if (!defined('ABSPATH')) {
    exit;
}

// Verifica se o WooCommerce está ativo
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>O plugin Agendamento Otimizado requer o WooCommerce ativo para funcionar.</p></div>';
    });
    return;
}

// Define constantes do plugin
define('AGENDAMENTO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGENDAMENTO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Carrega os arquivos necessários
function agendamento_otimizado_autoload() {
    // Primeiro carrega a classe do produto
    require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-wc-product-agendamento.php';
    
    // Depois carrega as outras classes
    require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-plugin.php';

    if (is_admin()) {
        require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-admin.php';
    }

    if (!is_admin()) {
        require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-frontend.php';
    }
}

// Registra o tipo de produto
add_action('init', function() {
    // Registra o tipo de produto apenas se o WooCommerce estiver ativo
    if (class_exists('WC_Product')) {
        require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-wc-product-agendamento.php';
    }
});

// Inicializa o plugin
function inicializar_agendamento_plugin() {
    agendamento_otimizado_autoload();
    new Agendamento_Plugin();
}
add_action('plugins_loaded', 'inicializar_agendamento_plugin', 20);

// Carrega o texto de tradução
add_action('init', function () {
    load_plugin_textdomain('agendamento-otimizado', false, dirname(plugin_basename(__FILE__)) . '/languages');
});
