<?php
/**
 * Plugin Name: WooCommerce PagSeguro Oficial
 * Plugin URI: http://github.com/pagseguro/woocomerce
 * Description: Gateway de pagamento PagSeguro para WooCommerce.
 * Author: PagSeguro Internet LTDA.
 * Author URI: https://pagseguro.uol.com.br/v2/guia-de-integracao/downloads.html#!Modulos
 * Version: 1.1.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-pagseguro-oficial
 * Domain Path: languages/
 *
 * @package WooCommerce_PagSeguro_Oficial
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_PagSeguro' )) :

    class WC_PagSeguro {

        /**
         * Plugin version.
         *
         * @var string
         */
        const VERSION = '1.2.0';

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

                // Filters
                add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

                // Actions
                add_action( 'init', 'notification_listener');
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
            require_once 'classes/class-wc-pagseguro-api.class.php';
            require_once 'classes/class-wc-pagseguro-gateway.class.php';
            require_once 'classes/class-wc-pagseguro-model.class.php';
            require_once 'classes/class-wc-pagseguro-shortcodes.php';
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
