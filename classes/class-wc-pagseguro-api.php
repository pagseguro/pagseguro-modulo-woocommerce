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
     *
     * @param $settings
     *
     * @throws Exception
     */
    public function __construct($settings)
    {
        global $woocommerce;

        $this->settings = $settings;

        \PagSeguro\Library::initialize();
        \PagSeguro\Library::cmsVersion()->setName('woocommerce-v')->setRelease($woocommerce->version);
        \PagSeguro\Library::moduleVersion()->setName('woocommerce-v')->setRelease($woocommerce->plugin_version);

        \PagSeguro\Configuration\Configure::setCharset($this->settings['charset']);
        \PagSeguro\Configuration\Configure::setEnvironment($this->settings['environment']);

        $this->invoice_prefix = !empty($this->settings['invoice_prefix']) ? $this->settings['invoice_prefix'] : 'WC-';
    }

    /**
     * Request a new PagSeguro checkout
     *
     * @param WC_Order $order
     * @param          $onlyCode boolean
     *
     * @return string
     */
    public function checkout($order, $onlyCode = false)
    {
        global $woocommerce;

        $request = new PagSeguro\Domains\Requests\Payment();
        $request->setCurrency("BRL");
        $request->setReference($this->invoice_prefix . $order->get_id());
        $request->setShipping()->setAddress()->withParameters(
            $order->get_billing_address_1(),
            null,
            null,
            $order->get_billing_postcode(),
            $order->get_billing_city(),
            $order->get_billing_state(),
            $order->get_billing_country(),
            $order->get_billing_address_2()
        );
        $request->setShipping()->setCost()->withParameters($order->get_shipping_total());
        $request->setShipping()->setType()->withParameters(PagSeguro\Enum\Shipping\Type::NOT_SPECIFIED);

        $request->setSender()->setName(
            sprintf('%s %s', $order->get_billing_first_name(), $order->get_billing_last_name())
        );
        $request->setSender()->setEmail($order->get_billing_email());
        $request->setSender()->setPhone()->withParameters(
            substr($order->get_billing_phone(), 0, 2),
            substr($order->get_billing_phone(), 2)
        );

        $request->setNotificationUrl($this->get_notification_url());
        $request->setRedirectUrl($this->get_redirect_url());

        //Sets Items
        if (sizeof($order->get_items()) > 0) {
            $request = $this->set_items($request, $order);
        }

        if ($this->get_extra_amount($order)) {
            $request->setExtraAmount($this->get_extra_amount($order));
        }

        try {
            return $request->register($this->get_account_credentials(), $onlyCode);
        } catch (Exception $e) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice(__('Sorry, unfortunately there was an error during checkout. Please contact the store administrator if the problem persists.',
                    'wpwcpagseguro'));
            } else {
                $woocommerce->add_error(__('Sorry, unfortunately there was an error during checkout. Please contact the store administrator if the problem persists.',
                    'wpwcpagseguro'));
                $woocommerce->show_messages();
            }
            wp_die();
        }
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

    /**
     * Set request items
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *                                                           | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *                                                           $request
     * @param WC_Order                                                                                                                                                        $order
     *
     * @return mixed
     */
    private function set_items($request, $order)
    {
        global $woocommerce;
        $count = 1;
        foreach ($order->get_items() as $item) {
            if ($item['qty']) {
                $weight = 0;
                foreach ($woocommerce->cart->cart_contents as $cart_item_key => $values) {
                    $_product = $values['data'];
                    if ($_product->get_id() == $item['product_id']) {
                        $weight = $_product->get_weight() * 1000;
                        break;
                    }
                }

                $request->addItems()->withParameters(
                    $count,
                    $item['name'],
                    $item['qty'],
                    $order->get_item_total($item, false),
                    $weight
                );
                $count++;
            }
        }

        return $request;
    }

    /**
     * Get extra amount
     *
     * @param WC_Order $order
     *
     * @return mixed
     */
    private function get_extra_amount($order)
    {
        return ($order->get_total_discount() * -1) + ($order->get_total_tax());
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
     * Request a direct payment boleto checkout
     *
     * @param WC_Order $order
     * @param          $data
     *
     * @return string
     * @throws Exception
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
     * Set direct paymnet data
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *                                                           | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *                                                           $request
     * @param WC_Order                                                                                                                                                        $order
     * @param                                                                                                                                                                 $data
     *
     * @throws Exception
     */
    private function direct_payment($request, $order, $data)
    {
        $request->setMode('DEFAULT');
        $request->setCurrency("BRL");
        $request->setReference($this->invoice_prefix . $order->get_id());
        $request->setNotificationUrl($this->get_notification_url());
        $request->setRedirectUrl($this->get_redirect_url());

        $this->set_shipping($request, $order);
        $this->set_sender($request, $order, $data);

        if (sizeof($order->get_items()) > 0) {
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
     * Set shipping data
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *                                                           | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *                                                           $request
     * @param WC_Order                                                                                                                                                        $order
     */
    private function set_shipping($request, $order)
    {
        $address_1 = explode(', ', $order->get_billing_address_1());
        $address_2 = explode(', ', $order->get_billing_address_2());

        $request->setShipping()->setAddress()->withParameters(
            $address_1[0],
            (isset($address_1[1]) ? $address_1[1] : null),
            $address_2[0],
            $order->get_billing_postcode(),
            $order->get_billing_city(),
            $order->get_billing_state(),
            $order->get_billing_country(),
            (isset($address_2[1]) ? $address_2[1] : null)
        );

        $request->setShipping()->setCost()->withParameters((float)$order->get_shipping_total());
        $request->setShipping()->setType()->withParameters(PagSeguro\Enum\Shipping\Type::NOT_SPECIFIED);
    }

    /**
     * Set sender data
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *                                                           | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *                                                           $request
     * @param WC_Order                                                                                                                                                        $order
     * @param                                                                                                                                                                 $data
     *
     * @throws WC_Data_Exception
     */
    private function set_sender($request, $order, $data)
    {
        $request->setSender()->setName(
            sprintf('%s %s', $order->get_billing_first_name(), $order->get_billing_last_name())
        );

        if ($this->settings['environment'] == 'sandbox') {
            $order->set_billing_email('woocommerce@sandbox.pagseguro.com.br');
        }
        $request->setSender()->setEmail($order->get_billing_email());
        $request->setSender()->setPhone()->withParameters(
            substr($order->get_billing_phone(), 0, 2),
            substr($order->get_billing_phone(), 2)
        );

        $request->setSender()->setHash($data['sender_hash']);
        $request->setSender()->setDocument()->withParameters(
            (strlen($data['sender_document']) <= 11) ? 'CPF' : 'CNPJ',
            $data['sender_document']
        );
    }

    /**
     * Get bank
     *
     * @param $bank
     *
     * @return mixed
     * @throws Exception
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
            5 => 'hsbc',
        ];
    }

    /**
     * Set billing data
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *                                                           | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *                                                           $request
     * @param WC_Order                                                                                                                                                        $order
     */
    private function set_billing($request, $order)
    {
        $address_1 = explode(', ', $order->get_billing_address_1());
        $address_2 = explode(', ', $order->get_billing_address_2());

        //Set billing information for credit card
        $request->setBilling()->setAddress()->withParameters(
            $address_1[0],
            $address_1[1],
            $address_2[0],
            $order->get_billing_postcode(),
            $order->get_billing_city(),
            $order->get_billing_state(),
            $order->get_billing_country(),
            (isset($address_2[1]) ? $address_2[1] : null)
        );
    }

    /**
     * Set holder data
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *                                                           | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *                                                           $request
     * @param WC_Order                                                                                                                                                        $order
     * @param                                                                                                                                                                 $data
     */
    private function set_holder($request, $order, $data)
    {
        // Set the credit card holder information
        $request->setHolder()->setBirthdate($data['holder_birthdate']);
        $request->setHolder()->setName(preg_replace('/( )+/', ' ', $data['holder_name'])); // Equals in Credit Card
        $request->setHolder()->setPhone()->withParameters(
            substr($order->get_billing_phone(), 0, 2),
            substr($order->get_billing_phone(), 2)
        );
        $request->setHolder()->setDocument()->withParameters(
            (strlen($data['sender_document']) <= 11) ? 'CPF' : 'CNPJ',
            $data['sender_document']
        );
    }

    /**
     * Request a direct payment debit checkout
     *
     * @param WC_Order $order
     * @param          $data
     *
     * @return string
     * @throws Exception
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
     * @param WC_Order $order
     * @param          $data
     *
     * @return string
     * @throws Exception
     * @throws WC_PagSeguro_Exception
     */
    public function direct_payment_cc_card($order, $data)
    {
        $request = new \PagSeguro\Domains\Requests\DirectPayment\CreditCard();
        $extra_amount = $this->get_extra_amount($order) + $this->fix_installments_value($order);
        $request->setExtraAmount($extra_amount);

        $this->direct_payment($request, $order, $data);
        try {
            return $request->register($this->get_account_credentials());
        } catch (\Exception $exception) {
            throw new WC_PagSeguro_Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Fix rounding inconsistency
     * 
     * @param  WC_Order $order
     * @return float
     */
    private function fix_installments_value($order) {
        $total_rounding_before = 0;
        $total_rounding_after = 0;

        foreach ($order->get_items() as $item_id => $item_data) {
            $total = $item_data->get_total();
            $quantity = $item_data->get_quantity();

            $total_rounding_after += $total * $quantity;
            $total_rounding_before += round($total, 2) * $quantity;
        }

        $total_rounding_after = round($total_rounding_after, 2);

        return round($total_rounding_after - $total_rounding_before, 2);
    }

    /**
     * @param WC_Order $order
     * @param          $data
     *
     * @return \PagSeguro\Services\Pagseguro\Domains\Responses\Installments
     * @throws Exception
     */
    public function get_installments($order, $data)
    {
        return \PagSeguro\Services\Installment::create(
            $this->get_account_credentials(),
            [
                'amount'     => $order->get_total(),
                'card_brand' => $data['credit_card_brand'],
            ]
        );
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function create_session()
    {
        return \PagSeguro\Services\Session::create(
            $this->get_account_credentials()
        )->getResult();
    }

    /**
     * @return mixed
     * @throws Exception
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
}