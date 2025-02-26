<?php
/*
Plugin Name: Agendamento Otimizado WooCommerce
Description: Sistema de agendamento otimizado para serviços no WooCommerce.
Version: 1.0
Author: Seu Nome
*/

if (!defined('ABSPATH')) {
    exit;
}

define('AGENDAMENTO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGENDAMENTO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload Classes
require_once AGENDAMENTO_PLUGIN_PATH . 'includes/class-agendamento-plugin.php';

// Inicializa o Plugin
function inicializar_agendamento_plugin() {
    new Agendamento_Plugin();
}
add_action('plugins_loaded', 'inicializar_agendamento_plugin');
