<?php

class Agendamento_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
    }

    public function register_admin_menu() {
        add_menu_page(
            'Agendamento',
            'Agendamento',
            'manage_options',
            'agendamento-otimizado',
            [$this, 'render_admin_page'],
            'dashicons-calendar-alt'
        );
    }

    public function render_admin_page() {
        echo '<div class="wrap"><h1>Gerenciar Agendamentos</h1>';
        echo '<p>Aqui você pode gerenciar as disponibilidades.</p>';
        // Futuro: Formulário interativo usando FullCalendar
        echo '</div>';
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css');
        wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js', [], null, true);
        wp_enqueue_script('admin-calendar-js', AGENDAMENTO_PLUGIN_URL . 'assets/js/admin-calendar.js', ['jquery', 'fullcalendar-js'], null, true);
    
        wp_localize_script('admin-calendar-js', 'agendamento_ajax', [
            'url' => admin_url('admin-ajax.php'),
        ]);
    }
}
