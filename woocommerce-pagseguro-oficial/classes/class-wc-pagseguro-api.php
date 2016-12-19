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
     * @param $onlyCode boolean
     * @return string
     */
    public function checkout($order, $onlyCode = false)
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
            return $request->register($this->get_account_credentials(), $onlyCode);
        } catch (Exception $e){
            if ( function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( __( 'Sorry, unfortunately there was an error during checkout. Please contact the store administrator if the problem persists.', 'wpwcpagseguro' ));
            } else {
                $woocommerce->add_error(__( 'Sorry, unfortunately there was an error during checkout. Please contact the store administrator if the problem persists.', 'wpwcpagseguro'));
                $woocommerce->show_messages();
            }
            wp_die();
        }
    }

    /**
     * Request a direct payment boleto checkout
     *
     * @param $order
     * @param $data
     * @return string
     * @throws WC_PagSeguro_Exception
     */
    public function direct_payment_boleto($order, $data)
    {
        $request = new \PagSeguro\Domains\Requests\DirectPayment\Boleto();
        $this->direct_payment($request, $order, $data);
        try {
            return $request->register($this->get_account_credentials());
        } catch (\Exception $exception) {
            throw new WC_PagSeguro_Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Request a direct payment debit checkout
     *
     * @param $order
     * @param $data
     * @return string
     * @throws WC_PagSeguro_Exception
     */
    public function direct_payment_debit($order, $data)
    {
        $request = new \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit();
        $this->direct_payment($request, $order, $data);
        try {
            return $request->register($this->get_account_credentials());
        } catch (\Exception $exception) {
            throw new WC_PagSeguro_Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Request a direct payment credit card checkout
     *
     * @param $order
     * @param $data
     * @return string
     * @throws WC_PagSeguro_Exception
     */
    public function direct_payment_cc_card($order, $data)
    {
        $request = new \PagSeguro\Domains\Requests\DirectPayment\CreditCard();
        $this->direct_payment($request, $order, $data);
        try {
            return $request->register($this->get_account_credentials());
        } catch (\Exception $exception) {
            throw new WC_PagSeguro_Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Set direct paymnet data
     *
     * @param $request
     * @param $order
     * @param $data
     */
    private function direct_payment($request, $order, $data)
    {
        $request->setMode('DEFAULT');
        $request->setCurrency("BRL");
        $request->setReference($this->invoice_prefix . $order->id);
        $request->setNotificationUrl($this->get_notification_url());
        $request->setRedirectUrl($this->get_redirect_url());

        $this->set_shipping($request, $order);
        $this->set_sender($request, $order, $data);

        if ( sizeof( $order->get_items() ) > 0 ) {
            $request = $this->set_items($request, $order);
        }

        if ($this->get_extra_amount($order)) {
            $request->setExtraAmount($this->get_extra_amount($order));
        }

        if ($request instanceof \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit) {
            $request->setBankName($this->get_bank($data['bank_name']));
        }

        if ($request instanceof \PagSeguro\Domains\Requests\DirectPayment\CreditCard) {
            $this->set_billing($request, $order);
            $this->set_holder($request, $order, $data);
            $request->setToken($data['card_token']);
            $request->setInstallment()->withParameters($data['installment_quantity'], $data['installment_amount']);
        }
    }

    /**
     * @param $order
     * @param $data
     * @return \PagSeguro\Services\Pagseguro\Domains\Responses\Installments
     */
    public function get_installments($order, $data)
    {
        return \PagSeguro\Services\Installment::create(
            $this->get_account_credentials(),
            [
                'amount' => $order->get_total(),
                'card_brand' => $data['credit_card_brand'],
            ]
        );
    }

    /**
    * Set request items
    *
    * @param $request
    * @param $order
    * @return mixed
    */
    private function set_items($request, $order){
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
     * Set shipping data
     *
     * @param $request
     * @param $order
     */
    private function set_shipping($request, $order)
    {
        $address_1 = explode(', ', $order->billing_address_1);
        $address_2 = explode(', ', $order->billing_address_2);

        $request->setShipping()->setAddress()->withParameters(
            $address_1[0],
            $address_1[1],
            $address_2[0],
            $order->billing_postcode,
            $order->billing_city,
            $order->billing_state,
            $order->billing_country,
            $address_2[1]
        );

        $request->setShipping()->setCost()->withParameters((float)$order->order_shipping);
        $request->setShipping()->setType()->withParameters(PagSeguro\Enum\Shipping\Type::NOT_SPECIFIED);
    }

    /**
     * Set billing data
     *
     * @param $request
     * @param $order
     */
    private function set_billing($request, $order)
    {

        $address_1 = explode(', ', $order->billing_address_1);
        $address_2 = explode(', ', $order->billing_address_2);

        //Set billing information for credit card
        $request->setBilling()->setAddress()->withParameters(
            $address_1[0],
            $address_1[1],
            $address_2[0],
            $order->billing_postcode,
            $order->billing_city,
            $order->billing_state,
            $order->billing_country,
            $address_2[1]
        );
    }

    /**
     * Set sender data
     *
     * @param $request
     * @param $order
     * @param $data
     */
    private function set_sender($request, $order, $data)
    {
        $request->setSender()->setName(
            sprintf('%s %s', $order->billing_first_name,  $order->billing_last_name)
        );

        if ($this->settings['environment'] == 'sandbox') {
            $order->billing_email = "woocommerce@sandbox.pagseguro.com.br";
        }
        $request->setSender()->setEmail($order->billing_email);
        $request->setSender()->setPhone()->withParameters(
            substr( $order->billing_phone, 0, 2 ),
            substr( $order->billing_phone, 2 )
        );

        $request->setSender()->setHash($data['sender_hash']);
        $request->setSender()->setDocument()->withParameters(
            (strlen($data['sender_document']) <= 11) ? 'CPF' : 'CNPJ',
            $data['sender_document']
        );
    }

    /**
     * Set holder data
     *
     * @param $request
     * @param $order
     * @param $data
     */
    private function set_holder($request, $order, $data)
    {
        // Set the credit card holder information
        $request->setHolder()->setBirthdate($data['holder_birthdate']);
        $request->setHolder()->setName(preg_replace('/( )+/', ' ',$data['holder_name'])); // Equals in Credit Card
        $request->setHolder()->setPhone()->withParameters(
            substr( $order->billing_phone, 0, 2 ),
            substr( $order->billing_phone, 2 )
        );
        $request->setHolder()->setDocument()->withParameters(
            (strlen($data['sender_document']) <= 11) ? 'CPF' : 'CNPJ',
            $data['sender_document']
        );
    }

    /**
     * Get bank
     */
    private function get_bank($bank)
    {
        try {
            return $this->bank_list()[$bank];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Get bank list
     *
     * @return array
     */
    private function bank_list()
    {
        return [
            1 => 'itau',
            2 => 'bradesco',
            3 => 'banrisul',
            4 => 'bancodobrasil',
            5 => 'hsbc'
        ];
    }

    /**
     * @return mixed
     */
    public function create_session()
    {
        return \PagSeguro\Services\Session::create(
            $this->get_account_credentials()
        )->getResult();
    }

    /**
     * @return mixed
     */
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
