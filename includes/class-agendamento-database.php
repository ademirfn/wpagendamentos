<?php

class Agendamento_Database {

    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'create_tables']);
    }

    public function create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'agendamento_disponibilidade';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            data_disponivel DATE NOT NULL,
            horario_disponivel TIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
