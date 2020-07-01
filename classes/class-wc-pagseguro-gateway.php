<?php
/**
 ************************************************************************
 * Copyright [2016] [PagSeguro Internet Ltda.]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WC PagSeguro Gateway Class.
 *
 */
class WC_PagSeguro_Gateway extends WC_Payment_Gateway
{
    /**
     *
     */
    const STANDARD_JS = "https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js";
    /**
     *
     */
    const SANDBOX_JS = "https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js";
    /**
     *
     */
    const DIRECT_PAYMENT_URL = "https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js";
    /**
     *
     */
    const DIRECT_PAYMENT_URL_SANDBOX = "https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js";

    public function __construct()
    {
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [&$this, 'process_admin_options']);

        $this->id = 'pagseguro';
        $this->has_fields = false;
        $this->method_title = __('PagSeguro', 'woocomerce-pagseguro-oficial');
        $this->icon = plugins_url('/assets/images/logo_pagseguro200x41.png', PS_PLUGIN_DIR);

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
        $this->email = $this->settings['email'];
        $this->token = $this->settings['token'];
        $this->environment = $this->settings['environment'];
        $this->checkout = $this->settings['checkout'];
        $this->debug = $this->settings['debug'];
        $this->path_log = $this->settings['path_log'];

        $this->enabled = 'no';
        if ((('yes' == $this->settings['enabled']) && !empty($this->email) && !empty($this->token) && $this->is_valid_for_use())) {
            $this->enabled = 'yes';
        }

        // Checks if email is not empty.
        if (empty($this->email)) {
            add_action('admin_notices', [&$this, 'mail_missing_message']);
        }

        // Checks if token is not empty.
        if (empty($this->token)) {
            add_action('admin_notices', [&$this, 'token_missing_message']);
        }

        // Checks that the currency is supported
        if (!$this->support_currency()) {
            add_action('admin_notices', [$this, 'currency_not_supported_message']);
        }

        // Active logs.
        if ('yes' == $this->debug) {
            if (class_exists('WC_Logger')) {
                $this->log = new WC_Logger();
            } else {
                global $woocommerce;
                $this->log = $woocommerce->logger();
            }
            PagSeguro\Configuration\Configure::setLog(true, $this->return_path_log());
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * Initialise Gateway Settings Form Fields.
     *
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled'          => [
                'title'   => __('Enable/Disable', 'woocomerce-pagseguro-oficial'),
                'type'    => 'checkbox',
                'label'   => __('Enable module?', 'woocomerce-pagseguro-oficial'),
                'default' => 'yes',
            ],
            'title'            => [
                'title'       => __('Title', 'woocomerce-pagseguro-oficial'),
                'type'        => 'text',
                'description' => __('Checkout title.', 'woocomerce-pagseguro-oficial'),
                'default'     => __('PagSeguro', 'woocomerce-pagseguro-oficial'),
            ],
            'description'      => [
                'title'       => __('Description', 'woocomerce-pagseguro-oficial'),
                'type'        => 'textarea',
                'description' => __('Checkout description.', 'woocomerce-pagseguro-oficial'),
                'default'     => __('Choose between many payment methods, including credit cards and Brazilian banks. Pay securely and quickly with PagSeguro',
                    'wpwcpagseguro'),
            ],
            'email'            => [
                'title'       => __('E-Mail', 'woocomerce-pagseguro-oficial'),
                'type'        => 'text',
                'description' => __('Do not have a PagSeguro account? <a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=11&tipo=cadastro#!vendedor" target="_blank">Click here </a> and register for free.',
                    'wpwcpagseguro'),
                'default'     => '',
            ],
            'token'            => [
                'title'       => __('Token', 'woocomerce-pagseguro-oficial'),
                'type'        => 'text',
                'description' => __('Do not have or do not know your token? <a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank">Click here </a> to generate a new one.',
                    'wpwcpagseguro'),
                'default'     => '',
            ],
            'environment'      => [
                'title'   => __('Environment', 'woocomerce-pagseguro-oficial'),
                'type'    => 'select',
                'options' => [
                    'production' => 'Production',
                    'sandbox'    => 'Sandbox',
                ],
                'default' => '',
            ],
            'checkout'         => [
                'title'   => __('Checkout', 'woocomerce-pagseguro-oficial'),
                'type'    => 'select',
                'options' => [
                    'standard' => 'Padrão',
                    'lightbox' => 'Lightbox',
                    'direct'   => 'Checkout Transparente',
                ],
                'default' => '',
            ],
            'url_redirect'     => [
                'title'       => __('Redirect URL', 'woocomerce-pagseguro-oficial'),
                'type'        => 'text',
                'description' => __('Your customer will be redirected back to your store or to the URL entered in this field. <a href="https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml" target="_blank">Click here </a> to activate.',
                    'wpwcpagseguro'),
            ],
            'url_notification' => [
                'title'       => __('Notification URL', 'woocomerce-pagseguro-oficial'),
                'type'        => 'text',
                'description' => __('Whenever a transaction change its status, the PagSeguro sends a notification to your store or to the URL entered in this field.',
                    'wpwcpagseguro'),
                'default'     => home_url() . '/index.php?notificationurl=true',
            ],
            'invoice_prefix'   => [
                'title'       => __('Invoice Prefix', 'woocomerce-pagseguro-oficial'),
                'type'        => 'text',
                'description' => __('Prefix for your invoice numbers.', 'woocomerce-pagseguro-oficial'),
                'default'     => sprintf('%s-', strtoupper(uniqid('WC-'))),
            ],
            'charset'          => [
                'title'       => __('Charset', 'woocomerce-pagseguro-oficial'),
                'type'        => 'text',
                'default'     => 'UTF-8',
                'description' => __('Set the charset according to the coding of your system.',
                    'woocomerce-pagseguro-oficial'),
            ],
            'debug'            => [
                'title'   => __('Debug Log', 'woocomerce-pagseguro-oficial'),
                'type'    => 'checkbox',
                'label'   => __('Create log file?', 'woocomerce-pagseguro-oficial'),
                'default' => 'no',
            ],
            'path_log'         => [
                'type'        => 'text',
                'default'     => '',
                'description' => __('Path to the log file.', 'woocomerce-pagseguro-oficial') . ' Ex.: wp-content/logs',
            ],
        ];
    }

    /**
     * Check if this gateway is enabled and available in the user's country.
     *
     * @return bool
     */
    public function is_valid_for_use()
    {
        if (!in_array(get_woocommerce_currency(), ['BRL'])) {
            return false;
        }

        return true;
    }

    /**
     * Returns a bool that indicates if currency is amongst the supported ones.
     *
     * @return bool
     */
    protected function support_currency()
    {
        return in_array(get_woocommerce_currency(), ['BRL']);
    }

    /**
     * @return null|string
     */
    public function return_path_log()
    {
        return (isset($this->path_log) == true && empty($this->path_log) == false) ? ABSPATH . $this->path_log . '/PagSeguro.log' : null;
    }

    /**
     *
     */
    public function process_nofitication()
    {
        $api = new WC_PagSeguro_Api($this->settings);
        $transaction = $api->notification();

        $order_id = explode('-', $transaction->getReference())[2];

        $order = new WC_Order($order_id);

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
     * @throws Exception
     */
    public function process_payment($order_id)
    {
        global $woocommerce;

        $url = null;

        $order = new WC_Order($order_id);

        if ('yes' == $this->debug) {
            $this->log->add('pagseguro', 'Payment arguments for order #' . $order_id . ': ' . print_r($order, true));
        }

        $array_order = WC_Pagseguro_Model::$array_order_status;

        $order->update_status($array_order[1]);

        if ($this->checkout == 'lightbox') {
            $code = $this->payment($order, true);
            $url = get_permalink(get_page_by_path('pagseguro/checkout'));
            add_user_meta(get_current_user_id(), '_pagseguro_data', [
                'code' => $code,
                'js'   => ($this->environment == 'sandbox') ? self::SANDBOX_JS : self::STANDARD_JS,
            ]);
        }

        if ($this->checkout == 'direct') {
            $url = get_permalink(get_page_by_path('pagseguro/direct-payment'));
            add_user_meta(get_current_user_id(), '_pagseguro_data', [
                'js'           => ($this->environment == 'sandbox') ? self::DIRECT_PAYMENT_URL_SANDBOX : self::DIRECT_PAYMENT_URL,
                'order_id'     => $order_id,
                'session_code' => $this->create_session(),
            ]);
        }

        if ($this->checkout == 'standard') {
            $url = $this->payment($order);
        }

        return [
            'result'   => 'success',
            'redirect' => $url,
        ];
    }

    /**
     * Use PagSeguroLibrary
     *
     * @param WC_Order $order
     * @param boolean  $onlyCode
     *
     * @return string
     */
    public function payment($order, $onlyCode = false)
    {
        $api = new WC_PagSeguro_Api($this->settings);

        return $api->checkout($order, $onlyCode);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function create_session()
    {
        $api = new WC_PagSeguro_Api($this->settings);

        return $api->create_session();
    }

    /**
     * Adds error message when not configured the email.
     *
     * @return string Error Mensage.
     */
    public function mail_missing_message()
    {
        $html = '<div class="error">';
        $html .= '<p>' . sprintf(__('You should inform your PagSeguro account email.', 'woocomerce-pagseguro-oficial'),
                '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&amp;tab=payment_gateways">',
                '</a>') . '</p>';
        $html .= '</div>';
        echo $html;
    }

    /**
     * Adds error message when not configured the token.
     *
     * @return string Error Mensage.
     */
    public function token_missing_message()
    {
        $html = '<div class="error">';
        $html .= '<p>' . sprintf(__('You should inform your PagSeguro token.', 'woocomerce-pagseguro-oficial'),
                '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&amp;tab=payment_gateways">',
                '</a>') . '</p>';
        $html .= '</div>';
        echo $html;
    }

    /**
     * Adds error message when an unsupported currency is used.
     *
     * @return string
     */
    public function currency_support_message()
    {
        $html = '<div class="error">';
        $html .= '<p>' . sprintf(__('Currency <code>%s</code> is not supported. Please make sure that you use one of the following supported currencies: BRL',
                'woocomerce-pagseguro-oficial'), get_woocommerce_currency()) . '</p>';
        $html .= '</div>';
        echo $html;
    }
}
