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

if ( ! class_exists('WC_PagSeguro_Conciliation')):

/**
 * Class WC_PagSeguro_Admin
 */
class WC_PagSeguro_Conciliation extends WC_PagSeguro_Methods
{

    /**
     * @var int
     */
    protected $days;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var get_option()
     */
    protected $options;

    /**
     * @param $options
     */
    public function init($options)
    {
        global $woocommerce;
        $this->options = $options;
        $this->set_data();
        $this->set_days();

        \PagSeguro\Library::initialize();
        \PagSeguro\Library::cmsVersion()->setName('woocommerce-v')->setRelease($woocommerce->version);
        \PagSeguro\Library::moduleVersion()->setName('woocommerce-v')->setRelease('');

        \PagSeguro\Configuration\Configure::setCharset($this->options['charset']);
        \PagSeguro\Configuration\Configure::setEnvironment($this->options['environment']);

        if ($this->data['method'] == 'search') {
            $this->search();
        }
        if ($this->data['method'] == 'execute') {
            $this->execute();
        }
    }

    /**
     * Search
     */
    protected function search()
    {
        $array_payments = [];
        $response = $this->get_pagseguro_transactions();

        if (count($response->getTransactions()) > 1) {
            foreach ($response->getTransactions() as $transaction) {

                if ($this->options['invoice_prefix'] == $this->decrypt_reference_prefix($transaction->getReference())) {
                    $order_id = $this->decrypt_reference_order($transaction->getReference());
                    $order = new WC_Order($order_id);
                    if ($order->get_status() != $this->array_order_status[$transaction->getStatus()]) {
                        array_push($array_payments, $this->build($transaction, $order));
                    }
                }
            }
            WC_PagSeguro_Payload::when_success($array_payments);
        }
        WC_PagSeguro_Payload::when_success();
    }

    /**
     * Execute
     */
    protected function execute()
    {
        foreach ($this->data['data'] as $row) {
            $data = $this->sanitize_config($row);
            try {
                $order = new WC_Order( $data->order_id );
                $order->update_status('wc-'.$data->pagseguro_status, 'order_note');
                WC_PagSeguro_Payload::when_success();
            } catch (\Exception $exception) {
                WC_PagSeguro_Payload::when_error($exception->getMessage());
            }
        }
    }

    /**
     * Build data for dataTable
     *
     * @param $payment
     * @param $order
     * @return array
     */
    protected function build($payment, $order)
    {
        return $this->toArray($payment, $order);
    }

    /**
     * Create array
     *
     * @param $payment
     * @param $order
     * @return array
     */
    private function toArray($payment, $order)
    {
        return  [
            'date'             => $this->date_format($payment->getDate()),
            'wordpress_id'     => $order->get_order_number(),
            'wordpress_status' => wc_get_order_status_name($order->get_status()),
            'pagseguro_id'     => $payment->getCode(),
            'pagseguro_status' => wc_get_order_status_name($this->array_order_status[$payment->getStatus()]),
            'action'           => $order->get_view_order_url(),
            'details'          => $this->details($order, $payment)
        ];
    }

    /**
     * Get data for details
     *
     * @param $order
     * @param $payment
     * @param $options
     * @return string
     */
    protected function details($order, $payment, $options = null)
    {
        unset($options);
        return $this->encrypt('!@#$GfdsdgRGF4s35%#$%%',
            json_encode([
                'order_id'         => $order->get_order_number(),
                'pagseguro_status' => $this->array_order_status[$payment->getStatus()],
                'pagseguro_id'     => $payment->getCode()
            ])
        );
    }

    /**
     * Set days
     */
    private function set_days()
    {
        if (array_key_exists('date', $this->data))
            $this->days = $this->data['date'];
    }

    /**
     * Set data
     */
    private function set_data()
    {
        $this->data = filter_var_array($_REQUEST);
    }
}


endif;