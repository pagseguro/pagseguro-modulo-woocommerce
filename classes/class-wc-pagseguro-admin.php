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

if ( ! class_exists('WC_PagSeguro_Admin')):

/**
 * Class WC_PagSeguro_Admin
 */
class WC_PagSeguro_Admin
{

    /**
     * Register menus page.
     */
    public static function register_menus(){
        self::add_pagseguro_menu();
        self::add_config_submenu();
        self::add_conciliation_submenu();
        self::add_cancel_submenu();
    }

    /**
     * Add menu PagSeguro
     */
    private static function add_pagseguro_menu()
        {
            add_menu_page(
                __( 'PagSeguro Menu', 'woocommerce-pagseguro-oficial' ),
                __( 'PagSeguro', 'woocommerce-pagseguro-oficial' ),
                'manage_options',
                'wp_pagseguro_menu',
                array(WC_PagSeguro_Admin::class, 'redirect_to_config_page')
            );
        }

    /**
     * Add submenu configurations
     */
    private static function add_config_submenu()
    {
        add_submenu_page(
            'wp_pagseguro_menu',
            __( 'PagSeguro Configurações Menu', 'woocommerce-pagseguro-oficial' ),
            'Configurações',
            'manage_options',
            'wp_pagseguro_menu'


        );
    }

    /**
     * Add submenu conciliation
     */
    private static function add_conciliation_submenu()
    {
        add_submenu_page(
            'wp_pagseguro_menu',
            __( 'PagSeguro Conciliação Menu', 'woocommerce-pagseguro-oficial' ),
            'Conciliação',
            'manage_options',
            'wp_pagseguro_conciliation_menu',
            array(WC_PagSeguro_Admin::class, 'admin_page_conciliation')
        );
    }

    /**
     * Add submenu conciliation
     */
    private static function add_cancel_submenu()
    {
        add_submenu_page(
            'wp_pagseguro_menu',
            __( 'PagSeguro Cancelameto', 'woocommerce-pagseguro-oficial' ),
            'Cancelamento',
            'manage_options',
            'wp_pagseguro_cancel_menu',
            array(WC_PagSeguro_Admin::class, 'admin_page_cancel')
        );
    }

    /**
     * Redirect to PagSeguro configuration page
     */
    public static function redirect_to_config_page()
    {
        wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=pagseguro'));
    }

    /**
     * Load admin page conciliation template
     */
    public static function admin_page_conciliation()
    {
        include(pathinfo(__DIR__)['dirname'] . '/template/admin/conciliation.php');
    }

    /**
     * Load admin page cancel template
     */
    public static function admin_page_cancel()
    {
        include(pathinfo(__DIR__)['dirname'] . '/template/admin/cancel.php');
    }
}

add_action( 'admin_menu', array(WC_PagSeguro_Admin::class, 'register_menus' ));

endif;