<?php
/**
 ************************************************************************
Copyright [2016] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 ************************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('WC_PagSeguro_Status')):
    
    class WC_PagSeguro_Status
    {

        /**
         * @var array
         * @enum
         */
        public static $statusList = array(
            0 => "wc-ps-iniciado",
            1 => "wc-ps-pagamento",
            2 => "wc-ps-em-analise",
            3 => "wc-ps-paga",
            4 => "wc-ps-disponivel",
            5 => "wc-ps-em-disputa",
            6 => "wc-ps-devolvida",
            7 => "wc-ps-cancelada",
            8 => "wc-ps-chargeback-debitado",
            9 => "wc-ps-em-contestacao"
        );

        /**
         * When plugin is activated
         */
        public static function init(){

                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_iniciado'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_aguardando_pagamento'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_em_analise'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_paga'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_disponivel'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_em_disputa'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_devolvida'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_cancelada'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_chargeback_debitado'));
                add_action( 'init', array(WC_PagSeguro_Status::class, 'register_ps_em_constestacao'));

                add_filter( 'wc_order_statuses', array(WC_PagSeguro_Status::class, 'register_ps_order_status'));

                add_action('admin_print_styles', array(WC_PagSeguro_Status::class, 'add_css'));

        }

        public static function add_css()
        {
            wp_enqueue_style( 'woocommerce-pagseguro-styles', plugins_url('woocommerce-pagseguro-oficial/assets/css/styles.css') );
        }

        public static function register_ps_order_status($order_statuses)
        {
            $order_statuses['wc-ps-iniciado'] = _x('PagSeguro Iniciado', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-pagamento'] = _x('PagSeguro Aguardando Pagamento', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-em-analise'] = _x('PagSeguro Em Análise', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-paga'] = _x('PagSeguro Paga', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-disponivel'] = _x('PagSeguro Disponível', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-em-disputa'] = _x('PagSeguro Em Disputa', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-devolvida'] = _x('PagSeguro Devolvida', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-cancelada'] = _x('PagSeguro Cancelada', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-chargeback-debitado'] = _x('PagSeguro Chargeback Debitado', 'WooCommerce Order status', 'text_domain');
            $order_statuses['wc-ps-em-contestacao'] = _x('PagSeguro Em Contestação', 'WooCommerce Order status', 'text_domain');
            return $order_statuses;
        }

        public static function register_ps_iniciado()
        {
            register_post_status( 'wc-ps-iniciado', array(
                    'label'                     => 'PagSeguro Iniciado',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Iniciado <span class="count">(%s)</span>', 'PagSeguro Iniciado <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_aguardando_pagamento()
        {
            register_post_status( 'wc-ps-pagamento', array(
                    'label'                     => 'PagSeguro Aguardando Pagamento',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Aguardando Pagamento <span class="count">(%s)</span>', 'PagSeguro Aguardando Pagamento <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_em_analise()
        {
            register_post_status( 'wc-ps-em-analise', array(
                    'label'                     => 'PagSeguro Em Análise',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Em Análise <span class="count">(%s)</span>', 'PagSeguro Em Análise <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_paga()
        {
            register_post_status( 'wc-ps-paga', array(
                    'label'                     => 'PagSeguro Paga',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Paga <span class="count">(%s)</span>', 'PagSeguro Paga <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_disponivel()
        {
            register_post_status( 'wc-ps-disponivel', array(
                    'label'                     => 'PagSeguro Disponível',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Disponível <span class="count">(%s)</span>', 'PagSeguro Disponível <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_em_disputa()
        {
            register_post_status( 'wc-ps-em-disputa', array(
                    'label'                     => 'PagSeguro Em Disputa',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Em Disputa <span class="count">(%s)</span>', 'PagSeguro Em Disputa <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_devolvida()
        {
            register_post_status( 'wc-ps-devolvida', array(
                    'label'                     => 'PagSeguro Devolvida',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Devolvida <span class="count">(%s)</span>', 'PagSeguro Devolvida <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_cancelada()
        {
            register_post_status( 'wc-ps-cancelada', array(
                    'label'                     => 'PagSeguro Cancelada',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Cancelada <span class="count">(%s)</span>', 'PagSeguro Cancelada <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_chargeback_debitado()
        {
            register_post_status( 'wc-ps-chargeback-debitado', array(
                    'label'                     => 'PagSeguro Chargeback Debitado',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Chargeback Debitado <span class="count">(%s)</span>', 'PagSeguro Chargeback Debitado <span class="count">(%s)</span>' )
                )
            );
        }

        public static function register_ps_em_constestacao()
        {
            register_post_status( 'wc-ps-em-contestacao', array(
                    'label'                     => 'PagSeguro Em Contestação',
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'PagSeguro Em Contestação <span class="count">(%s)</span>', 'PagSeguro Em Contestação <span class="count">(%s)</span>' )
                )
            );
        }

    }

endif;