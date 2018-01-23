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

if ( ! class_exists('WC_PagSeguro_Methods')):

    abstract class WC_PagSeguro_Methods
    {

        protected $transaction_list;

        /**
         * Array with PagSeguro Status key in relation of WC PagSeguro Status as value
         *
         * @var array
         */
        protected $array_order_status = array(
            0 => "ps-iniciado",
            1 => "ps-pagamento",
            2 => "ps-em-analise",
            3 => "ps-paga",
            4 => "ps-disponivel",
            5 => "ps-em-disputa",
            6 => "ps-devolvida",
            7 => "ps-cancelada",
            8 => "ps-chargeback-debitado",
            9 => "ps-em-contestacao"
        );

        /**
         * Sanitize configuration
         *
         * @param $data
         * @return mixed
         */
        protected function sanitize_config($data)
        {
            $config = $this->decrypt('!@#$GfdsdgRGF4s35%#$%%', $data);
            $config = filter_var($config, FILTER_SANITIZE_URL);
            return json_decode($config);
        }

        protected function get_pagseguro_transactions($page = null)
        {
            if (is_null($page)) $page = 1;

            try {
                //check if is the first step, if is just add the response object to local var
                if (is_null($this->transaction_list)) {
                    $this->transaction_list = $this->request_pagseguro_transactions($page);
                } else {
                    $response = $this->request_pagseguro_transactions($page);
                    //update some important data
                    $this->transaction_list->setDate($response->getDate());
                    $this->transaction_list->setCurrentPage($response->getCurrentPage());
                    $this->transaction_list->setResultsInThisPage(
                        $response->getResultsInThisPage() + $this->transaction_list->getResultsInThisPage()
                    );
                    //add new transactions
                    $this->transaction_list->addTransactions($response->getTransactions());
                }
                //check if was more pages
                if ($this->transaction_list->getTotalPages() > $page) {
                    $this->get_pagseguro_transactions(++$page);
                }
            } catch (\Exception $exception) {
                WC_PagSeguro_Payload::when_error($exception->getMessage());
            }
            return $this->transaction_list;
        }

        /**
         * @param $page
         * @return string
         */
        protected function request_pagseguro_transactions($page = 1)
        {

            $initial_date = new \DateTime($this->days . " days ago", new DateTimeZone('America/Sao_Paulo'));
            $final_date   = new \DateTime('now',  new DateTimeZone('America/Sao_Paulo'));

            $options = [
                'initial_date' => $initial_date->format('Y-m-d\TH:i:s'),
                'final_date' => $final_date->format('Y-m-d\TH:i:s'), //Optional
                'page' => $page, //Optional
                'max_per_page' => 1000, //Optional
            ];

            \PagSeguro\Configuration\Configure::setEnvironment($this->options['environment']);
            return \PagSeguro\Services\Transactions\Search\Date::search(
                new \PagSeguro\Domains\AccountCredentials($this->options['email'], $this->options['token']),
                $options
            );
        }

        /**
         * @param $date
         * @return string
         */
        protected function date_format($date)
        {
            $date = new DateTime($date);
            return $date->format('d/m/Y H:i:s');
        }

        /**
         * @param $string
         * @return string
         */
        protected function decrypt_reference_prefix($string)
        {
            return substr($string, 0, 17);
        }

        /**
         * @param $string
         * @return string
         */
        protected function decrypt_reference_order($string)
        {
            return substr($string, 17);
        }

        /**
         * Encrypt data
         *
         * @param $password
         * @param $data
         * @return string
         */
        protected function encrypt($password, $data)
        {
            $salt = substr(md5(mt_rand(), true), 8);
            $key = md5($password . $salt, true);
            $iv  = md5($key . $password . $salt, true);
            $ct = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
            return base64_encode('Salted__' . $salt . $ct);
        }
        /**
         * Decrypt data
         *
         * @param $password
         * @param $data
         * @return string
         */
        protected function decrypt($password, $data)
        {
            $data = base64_decode($data);
            $salt = substr($data, 8, 8);
            $ct   = substr($data, 16);
            $key = md5($password . $salt, true);
            $iv  = md5($key . $password . $salt, true);
            $pt = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ct, MCRYPT_MODE_CBC, $iv);
            return $pt;
        }

        abstract public function init($options);

        abstract protected function search();

        abstract protected function execute();

    }

endif;