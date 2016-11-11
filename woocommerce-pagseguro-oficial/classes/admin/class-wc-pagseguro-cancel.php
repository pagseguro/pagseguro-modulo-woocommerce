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

if ( ! class_exists('WC_PagSeguro_Cancel')):

/**
 * Class WC_PagSeguro_Admin
 */
class WC_PagSeguro_Cancel extends WC_PagSeguro_Methods
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
        \PagSeguro\Library::moduleVersion()->setName('woocommerce-pagseguro-v')->setRelease('');

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
                    if (in_array($transaction->getStatus(), [1, 2])) {
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
        $data = $this->sanitize_config($this->data['data']);
        $this->is_conciliated($data);

        try {
            $this->cancel($data);
            $order = new WC_Order( $data->order_id );
            $order->update_status('wc-'.$this->array_order_status[7], 'order_note');
            WC_PagSeguro_Payload::when_success();
        } catch (\Exception $exception) {
            WC_PagSeguro_Payload::when_error($exception->getMessage());
        }

    }

    /**
     * Execute cancellation
     *
     * @param $config
     * @return bool
     * @throws \Exception
     */
    private function cancel($config)
    {
        if ($this->request_cancel($config)->getResult() == "OK")
            return true;
        return false;
    }

    /**
     * Request a PagSeguro Cancel
     *
     * @param $config
     * @return \PagSeguro\Parsers\Cancel\Response
     * @throws \Exception
     */
    private function request_cancel($config)
    {
        \PagSeguro\Configuration\Configure::setEnvironment($this->options['environment']);
        try {
            return \PagSeguro\Services\Transactions\Cancel::create(
                new \PagSeguro\Domains\AccountCredentials($this->options['email'], $this->options['token']),
                $config->pagseguro_id
            );
        } catch (\Exception $exception) {
            WC_PagSeguro_Payload::when_error($exception->getMessage());
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
        return $this->toArray($payment, $order, $this->check_conciliation($payment, $order));
    }

    /**
     * Create array
     *
     * @param $payment
     * @param $order
     * @param $conciliate
     * @return array
     */
    private function toArray($payment, $order, $conciliate = false)
    {
        return  [
            'date'             => $this->date_format($payment->getDate()),
            'wordpress_id'     => $order->get_order_number(),
            'wordpress_status' => wc_get_order_status_name($order->get_status()),
            'pagseguro_id'     => $payment->getCode(),
            'details'          => $this->details($order, $payment, ['conciliate' => $conciliate])
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
        return $this->encrypt('!@#$GfdsdgRGF4s35%#$%%',
            json_encode([
                'order_id'         => $order->get_order_number(),
                'pagseguro_status' => $this->array_order_status[$payment->getStatus()],
                'pagseguro_id'     => $payment->getCode(),
                'need_conciliate'  => $options['conciliate']
            ])
        );
    }

    /**
     * Check for conciliation
     *
     * @param $payment
     * @param $order
     * @return bool
     */
    private function check_conciliation($payment, $order)
    {
        if ($order->get_status() == $this->array_order_status[$payment->getStatus()])
            return true;
        return false;
    }

    private function is_conciliated($config)
    {
        if (!$config->need_conciliate) {
            WC_PagSeguro_Payload::when_error('need to conciliate');
        }
        return true;
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
