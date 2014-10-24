<?php
/**
 * Plugin Name: WooCommerce PagSeguro Oficial
 * Plugin URI: https://pagseguro.uol.com.br/v2/guia-de-integracao/downloads.html#!Modulos
 * Description: Ofereça PagSeguro em seu e-commerce e receba pagamentos por cartão de crédito, transferência bancária e boleto.
 * Author: PagSeguro Internet LTDA.
 * Author URI: https://pagseguro.uol.com.br
 * Version: 1.0 
 * Text Domain: wpwcpagseguro
 * Domain Path: /languages/
 */

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

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

/**
 * Functions to Gateway PagSeguro.
 */
add_action( 'plugins_loaded', 'init_wp_wc_pagseguro_gateway_function');

function init_wp_wc_pagseguro_gateway_function() {
    
    /**
     * Load textdomain.
     */
    load_plugin_textdomain( 'wpwcpagseguro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    
    /**
     * Add the gateway to WooCommerce.
     **/
    add_filter( 'woocommerce_payment_gateways', 'add_wp_wc_pagseguro_gateway_class' , 1, 10);

    function add_wp_wc_pagseguro_gateway_class( $methods ) {
        $methods[] = 'WP_WC_PagSeguro_Gateway';

        return $methods;
    }
    
    class WP_WC_PagSeguro_Gateway extends WC_Payment_Gateway {

        /**
         * Version Plugin
         * @var version
         */
        private $plugin_version = '1.1';
        
        
        /**
         * Constructor for init always data for gateway.
         *
         * @return void
         */
         public function __construct() {
            $this->load();
            
            $this->id             = 'pagseguro';
            $this->has_fields     = false;
            $this->method_title   = 'PagSeguro';
            $this->icon           = plugins_url( 'image/ps-logo.png', __FILE__ );

            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables.
            $this->title            = $this->settings['title'];
            $this->description      = $this->settings['description'];
            $this->email            = $this->settings['email'];
            $this->token            = $this->settings['token'];
            $this->invoice_prefix   = ! empty( $this->settings['invoice_prefix'] ) ? $this->settings['invoice_prefix'] : 'WC-';
            $this->url_notification = $this->settings['url_notification'];
            $this->url_redirect     = $this->settings['url_redirect'];
            $this->charset          = $this->settings['charset'];
            $this->debug            = $this->settings['debug'];
            $this->path_log         = $this->settings['path_log'];

            // Actions.
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            } else {
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
            }
            
            $this->enabled = 'no';
            if ((( 'yes' == $this->settings['enabled'] ) && ! empty( $this->email ) && ! empty( $this->token ) && $this->is_valid_for_use())) {
                $this->enabled = 'yes';
            }

            // Checks if email is not empty.
            if ( empty( $this->email ) ) {
                add_action( 'admin_notices', array( &$this, 'mail_missing_message' ) );
            }

            // Checks if token is not empty.
            if ( empty( $this->token ) ) {
                add_action( 'admin_notices', array( &$this, 'token_missing_message' ) );
            }
            
            global $woocommerce;

            // Active logs.
            if ( 'yes' == $this->debug ) {
                $this->log = $woocommerce->logger();
                $this->createLogFile($this->returnPathLog());
                PagSeguroConfig::activeLog($this->returnPathLog());
            }
            
            //Insert new status of PagSeguro
            wp_insert_term('em disputa', 'shop_order_status');
        }
        
        /**
         * Load archives PagSeguroLibrary and wp-wc-modal-pagseguro
         */
        public function load(){
           require_once "PagSeguroLibrary/PagSeguroLibrary.php";
           require_once "classes/wpwcmodalpagseguro.class.php";
        }
        
        /**
         * Check if this gateway is enabled and available in the user's country.
         *
         * @return bool
         */
        public function is_valid_for_use() {
            if ( ! in_array( get_woocommerce_currency(), array( 'BRL' ) ) ) {
                return false;
            }

            return true;
        }

        /**
         * Admin Panel Options.
         */
        public function admin_options() {

            echo '<h3>' . __( 'PagSeguro', 'wpwcpagseguro' ) . '</h3>';

            if ( ! $this->is_valid_for_use() ) {

                // Valid currency.
                echo '<div class="inline error"><p>'. __( 'Change your store currency to Brazilian Real (R$).', 'wpwcpagseguro' ) . '</p></div>';

            } else {

                // Generate the HTML For the settings form.
                echo '<table class="form-table">';
                $this->generate_settings_html();
                echo '</table>';
            }
        }

        /**
         * Initialise Gateway Settings Form Fields.
         *
         * @return void
         */
        public function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'wpwcpagseguro' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable module?', 'wpwcpagseguro' ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __( 'Title', 'wpwcpagseguro' ),
                    'type' => 'text',
                    'description' => __( 'Checkout title.', 'wpwcpagseguro' ),
                    'default' => __( 'PagSeguro', 'wpwcpagseguro' )
                ),
                'description' => array(
                    'title' => __( 'Description', 'wpwcpagseguro' ),
                    'type' => 'textarea',
                    'description' => __( 'Checkout description.', 'wpwcpagseguro' ),
                    'default' => __( 'Choose between many payment methods, including credit cards and Brazilian banks. Pay securely and quickly with PagSeguro', 'wpwcpagseguro' )
                ),
                'email' => array(
                    'title' => __( 'E-Mail', 'wpwcpagseguro' ),
                    'type' => 'text',
                    'description' => __( 'Do not have a PagSeguro account? <a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=11&tipo=cadastro#!vendedor" target="_blank">Click here </a> and register for free.', 'wpwcpagseguro' ),
                    'default' => ''
                ),
                'token' => array(
                    'title' => __( 'Token', 'wpwcpagseguro' ),
                    'type' => 'text',
                    'description' => __( 'Do not have or do not know your token? <a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank">Click here </a> to generate a new one.', 'wpwcpagseguro' ),
                    'default' => ''
                ),
                'url_redirect' => array(
                    'title' => __( 'Redirect URL', 'wpwcpagseguro' ),
                    'type' => 'text',
                    'description' => __( 'Your customer will be redirected back to your store or to the URL entered in this field. <a href="https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml" target="_blank">Click here </a> to activate.' , 'wpwcpagseguro' )
                ),
                'url_notification' => array(
                    'title' => __( 'Notification URL', 'wpwcpagseguro' ),
                    'type' => 'text',
                    'description' => __( 'Whenever a transaction change its status, the PagSeguro sends a notification to your store or to the URL entered in this field.', 'wpwcpagseguro' ),
                    'default' => home_url().'/index.php?notificationurl=true'
                ),
                'invoice_prefix' => array(
                    'title' => __( 'Invoice Prefix', 'wpwcpagseguro' ),
                    'type' => 'text',
                    'description' => __( 'Prefix for your invoice numbers.', 'wpwcpagseguro' ),
                    'default' => 'WC-'
                ),
                'charset' => array(
                    'title' => __( 'Charset', 'wpwcpagseguro' ),
                    'type' => 'text',
                    'default' => 'UTF-8',
                    'description' => __( 'Set the charset according to the coding of your system.', 'wpwcpagseguro' ),
                ),
                'debug' => array(
                    'title' => __( 'Debug Log', 'wpwcpagseguro' ),
                    'type' => 'checkbox',
                    'label' => __( 'Create log file?', 'wpwcpagseguro' ),
                    'default' => 'no',
                ),
                'path_log' => array(
                    'type' => 'text',
                    'default' => '',
                    'description' => __( 'Path to the log file.', 'wpwcpagseguro' ).' Ex.: wp-content/logs',
                )
            );

        }

        /**
         * Sets items of payment for PagSeguroLybrary
         * 
         * @param type $order
         * @return array
         */
        public function setItems($order){
            global $woocommerce;
            $count = 1;
            $pagSeguroItens = array();
            
            foreach ( $order->get_items() as $item ) {
                    if ( $item['qty'] ) {
                        $pagSeguroItem = new PagSeguroItem();
                        
                        foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $values ) {
                            $_product = $values['data'];

                            if($_product->id == $item['product_id']){
                                $pagSeguroItem->setWeight(($_product->get_weight()* 1000));
                                break;
                            }
                        }
                        $pagSeguroItem->setId($count);
                        $pagSeguroItem->setDescription($item['name']);
                        $pagSeguroItem->setQuantity($item['qty']);
                        $pagSeguroItem->setAmount($order->get_item_total( $item, false ));
                        $count++;
                        
                        array_push($pagSeguroItens, $pagSeguroItem);
                    }
                }
            return $pagSeguroItens;
        }
        
        /**
         * Use PagSeguroLibrary
         * 
         * @param type $order_id
         * @return type
         */
        public function payment($order){
            global $woocommerce;

            // Instantiate a new payment request
            $paymentRequest = new PagSeguroPaymentRequest();

            // Set cms version
            PagSeguroLibrary::setCMSVersion('woocommerce-v.'.$woocommerce->version);
            
            // Set plugin version
            PagSeguroLibrary::setModuleVersion('woocommerce-v.'.$this->plugin_version);
            
            // Set charset
            PagSeguroConfig::setApplicationCharset($this->charset);
            
            // Sets the currency
            $paymentRequest->setCurrency(PagSeguroCurrencies::getIsoCodeByName("REAL"));
            
            // Set a reference
            $paymentRequest->setReference($this->invoice_prefix . $order->id);
            
            //Sets shipping data
            $paymentRequest->setShippingAddress($order->billing_postcode, $order->billing_address_1, '', $order->billing_address_2, '', $order->billing_city, $order->billing_state, $order->billing_country);
            $paymentRequest->setShippingCost($order->order_shipping);
            $paymentRequest->setShippingType(PagSeguroShippingType::getCodeByType('NOT_SPECIFIED'));
            
            // Sets your customer information.
            $paymentRequest->setSender($order->billing_first_name. ' ' . $order->billing_last_name, $order->billing_email, substr( $order->billing_phone, 0, 2 ), substr( $order->billing_phone, 2 ));
              
            // Sets the url used by PagSeguro for redirect user after ends checkout process
            if(!empty($this->url_redirect)){
                $paymentRequest->setRedirectUrl($this->url_redirect);
            }else{
                $paymentRequest->setRedirectUrl($this->get_return_url($order));
            }
            
            // Sets the url used by PagSeguro for redirect user after ends checkout process
            if(!empty($this->url_notification)){
                $paymentRequest->setNotificationURL($this->url_notification);
            }else{
                $paymentRequest->setNotificationURL(home_url().'/index.php?notificationurl=true');
            }
            
            //Sets Items
            if ( sizeof( $order->get_items() ) > 0 ) {
                $paymentRequest->setItems($this->setItems($order));
            }
            
            // Sets the sum of discounts
            $paymentRequest->setExtraAmount((($order->order_discount + $order->cart_discount) * -1)+($order->order_tax + $order->order_shipping_tax + $order->prices_include_tax));
            
            try {
                $credentials = new PagSeguroAccountCredentials($this->email, $this->token);
                return $paymentRequest->register($credentials);
            } catch (PagSeguroServiceException $e){
                $woocommerce->add_error(__( 'Sorry, unfortunately there was an error during checkout. Please contact the store administrator if the problem persists.', 'wpwcpagseguro'));
                $woocommerce->show_messages();
                wp_die(); 
            }
        }
        
        /**
         * Process the payment and return the result.
         *
         * @param int $order_id
         *
         * @return array
         */
        public function process_payment( $order_id ) {
            global $woocommerce;
            
            $order = new WC_Order( $order_id );

            $url = $this->payment($order);
            
            if ( 'yes' == $this->debug ) {
                $this->log->add( 'pagseguro', 'Payment arguments for order #' . $order_id . ': ' . print_r( $order, true ) );
            }
            
            //Remove Items Cart
            $woocommerce->cart->empty_cart();
            
            //Update status to Awaiting payment
            $modal_pagseguro = new WP_WC_Modal_Pagseguro();
            
            $array_order = $modal_pagseguro->getOrderStatus();
            $key = $modal_pagseguro->getKeyOrderStatusByName($array_order[1]);
            
            $modal_pagseguro->updateOrder($order_id, $key);
            $modal_pagseguro->saveHistoric($order_id, $modal_pagseguro->getNameOrderStatusByKey($key), true);
            
            
            return array(
                'result'    => 'success',
                'redirect'  => $url
            );
        }
        
        /**
         * Adds error message when not configured the email.
         *
         * @return string Error Mensage.
         */
        public function mail_missing_message() {
            $html = '<div class="error">';
                $html .= '<p>' . sprintf( __( 'You should inform your PagSeguro account email.', 'wpwcpagseguro' ), '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&amp;tab=payment_gateways">', '</a>' ) . '</p>';
            $html .= '</div>';

            echo $html;
        }
        
        /**
         * Adds error message when not configured the token.
         *
         * @return string Error Mensage.
         */
        public function token_missing_message() {
            $html = '<div class="error">';
                $html .= '<p>' .sprintf( __( 'You should inform your PagSeguro token.', 'wpwcpagseguro' ), '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&amp;tab=payment_gateways">', '</a>' ) . '</p>';
            $html .= '</div>';

            echo $html;
        }


        public function returnPathLog(){
            return (isset($this->path_log) == TRUE && empty($this->path_log) == FALSE) ? ABSPATH.$this->path_log.'/PagSeguro.log' : null;
        }
        
        public function createLogFile($file){
            if(!is_null($file)){
                try{
                    $f = fopen($file, "a");
                    fclose($f);
                }
                    catch(Exception $e){
                    die($e);
                }
            }
        }
        
    }
} 

/**
 *  Call actions for notification
 */
if(isset($_REQUEST['notificationurl']) && $_REQUEST['notificationurl'] == "true" && isset($_POST)){
    require_once "wpwcpagseguro_notification.php";
    
    $notification = new WP_WC_Pagseguro_Notification();
    $notification->index($_POST);
}
