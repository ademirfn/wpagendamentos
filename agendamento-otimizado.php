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

define('AGENDAMENTO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGENDAMENTO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload Classes
require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-plugin.php';
require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-wc-product-agendamento.php'; // Adicionado

// Hook de ativação
register_activation_hook(__FILE__, function () {
    require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-database.php';
    $database = new Agendamento_Database();
    $database->create_tables();
});

// Inicializa o Plugin
function inicializar_agendamento_plugin() {
    new Agendamento_Plugin();
}
add_action('plugins_loaded', 'inicializar_agendamento_plugin');

add_action('init', function () {
    load_plugin_textdomain('agendamento-otimizado', false, dirname(plugin_basename(__FILE__)) . '/languages');
});
