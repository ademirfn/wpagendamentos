<?php

if (!defined('ABSPATH')) {
    exit;
}

// Verifica se a classe já existe antes de declará-la
if (!class_exists('WC_Product_Agendamento')) {
    class WC_Product_Agendamento extends WC_Product {
        
        public function __construct($product) {
            $this->product_type = 'agendamento';
            parent::__construct($product);
        }

        public function get_type() {
            return 'agendamento';
        }

        public function is_purchasable() {
            return true;
        }

        public function is_in_stock() {
            return true;
        }

        public function is_sold_individually() {
            return true;
        }

        public function is_virtual() {
            return true;
        }

        // Corrigido para corresponder à assinatura da classe pai
        public function get_stock_quantity($context = 'view') {
            $horarios_disponiveis = get_post_meta($this->get_id(), '_dias_horarios_disponiveis', true);
            return !empty($horarios_disponiveis) ? count($horarios_disponiveis) : 0;
        }

        public function get_data() {
            $data = parent::get_data();
            $data['_tipo_agendamento'] = get_post_meta($this->get_id(), '_tipo_agendamento', true);
            $data['_dias_horarios_disponiveis'] = get_post_meta($this->get_id(), '_dias_horarios_disponiveis', true);
            $data['_preco_agendamento'] = get_post_meta($this->get_id(), '_preco_agendamento', true);
            return $data;
        }

        public function get_price($context = 'view') {
            $preco = get_post_meta($this->get_id(), '_preco_agendamento', true);
            return !empty($preco) ? $preco : 0;
        }
    }
}