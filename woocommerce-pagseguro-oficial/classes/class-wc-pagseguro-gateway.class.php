<?php
/**
 * WC PagSeguro Gateway Class.
 *
 */
class WC_PagSeguro_Gateway extends WC_Payment_Gateway
{

    public function __construct()
    {

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));

        $this->id             = 'pagseguro';
        $this->has_fields     = false;
        $this->method_title   = __('PagSeguro', 'woocomerce-pagseguro-oficial');
        $this->icon           = plugins_url( 'assets/images/ps-logo.png', __FILE__ );

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->title            = $this->settings['title'];
        $this->description      = $this->settings['description'];
        $this->email            = $this->settings['email'];
        $this->token            = $this->settings['token'];
        $this->environment      = $this->settings['environment'];
        $this->debug            = $this->settings['debug'];
        $this->path_log         = $this->settings['path_log'];

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

        // Checks that the currency is supported
        if ( ! $this->support_currency() ) {
            add_action( 'admin_notices', array( $this, 'currency_not_supported_message' ) );
        }

        // Active logs.
        if ( 'yes' == $this->debug ) {
            if ( class_exists( 'WC_Logger' ) ) {
                $this->log = new WC_Logger();
            } else {
                global $woocommerce;
                $this->log = $woocommerce->logger();
            }
            PagSeguro\Configuration\Configure::setLog(true, $this->return_path_log());
        }

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Use PagSeguroLibrary
     *
     * @param type $order
     * @return type
     */
    public function payment($order){
        $api = new WC_PagSeguro_Api($this->settings);
        return $api->checkout($order);

    }

    /**
     *
     */
    public function process_nofitication()
    {
        $api = new WC_PagSeguro_Api($this->settings);
        $transaction = $api->notification();

        $order_id = str_replace('WC-','',$transaction->getReference());

        $order = new WC_Order( $order_id );

        //Update status
        $modal_pagseguro = new WC_Pagseguro_Model();
        $array_order = $modal_pagseguro->getOrderStatus();
        $order->update_status($array_order[$transaction->getStatus()], 'order_note');

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
        $modal_pagseguro = new WC_Pagseguro_Model();

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
     * Initialise Gateway Settings Form Fields.
     *
     * @return void
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'woocomerce-pagseguro-oficial' ),
                'type' => 'checkbox',
                'label' => __( 'Enable module?', 'woocomerce-pagseguro-oficial' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Title', 'woocomerce-pagseguro-oficial' ),
                'type' => 'text',
                'description' => __( 'Checkout title.', 'woocomerce-pagseguro-oficial' ),
                'default' => __( 'PagSeguro', 'woocomerce-pagseguro-oficial' )
            ),
            'description' => array(
                'title' => __( 'Description', 'woocomerce-pagseguro-oficial' ),
                'type' => 'textarea',
                'description' => __( 'Checkout description.', 'woocomerce-pagseguro-oficial' ),
                'default' => __( 'Choose between many payment methods, including credit cards and Brazilian banks. Pay securely and quickly with PagSeguro', 'wpwcpagseguro' )
            ),
            'email' => array(
                'title' => __( 'E-Mail', 'woocomerce-pagseguro-oficial' ),
                'type' => 'text',
                'description' => __( 'Do not have a PagSeguro account? <a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=11&tipo=cadastro#!vendedor" target="_blank">Click here </a> and register for free.', 'wpwcpagseguro' ),
                'default' => ''
            ),
            'token' => array(
                'title' => __( 'Token', 'woocomerce-pagseguro-oficial' ),
                'type' => 'text',
                'description' => __( 'Do not have or do not know your token? <a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank">Click here </a> to generate a new one.', 'wpwcpagseguro' ),
                'default' => ''
            ),
            'environment' => array(
                'title' => __('Choose your environment', 'woocomerce-pagseguro-oficial' ),
                'type' => 'select',
                'options' => array(
                    'production' => 'Production',
                    'sandbox' => 'Sandbox'
                ),
                'default' => ''
            ),
            'url_redirect' => array(
                'title' => __( 'Redirect URL', 'woocomerce-pagseguro-oficial' ),
                'type' => 'text',
                'description' => __( 'Your customer will be redirected back to your store or to the URL entered in this field. <a href="https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml" target="_blank">Click here </a> to activate.' , 'wpwcpagseguro' )
            ),
            'url_notification' => array(
                'title' => __( 'Notification URL', 'woocomerce-pagseguro-oficial' ),
                'type' => 'text',
                'description' => __( 'Whenever a transaction change its status, the PagSeguro sends a notification to your store or to the URL entered in this field.', 'wpwcpagseguro' ),
                'default' => home_url().'/index.php?notificationurl=true'
            ),
            'invoice_prefix' => array(
                'title' => __( 'Invoice Prefix', 'woocomerce-pagseguro-oficial' ),
                'type' => 'text',
                'description' => __( 'Prefix for your invoice numbers.', 'woocomerce-pagseguro-oficial' ),
                'default' => 'WC-'
            ),
            'charset' => array(
                'title' => __( 'Charset', 'woocomerce-pagseguro-oficial' ),
                'type' => 'text',
                'default' => 'UTF-8',
                'description' => __( 'Set the charset according to the coding of your system.', 'woocomerce-pagseguro-oficial' ),
            ),
            'debug' => array(
                'title' => __( 'Debug Log', 'woocomerce-pagseguro-oficial' ),
                'type' => 'checkbox',
                'label' => __( 'Create log file?', 'woocomerce-pagseguro-oficial' ),
                'default' => 'no',
            ),
            'path_log' => array(
                'type' => 'text',
                'default' => '',
                'description' => __( 'Path to the log file.', 'woocomerce-pagseguro-oficial' ).' Ex.: wp-content/logs',
            )
        );
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
     * @return null|string
     */
    public function return_path_log(){
        return (isset($this->path_log) == TRUE && empty($this->path_log) == FALSE) ? ABSPATH.$this->path_log.'/PagSeguro.log' : null;
    }

    /**
     * Returns a bool that indicates if currency is amongst the supported ones.
     *
     * @return bool
     */
    protected function support_currency() {
        return in_array( get_woocommerce_currency(), array( 'BRL') );
    }

    /**
     * Adds error message when not configured the email.
     *
     * @return string Error Mensage.
     */
    public function mail_missing_message() {
        $html  = '<div class="error">';
        $html .= '<p>' . sprintf( __( 'You should inform your PagSeguro account email.', 'woocomerce-pagseguro-oficial' ), '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&amp;tab=payment_gateways">', '</a>' ) . '</p>';
        $html .= '</div>';
        echo $html;
    }

    /**
     * Adds error message when not configured the token.
     *
     * @return string Error Mensage.
     */
    public function token_missing_message() {
        $html  = '<div class="error">';
        $html .= '<p>' .sprintf( __( 'You should inform your PagSeguro token.', 'woocomerce-pagseguro-oficial' ), '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&amp;tab=payment_gateways">', '</a>' ) . '</p>';
        $html .= '</div>';
        echo $html;
    }

    /**
     * Adds error message when an unsupported currency is used.
     *
     * @return string
     */
    public function currency_support_message() {
        $html  = '<div class="error">';
        $html .= '<p>'.sprintf( __( 'Currency <code>%s</code> is not supported. Please make sure that you use one of the following supported currencies: BRL', 'woocomerce-pagseguro-oficial' ), get_woocommerce_currency() ) . '</p>';
        $html .= '</div>';
        echo $html;
    }

}