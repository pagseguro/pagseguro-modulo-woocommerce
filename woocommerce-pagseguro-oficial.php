<?php
/**
 * Plugin Name: WooCommerce PagSeguro Oficial
 * Plugin URI: http://github.com/pagseguro/woocomerce
 * Description: Gateway de pagamento PagSeguro para WooCommerce.
 * Author: PagSeguro Internet LTDA.
 * Author URI: https://pagseguro.uol.com.br/v2/guia-de-integracao/downloads.html#!Modulos
 * Version: 1.4.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-pagseguro-oficial
 * Domain Path: languages/
 *
 * @package WooCommerce_PagSeguro_Oficial
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


if ( ! defined( 'PS_PLUGIN_DIR' ) ) {
    define( 'PS_PLUGIN_DIR', __FILE__ );
}

if ( ! class_exists( 'WC_PagSeguro' )) :

    class WC_PagSeguro {

        /**
         * Plugin version.
         *
         * @var string
         */
        const VERSION = '1.4.0';

        /**
         * Instance of this class.
         *
         * @var object
         */
        protected static $instance = null;

        public function __construct()
        {
            // Load plugin text domain
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

            // Checks with WooCommerce is installed.
            if ( class_exists( 'WC_Payment_Gateway' ) ) {
                $this->requires();

                WC_PagSeguro_Shortcodes::init();
                WC_PagSeguro_Status::init();

                // Filters
                add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

                // Actions
                add_action( 'init', 'notification_listener');
                add_action( 'init', array( $this, 'ajax_listener'));
            } else {
                add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            }
        }

        /**
         * Return an instance of this class.
         *
         * @return object A single instance of this class.
         */
        public static function get_instance() {
            // If the single instance hasn't been set, set it now.
            if ( null == self::$instance ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Load the plugin text domain for translation.
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( 'woocommerce-pagseguro-oficial', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

        /**
         * Add the gateway to WooCommerce.
         *
         * @param  array $methods WooCommerce payment methods.
         *
         * @return array Payment methods with PagSeguro.
         */
        public function add_gateway( $methods ) {
            $methods[] = 'WC_PagSeguro_Gateway';
            return $methods;
        }

        /**
         * Requires.
         */
        private function requires() {
            require_once 'library/vendor/autoload.php';
            require_once 'classes/exceptions/class-wc-pagseguro-exception.php';
            require_once 'classes/class-wc-pagseguro-api.php';
            require_once 'classes/class-wc-pagseguro-gateway.php';
            require_once 'classes/class-wc-pagseguro-model.php';
            require_once 'classes/class-wc-pagseguro-shortcodes.php';
            require_once 'classes/class-wc-pagseguro-admin.php';
            require_once 'classes/class-wc-pagseguro-status.php';
            require_once 'classes/class-wc-pagseguro-payload.php';
            require_once 'classes/class-wc-pagseguro-direct-payment.php';
            require_once 'classes/admin/class-wc-pagseguro-methods.php';
            require_once 'classes/admin/class-wc-pagseguro-conciliation.php';
            require_once 'classes/admin/class-wc-pagseguro-cancel.php';
        }

        /**
         * Ajax listener
         */
        public function ajax_listener()
        {
            $requests = $this->filter_requests();

            if (! is_null($requests['is_ajax']) && $requests['is_ajax'] && $requests['action'] == 'conciliation') {
                $conciliation = new WC_PagSeguro_Conciliation;
                $conciliation->init(get_option('woocommerce_pagseguro_settings'));
            }

            if (! is_null($requests['is_ajax']) && $requests['is_ajax'] && $requests['action'] == 'cancel') {
                $cancel = new WC_PagSeguro_Cancel();
                $cancel->init(get_option('woocommerce_pagseguro_settings'));
            }
        }

        /**
         * Get requests from http post|get
         *
         * @return array
         */
        public function filter_requests()
        {

            return [
                'is_ajax' => (!isset($_REQUEST['ajax'])) ? null: filter_var($_REQUEST['ajax'], FILTER_SANITIZE_STRING),
                'action'  => (!isset($_REQUEST['action'])) ? null : filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING)
            ];
        }

        /**
         * Action links.
         *
         * @return array
         */
        public function plugin_action_links($links) {
            $plugin_links = array();
            $plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=pagseguro' ) ) . '">' . __( 'Settings', 'woocommerce-pagseguro-oficial' ) . '</a>';
            return array_merge( $plugin_links, $links );
        }

        /**
         * WooCommerce fallback notice.
         *
         * @return  string
         */
        public function woocommerce_missing_notice() {
            $html  = '<div class="error"><p>';
            $html .= __( 'WooCommerce PagSeguro depends on the WooCommerce plugin to work!', 'woocommerce-pagseguro-oficial' );
            $html .= '</p></div>';
            echo $html;
        }

    }

    add_action( 'plugins_loaded', array( 'WC_PagSeguro', 'get_instance' ) );

    /**
     * Insert Modal Markup in the top markup page
     **/
    function template_modals() {
        include 'template/admin/modals.php';
    };
    add_action('admin_head', 'template_modals');

    /**
     * Include dataTables styles
     */
    add_action('admin_enqueue_scripts', function(){
        wp_enqueue_style(
            'dataTable-styles',
            plugins_url('/assets/css/jquery.dataTables.css', PS_PLUGIN_DIR), '', 1, 'screen'
        );
    });

    /**
     * Include ajax object
     */
    add_action( 'wp_enqueue_scripts', function(){
        wp_enqueue_script( 'ajax-script', plugins_url( '/assets/js/direct-payment.js', PS_PLUGIN_DIR ), array('jquery') );
        wp_localize_script( 'ajax-script', 'ajax_object',
            array( 'ajax_url' => plugins_url( '/classes/class-wc-pagseguro-ajax.php' , PS_PLUGIN_DIR ) ));
    });

    /**
     * Include bootstrap to front-end
     */
    add_action('wp_enqueue_scripts', function(){
        wp_enqueue_script(
            'bootstrap-script',
            plugins_url( '/assets/js/vendor/bootstrap.min.js', PS_PLUGIN_DIR ), array('jquery')
        );
        wp_enqueue_style(
            'bootstrap-styles',
            plugins_url('/assets/css/vendor/bootstrap.min.css', PS_PLUGIN_DIR)
        );
    });

    /**
     * Direct payment styles
     */
    add_action('wp_enqueue_scripts', function (){
        wp_enqueue_style(
            'font-awesome-styles',
            plugins_url('/assets/css/vendor/font-awesome.min.css', PS_PLUGIN_DIR)
        );
        wp_enqueue_style(
            'direct-payment-styles',
            plugins_url('/assets/css/direct-payment.css', PS_PLUGIN_DIR)
        );
    });

    /**
     * Intercept woocommerce checkout process
     */
    add_action('woocommerce_checkout_process', 'ps_validade_checkout_proccess');

    function ps_validade_checkout_proccess() {
        try {
            $billing_address_1 = explode(', ', $_POST['billing_address_1']);
            if(!isset($billing_address_1[1])){
                throw new Exception('[PAGSEGURO]: Invalid address');
            };
        } catch (Exception $exception){
            wc_add_notice(__('Endereço com formato inválido. Exemplo: Rua São João, 11'), 'error');
        }

        try {
            if(!isset($_POST['billing_address_2']) || !$_POST['billing_address_2']){
                throw new Exception('[PAGSEGURO]: Invalid address');
            }
        } catch (Exception $exception){
            wc_add_notice(__('Por favor, preencha o bairro.'), 'error');
        }


        try {
            $phone = count(filter_var($_POST['billing_phone'], FILTER_SANITIZE_NUMBER_INT));

            if($phone < 9 || $phone > 11){
                throw new Exception('[PAGSEGURO]: Invalid phone');
            }
        } catch (Exception $exception){
            wc_add_notice(__('Telefone inválido. Preencha DDD + NÚMERO'), 'error');
        }
    }


    /**
     *  Call actions for notification
     */
    function notification_listener()
    {
        if(isset($_REQUEST['notificationurl']) && $_REQUEST['notificationurl'] == "true" && isset($_POST)) {
            $notification = new WC_PagSeguro_Gateway();
            $notification->process_nofitication();
        }
    }

    /**
     * Setup activation hook
     */
    register_activation_hook( __FILE__, function (){
        require_once 'classes/class-wc-pagseguro-setup.php';
        require_once 'classes/class-wc-pagseguro-pages.php';
        WC_PagSeguro_Setup::plugin_activated();
    });

endif;
