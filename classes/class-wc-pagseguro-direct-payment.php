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

if ( ! class_exists('WC_PagSeguro_Direct_Payment')):
    
    class WC_PagSeguro_Direct_Payment
    {

        /**
         * @var WC_PagSeguro_Api
         */
        private $api;
        /**
         * @var mixed
         */
        private $data;

        /**
         * WC_PagSeguro_Direct_Payment constructor.
         * @param $data
         */
        public function __construct($data)
        {
            try {
                $this->data = $data;
                $this->api = new WC_PagSeguro_Api(
                    get_option('woocommerce_pagseguro_settings')
                );
            } catch (WC_PagSeguro_Exception $pagseguro_exception) {
                WC_PagSeguro_Payload::when_error($pagseguro_exception->getMessage());
            }
        }

        /**
         * Proceed to boleto checkout
         */
        public function boleto()
        {
            try {
                $data = $this->validate_data($this->data);
                $response = $this->api->direct_payment_boleto(
                    new WC_Order($data['order_id']),
                    $data
                );
                WC_PagSeguro_Payload::when_success($this->push_payment_link_to_array($response, $this->to_array()));
                exit;
            } catch (WC_PagSeguro_Exception $pagseguro_exception) {
                WC_PagSeguro_Payload::when_error($pagseguro_exception->getMessage());
            }
        }

        /**
         * Proceed to debit checkout
         */
        public function debit()
        {
            try {
                $data = $this->validate_data($this->data);
                $response = $this->api->direct_payment_debit(
                    new WC_Order($data['order_id']),
                    $data
                );
                WC_PagSeguro_Payload::when_success($this->push_payment_link_to_array($response, $this->to_array()));
                exit;
            } catch (WC_PagSeguro_Exception $pagseguro_exception) {
                WC_PagSeguro_Payload::when_error($pagseguro_exception->getMessage());
            }
        }

        /**
         * Proceed to credit card checkout
         */
        public function credit_card()
        {
            try {
                $data = $this->validate_data($this->data);
                $response = $this->api->direct_payment_cc_card(
                    new WC_Order($data['order_id']),
                    $data
                );
                WC_PagSeguro_Payload::when_success(array_merge($this->to_array(), [
                    'transaction' => [
                        'date' => $response->getDate(),
                        'code' => $response->getCode()
                    ]
                ]));
                exit;
            } catch (WC_PagSeguro_Exception $pagseguro_exception) {
                WC_PagSeguro_Payload::when_error($pagseguro_exception->getMessage());
            }
        }

        /**
         * Proceed to get installments
         */
        public function installments()
        {
            try {
                $response = $this->api->get_installments(
                    new WC_Order($this->data['order_id']),
                    $this->data
                );
                WC_PagSeguro_Payload::when_success(
                    $this->build_installment_response(
                        $response->getInstallments()
                    )
                );
                exit;
            } catch (WC_PagSeguro_Exception $pagseguro_exception) {
                WC_PagSeguro_Payload::when_error($pagseguro_exception->getMessage());
            }
        }

        /**
         * Build installment response
         * @param $installments
         * @return mixed
         */
        private function build_installment_response($installments)
        {
            array_walk($installments, function(&$item){
                $item = [
                    'cardBrand' => $item->getCardBrand(),
                    'quantity' => $item->getQuantity(),
                    'amount' => $item->getAmount(),
                    'totalAmount' => $item->getTotalAmount(),
                    'interestFree' => $item->getInterestFree(),
                    'text' => str_replace('.', ',', $this->get_installment_text($item))
                ];
            });
            return $installments;
        }

        /**
         * Validate non optional data
         * @param $data
         * @return mixed
         * @throws WC_PagSeguro_Exception
         */
        private function validate_data($data)
        {
            if (is_null($data['order_id']))
                throw new WC_PagSeguro_Exception('O campos `order_id` não pode ser nulo.', 400);
            if (is_null($data['sender_hash']))
                throw new WC_PagSeguro_Exception('O campo `sender_hash` não pode ser nulo.', 400);
            if (is_null($data['sender_hash']))
                throw new WC_PagSeguro_Exception('O campo `sender_document` não pode ser nulo.', 400);
            return $data;
        }

        /**
         * Mount the text message of the installment
         * @param  object $installment
         * @return string
         */
        private function get_installment_text($installment)
        {
            return sprintf(
                "%s x de R$ %.2f %s juros",
                $installment->getQuantity(),
                $installment->getAmount(),
                $this->get_interest_free_text($installment->getInterestFree()));
        }

        /**
         * Get the string relative to if it is an interest free or not
         * @param string $interest_free
         * @return string
         */
        private function get_interest_free_text($interest_free)
        {
            return ($interest_free == 'true') ? 'sem' : 'com';
        }

        /**
         * Return the base array needle to payload
         * @return array
         */
        private function to_array()
        {
            return [
                'url' => sprintf('%s/%s', get_site_url(), 'index.php/pagseguro/order-confirmation'),
                'order_id' => $this->data['order_id']
            ];
        }

        /**
         * Add paymnet link to array
         * @param $response
         * @param $array
         * @return mixed
         */
        private function push_payment_link_to_array($response, $array)
        {
            $array['payment_link'] = $response->getPaymentLink();
            return $array;
        }
    }
endif;
