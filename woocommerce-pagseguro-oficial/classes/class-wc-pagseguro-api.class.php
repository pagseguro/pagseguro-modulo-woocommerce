<?php

/**
 * Class WC_PagSeguro_Api
 */
class WC_PagSeguro_Api
{
    /**
     * @var
     */
    protected $settings;
    /**
     * @var string
     */
    protected $invoice_prefix;

    /**
     * WC_PagSeguro_Api constructor.
     * @param $settings
     */
    public function __construct($settings)
    {
        global $woocommerce;

        $this->settings = $settings;

        \PagSeguro\Library::initialize();
        \PagSeguro\Library::cmsVersion()->setName('woocommerce-v')->setRelease($woocommerce->version);
        \PagSeguro\Library::moduleVersion()->setName('woocommerce-v')->setRelease($this->plugin_version);

        \PagSeguro\Configuration\Configure::setCharset($this->settings['charset']);
        \PagSeguro\Configuration\Configure::setEnvironment($this->settings['environment']);

        $this->invoice_prefix = ! empty( $this->settings['invoice_prefix'] ) ? $this->settings['invoice_prefix'] : 'WC-';
    }

    /**
     * Request a new PagSeguro checkout
     *
     * @param $order
     * @return string
     */
    public function checkout($order)
    {

        global $woocommerce;

        $request = new PagSeguro\Domains\Requests\Payment();
        $request->setCurrency("BRL");
        $request->setReference($this->invoice_prefix . $order->id);
        $request->setShipping()->setAddress()->withParameters(
            $order->billing_address_1,
            null,
            null,
            $order->billing_postcode,
            $order->billing_city,
            $order->billing_state,
            $order->billing_country,
            $order->billing_address_2
        );
        $request->setShipping()->setCost()->withParameters($order->order_shipping);
        $request->setShipping()->setType()->withParameters(PagSeguro\Enum\Shipping\Type::NOT_SPECIFIED);

        $request->setSender()->setName(
            sprintf('%s %s', $order->billing_first_name,  $order->billing_last_name)
        );
        $request->setSender()->setEmail($order->billing_email);
        $request->setSender()->setPhone()->withParameters(
            substr( $order->billing_phone, 0, 2 ),
            substr( $order->billing_phone, 2 )
        );

        $request->setNotificationUrl($this->get_notification_url());
        $request->setRedirectUrl($this->get_redirect_url());

        //Sets Items
        if ( sizeof( $order->get_items() ) > 0 ) {
            $request = $this->set_items($request, $order);
        }

        if ($this->get_extra_amount($order)) {
            $request->setExtraAmount($this->get_extra_amount($order));
        }

        try {
            return $request->register($this->get_account_credentials());
        } catch (PagSeguroServiceException $e){
            if ( function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( __( 'Sorry, unfortunately there was an error during checkout. Please contact the store administrator if the problem persists.', 'wpwcpagseguro' ));
            } else {
                $woocommerce->add_error(__( 'Sorry, unfortunately there was an error during checkout. Please contact the store administrator if the problem persists.', 'wpwcpagseguro'));
                $woocommerce->show_messages();
            }
            //wp_die();
        }
    }

    public function notification()
    {
        if (\PagSeguro\Helpers\Xhr::hasPost()) {
            return \PagSeguro\Services\Transactions\Notification::check(
                $this->get_account_credentials()
            );
        } else {
            throw new \InvalidArgumentException($_POST);
        }
    }

    /**
     * Set request items
     *
     * @param $request
     * @param $order
     * @return mixed
     */
    public function set_items($request, $order){
        global $woocommerce;
        $count = 1;

        foreach ( $order->get_items() as $item ) {
            if ( $item['qty'] ) {
                foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $values ) {
                    $_product = $values['data'];
                    if($_product->id == $item['product_id']){
                        $weight = $_product->get_weight()* 1000;
                        break;
                    }
                }

                $request->addItems()->withParameters(
                    $count,
                    $item['name'],
                    $item['qty'],
                    $order->get_item_total( $item, false ),
                    $weight
                );
                $count++;
            }
        }
        return $request;
    }

    /**
     * Get account credentials
     *
     * @return \PagSeguro\Domains\AccountCredentials
     */
    private function get_account_credentials()
    {
        return new \PagSeguro\Domains\AccountCredentials($this->get_account_email(), $this->get_account_token());
    }

    /**
     * Get extra amount
     *
     * @param $order
     * @return mixed
     */
    private function get_extra_amount($order)
    {
        return (($order->order_discount + $order->cart_discount) * -1)+($order->order_tax + $order->order_shipping_tax + $order->prices_include_tax);
    }

    /**
     * Get account e-mail
     *
     * @return string
     */
    private function get_account_email()
    {
        return $this->settings['email'];
    }

    /**
     * Get account token
     *
     * @return string
     */
    private function get_account_token()
    {
        return $this->settings['token'];
    }

    /**
     * Get notification url
     *
     * @return mixed
     */
    private function get_notification_url()
    {
        return $this->settings['url_notification'];
    }

    /**
     * Get redirect url
     *
     * @return mixed
     */
    private function get_redirect_url()
    {
        return $this->settings['url_redirect'];
    }
}