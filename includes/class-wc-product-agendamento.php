<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Product_Agendamento')) {
    class WC_Product_Agendamento extends WC_Product {
        public function __construct($product = 0) {
            parent::__construct($product);
            $this->set_props([
                'type' => 'agendamento',
            ]);
        }
    }
}