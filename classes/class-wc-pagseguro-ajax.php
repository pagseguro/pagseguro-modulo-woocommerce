<?php

require_once('../../../../wp-load.php');

class WC_PagSeguro_Ajax
{
    /**
     * Hook in ajax handlers.
     */
    public static function init()
    {
        try {
            (new WC_PagSeguro_Ajax)->is_ajax();
            self::get_payment_service(
                self::validate_checkout_type($_REQUEST['checkout_type'])
            );
        } catch (WC_PagSeguro_Exception $pagseguro_exception) {
            WC_PagSeguro_Payload::when_error($pagseguro_exception->getMessage());
        }
    }

    /**
     * Check if is ajax request
     *
     * @return bool
     * @throws WC_PagSeguro_Exception
     */
    private function is_ajax()
    {
        if ((bool)$_GET['wc-pagseguro-ajax'] === true) {
            return true;
        }

        throw new WC_PagSeguro_Exception('Não foi possivel encontrar o parâmetro ajax ou o mesmo não é válido.', 400);
    }

    /**
     * Get direct payment service by name
     *
     * @param $service_name
     *
     * @throws WC_PagSeguro_Exception
     */
    private static function get_payment_service($service_name)
    {
        $payment = new WC_PagSeguro_Direct_Payment($_REQUEST);
        switch ($service_name) {
            case "boleto":
                return $payment->boleto();
            case "debit":
                return $payment->debit();
            case "credit_card":
                return $payment->credit_card();
            case "installments":
                return $payment->installments();
            default:
                throw new WC_PagSeguro_Exception('Não foi possivel encontrar o tipo de checkout informado.', 400);
        }
    }

    /**
     * Check if the checkout type is valid.
     *
     * @param $type
     *
     * @return mixed
     * @throws WC_PagSeguro_Exception
     */
    private static function validate_checkout_type($type)
    {
        if (isset($type)) {
            return filter_var($_REQUEST['checkout_type'], FILTER_SANITIZE_STRING);
        }
        throw new WC_PagSeguro_Exception('O tipo de checkout informado não é valido', 400);
    }
}

WC_PagSeguro_Ajax::init();