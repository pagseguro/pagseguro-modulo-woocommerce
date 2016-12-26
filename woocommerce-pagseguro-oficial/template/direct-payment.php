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

$user_data = get_user_meta(get_current_user_id(), '_pagseguro_data');
if ($user_data) {
    $user_data = end($user_data);
//    delete_user_meta(get_current_user_id(), '_pagseguro_data');
} else {
//    echo "<script>window.location = history.back(-1);</script>";
}

?>
<section class="ps-wrap">
    <div id="loader">
        <img src="<?= plugin_dir_url(__DIR__).'assets/images/load_blockui.gif'?>">
    </div>
    <section class="ps-tabs clearfix">
        <h2 class="title-payment">Finalizando sua compra com PagSeguro</h2>
        <section id="direct-payment" class="row" data-url="<?php echo get_home_url().'/index.php/wp-content/plugins/woocommerce-pagseguro-oficial/woocommerce-pagseguro-oficial.php?ajax=true';?>">
            <h2 class="title-payment">Formas de pagamento</h2>
            <div class="alert alert-danger hide" id="ps-alert-error" role="alert" data-redirect="<?php echo get_permalink( get_option( 'woocommerce_shop_page_id' ) ); ?>">
                <strong>Ops!</strong>
                Aconteceu um erro, por favor contate o administrador do sistema e você será re-direcionado para a página
                inicial.
            </div>
            <h4 class="method-payment">Escolha o método</h4>
            <nav class="tabs-pagseguro clearfix" id="tabs-payment">
                <ul class="items clearfix" role="tablist">
                    <li class="item active" role="presentation">
                        <a class="action js-tab-action" role="tab" data-toggle="tab" href="#credit-card">
                            <i class="fa fa-credit-card fa-4x"></i>
                            <span class="name">Cartão de Crédito</span>
                        </a>
                    </li><!-- /.item -->
                    <li class="item" role="presentation">
                        <a class="action js-tab-action" role="tab" data-toggle="tab" href="#debit-online">
                            <i class="fa fa-money fa-4x"></i>
                            <span class="name">Débito Online</span>
                        </a>
                    </li><!-- /.item -->
                    <li class="item" role="presentation">
                        <a class="action js-tab-action" role="tab" data-toggle="tab" href="#bilet">
                            <i class="fa fa-barcode fa-4x"></i>
                            <span class="name">Boleto</span>
                        </a>
                    </li><!-- /.item -->
                </ul><!-- /.items -->
            </nav><!-- /.tabs-payment -->
            <div class="tab-content col-xs-12">
                <div role="tabpanel" class="tab-pane active" id="credit-card">
                    <h3 class="title-tab">Cartão de Crédito</h3>
                    <form class="form-horizontal form-validate clearfix" name="form-credit">
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control cpf-cnpj-mask" id="document-credit-card" name="document" type="text">
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                                <span class="form-error custom-validate document-personal hide">O número do documento não é válido.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_num">Número do cartão</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control credit-card-mask" id="card_num" name="card_num" type="text">
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                                <span class="form-error custom-validate numbercard hide">O número do cartão não é válido.</span>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_holder_name">Nome impresso no cartão</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control" id="card_holder_name" name="card_holder_name" type="text">
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_holder_birthdate">Data de nascimento</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control date-mask" id="card_holder_birthdate" name="card_holder_birthdate" type="text">
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                                <span class="form-error custom-validate birthdate hide">A data de aniversário não é válida.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_validate">Validade</label>
                            <div class="col-xs-12 col-sm-10">
                                <div class="row form-inline-childs">
                                    <div class="col-xs-12 col-sm-6">
                                        <select class="form-control" id="card_expiration_month" name="card_validate">
                                            <option value="" disabled selected>Mês</option>
                                            <option value="01">01</option>
                                            <option value="02">02</option>
                                            <option value="03">03</option>
                                            <option value="04">04</option>
                                            <option value="05">05</option>
                                            <option value="06">06</option>
                                            <option value="07">07</option>
                                            <option value="08">08</option>
                                            <option value="09">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                        <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                                    </div>
                                    <div class="col-xs-12 col-sm-6">
                                        <select id="card_expiration_year" name="card_validate" class="form-control">
                                            <option value="" disabled selected>Ano</option>
                                            <?php
                                            $year = idate("Y");
                                            $maxYear = $year + 20;
                                            for ($i = $year; $i < $maxYear; $i++): ?>
                                                <option value="<?=$i;?>"><?=$i;?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">Código de segurança</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control code-card-mask" id="card_cod" name="card_cod" type="text">
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group display-none">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Parcelas</label>
                            <div class="col-xs-12 col-sm-10">
                                <select id="card_installments" name="card_installments" class="form-control">
                                    <option value="" disabled selected>Escolha o N° de parcelas</option>
                                </select>
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                            </div>
                        </div>
                        <div class="form-group credit-total display-none">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Total</label>
                            <div class="col-xs-12 col-sm-10">
                                <span id="card_total">R$ 00,00</span>
                            </div>
                        </div>
                        <button class="btn-pagseguro btn-form --align-right" data-target-payment="credit" type="button">Concluir</button>
                    </form>
                </div><!-- /.item-tab#credit-card -->
                <div role="tabpanel" class="tab-pane" id="debit-online">
                    <h3 class="title-tab">Débito On-line</h3>
                    <form class="form-horizontal form-validate clearfix" name="form-debit">
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control cpf-cnpj-mask" id="document-debit" name="document" type="text">
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                                <span class="form-error custom-validate document-personal hide">O número do documento não é válido.</span>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-6 control-label">Escolha seu banco abaixo onde deverá fazer o pagamento online.</label>
                            <div id="bankList" class="col-xs-12 col-sm-5 col-sm-offset-1">
                                <label class="radio">
                                    <input type="radio" name="bank" id="optionsRadios1" value="1">
                                    Itaú
                                </label>
                                <!-- <label class="radio">
                                    <input type="radio" name="bank" id="optionsRadios2" value="2">
                                    Bradesco
                                </label> -->
                                <label class="radio">
                                    <input type="radio" name="bank" id="optionsRadios2" value="3">
                                    Banrisul
                                </label>
                                <label class="radio">
                                    <input type="radio" name="bank" id="optionsRadios2" value="4">
                                    Banco do Brasil
                                </label>
                                <label class="radio">
                                    <input type="radio" name="bank" id="optionsRadios2" value="5">
                                    HSBC
                                </label>
                            </div>
                            <span class="form-error hide">A opção de banco não pode estar vazia, escolha uma das opções acima.</span>
                        </div><!-- /.form-group -->
                        <button class="btn-pagseguro btn-form --align-right" type="button" data-target-payment="debit">Concluir</button>
                    </form>
                </div><!-- /.item-tab#debit-online -->
                <div role="tabpanel" class="tab-pane" id="bilet">
                    <h3 class="title-tab">Boleto</h3>
                    <form class="form-horizontal form-validate clearfix" name="form-bilit">
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control cpf-cnpj-mask" id="document-boleto" name="document" type="text">
                                <span class="form-error hide">Este campo é obrigatório, não pode estar vazio.</span>
                                <span class="form-error custom-validate document-personal hide">O número do documento não é válido.</span>
                            </div>
                        </div>
                        <button class="btn-pagseguro btn-form --align-right" type="button" data-target-payment="billet">Concluir</button>
                    </form>
                    <ul class="list-warning">
                        <li>Imprima o boleto e pague no banco</li>
                        <li>Ou pague pela internet utilizando o código de barras do boleto</li>
                        <li>o prazo de validade do boleto é de 1 dia útil</li>
                    </ul>
                </div><!-- /.item-tab#bilet -->
            </div><!-- /.tabs-content-->
        </section><!-- /.wrapper -->
    </section>
</section>

<input type="hidden" id="order" data-target="<?=$user_data['order_id'] ?>"/>
<input type="hidden" id="session-code" data-target="<?=$user_data['session_code'] ?>"/>

<script type="text/javascript" src="<?= plugin_dir_url(__DIR__).'assets/js/vendor/jquery.mask.min.js'; ?>"></script>
<script type="text/javascript" src="<?= plugin_dir_url(__DIR__).'assets/js/direct-payment.js'; ?>"></script>
<script type="text/javascript" src="<?= $user_data['js']; ?>"></script>
<script type="text/javascript" src="<?= plugin_dir_url(__DIR__).'assets/js/user.payment.js'; ?>"></script>
<script type="text/javascript">
    jQuery(document).ready(function($){

        PagSeguroDirectPayment.setSessionId($('#session-code').attr('data-target'));

        $(document).on("keypress", "form", function(e) {
            return e.keyCode != 13;
        });

        $('.btn-form').click(function () {
            var error = false;
            var paymentType = $(this).data('target-payment');
            var $form = $(this).parent('.form-validate');
            var $fields = $form.find('.form-control');
            $('.form-error').addClass('hide');

            $fields.each(function () {
                if(!$(this).val()){
                    $(this).siblings('.form-error:not(".custom-validate")').removeClass('hide');
                    error = true;
                }
            });

            /*
             Validade Personal or Company Document
             */
            var $inputpersonal = $form.find('.form-control[name="document"]');
            var documentPersonal = $inputpersonal.val();
            var documentVal = documentPersonal.toString();

            if(documentPersonal !== '') {
                if(documentPersonal.length === 14 && validateCpf(documentVal) === false) {
                    $inputpersonal.siblings('.form-error').addClass('hide');
                    $inputpersonal.siblings('.form-error.document-personal').removeClass('hide');
                    error = true;
                    return;
                }

                if(documentPersonal.length == 19 && validateCnpj(documentVal) === false) {
                    $inputpersonal.siblings('.form-error').addClass('hide');
                    $inputpersonal.siblings('.form-error.document-personal').removeClass('hide');
                    error = true;
                    return;
                }
            }

            /*
             Validate number credit card input
             */

            if(paymentType === 'credit') {
                var $inputcard = $form.find('.form-control[name="card_num"]');
                var carnumber = $inputcard.val();
                if(carnumber!== '' && carnumber.length !== 19){
                    $inputcard.siblings('.form-error').addClass('hide');
                    $inputcard.siblings('.form-error.numbercard').removeClass('hide');
                    error = true;
                }

                /*
                 Validate birthday input
                 */
                var $inputdate = $form.find('.form-control[name="card_holder_birthdate"]');
                var birthday = $inputdate.val();
                if(birthday !== '' && birthday.length !== 10){
                    $inputdate.siblings('.form-error').addClass('hide');
                    $inputdate.siblings('.form-error.birthdate').removeClass('hide');
                    error = true;
                }
            }

            if(paymentType === 'debit') {
                if(!$('input[name="bank"]').is(':checked')) {
                    $('input[name="bank"]').closest('.form-group').children('.form-error').removeClass('hide');
                    error = true;
                }
            }

            /*
             Call payment type choice user
             */
            if(error === false) {
                if(paymentType === 'credit') {
                    creditPayment();
                } else if (paymentType === 'debit') {
                    debitPayment();
                } else {
                    billetPayment();
                }
            };
        });

        function validateCpf(str) {
            str = str.replace('.','');
            str = str.replace('.','');
            str = str.replace('.','');
            str = str.replace('-','');
            var strCPF = str;
            var Soma;
            var Resto;
            Soma = 0;
            if (strCPF == "00000000000") return false;

            for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
            Resto = Soma % 11;

            if ((Resto == 0) || (Resto == 1)) {
                Resto = 0;
            } else {
                Resto = 11 - Resto;
            };

            if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;

            Soma = 0;
            for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
            Resto = Soma % 11;

            if ((Resto == 0) || (Resto == 1)) {
                Resto = 0;
            } else {
                Resto = 11 - Resto;
            };

            if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
            return true;
        };

        function validateCnpj(str) {
            str = str.replace('.','');
            str = str.replace('.','');
            str = str.replace('.','');
            str = str.replace('-','');
            str = str.replace('/','');
            var cnpj = str;
            var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
            digitos_iguais = 1;
            if (cnpj.length < 14 && cnpj.length < 15)
                return false;
            for (i = 0; i < cnpj.length - 1; i++)
                if (cnpj.charAt(i) != cnpj.charAt(i + 1))
                {
                    digitos_iguais = 0;
                    break;
                }
            if (!digitos_iguais)
            {
                tamanho = cnpj.length - 2
                numeros = cnpj.substring(0,tamanho);
                digitos = cnpj.substring(tamanho);
                soma = 0;
                pos = tamanho - 7;
                for (i = tamanho; i >= 1; i--)
                {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }
                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(0))
                    return false;
                tamanho = tamanho + 1;
                numeros = cnpj.substring(0,tamanho);
                soma = 0;
                pos = tamanho - 7;
                for (i = tamanho; i >= 1; i--)
                {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }
                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(1))
                    return false;
                return true;
            }
            else
                return false;
        };

        function errorPaymentProcess() {
            var redirect = $('#ps-alert-error').data('redirect');
            $('#ps-alert-error').removeClass('hide');
            setTimeout(function () {
                window.location.href = redirect;
            }, 5000);
        };

        //Event buttons methods buy types
        function billetPayment(){
            // load_elements('boleto') doesn't load bank and credit-card options.
            var elements = load_elements('boleto');
            set_session_code(elements.session_code);
            request_boleto(elements);
        };

        function debitPayment(){
            // load_elements('debit', true) doesn't credit-card options only bank options.
            var elements = load_elements('debit', true);
            set_session_code(elements.session_code);
            request_debit(elements);
        };

        function creditPayment(){
            // load_elements('credit-card', true, true) load all options.
            var elements = load_elements('credit-card', true, true);
            set_session_code(elements.session_code);
            request_cc_card(elements);
        };

        function set_session_code(session_id) {
            PagSeguroDirectPayment.setSessionId(
                session_id
            );
        }

        function request_boleto(elements) {
            setTimeout(function () {
                var hash = PagSeguroDirectPayment.getSenderHash();
                WS.Ajax.Direct.Boleto.Payment(elements.url, elements.order_id, hash, elements.document, errorPaymentProcess);
            }, 1000)

        }

        function request_debit(elements) {
            setTimeout(function () {
                var hash = PagSeguroDirectPayment.getSenderHash();
                WS.Ajax.Direct.OnlineDebit.Payment(elements.url, elements.order_id, hash, elements.document, elements.bank, errorPaymentProcess);
            }, 1000)

        }

        function request_cc_card(elements) {
            PagSeguroDirectPayment.createCardToken({
                cardNumber: unmaskField($('#card_num')),
                brand: $('#card-brand').attr('data-target'),
                internationalMode: $('#card-international').attr('data-target'),
                cvv: $('#card_cod').val(),
                expirationMonth: $('#card_expiration_month').val(),
                expirationYear: $('#card_expiration_year').val(),
                success: function (response) {
                    execute_cc_card(elements, response.card.token);
                }
            });
        }

        function execute_cc_card(elements, token) {
            setTimeout(function () {
                var hash = PagSeguroDirectPayment.getSenderHash();
                WS.Ajax.Direct.CreditCard.Payment(
                    elements.url,
                    elements.order_id,
                    hash,
                    elements.document,
                    token,
                    elements.cardInternational,
                    elements.installmentQuantity,
                    elements.installmentAmount,
                    elements.holderName,
                    elements.holderBirthdate,
                    errorPaymentProcess
                );
            }, 1000)

        }

        function load_elements(service, bank, credit_card) {
            var bank = bank || false;
            var credit_card = credit_card || false;
            var data = [];

            //default values to send
            data['url'] = $('#direct-payment').attr('data-url');
            data['order_id'] = $('#order').attr('data-target');
            data['session_code'] = $('#session-code').attr('data-target');
            data['document'] = unmaskField($('#document-'+service));
            //values only debit method to use
            if(bank) {
                var bankId = $("#bankList input[type='radio']:checked");
                if (bankId.length > 0) {
                    data['bank'] = bankId.val();
                }
            }
            //values only credit card method to use
            if(credit_card) {
                data['cardInternational'] = $('#card-international').attr('data-target');
                data['installmentQuantity'] = $("#card_installments option:selected" ).attr('data-quantity');
                data['installmentAmount'] = $("#card_installments option:selected" ).attr('data-amount');
                data['holderName'] = $('#card_holder_name').val();
                data['holderBirthdate'] = $('#card_holder_birthdate').val();
            }
            return data;
        };

        //get and showing brand credit card
        function get_card_brand(cardbin) {
            PagSeguroDirectPayment.getBrand({
                cardBin: cardbin,
                internationalMode: true,
                success: function (response) {
                    WS.Ajax.Direct.CreditCard.Installments(
                        $('#adminurl').attr('data-target'),
                        $('#order').attr('data-target'),
                        response.brand.name,
                        response.brand.international
                    );
                }
            });
        };

        (function() {
            var kbinValue,
                klength = 0,
                klastLength = 0,
                kunMasked;
            $('#card_num').on('keyup', function () {
                klastLength = klength;
                klength = $(this).val().length;
                //6 number + space of mask
                if (klength == 7 && klastLength <= 7) {
                    kunMasked = unmaskField($(this).val(), false);
                    kbinValue = kunMasked.substring(0,6);
                    get_card_brand(kbinValue);
                }
            });
        }());

        // Masks functions
        (function masksInputs() {
            $('.cpf-mask').mask('000.000.000-00');
            $('.cnpj-mask').mask('00.000.000/0000-00');
            $('.credit-card-mask').mask('0000 0000 0000 0000');
            $('.code-card-mask').mask('000');
            $('.date-mask').mask('00/00/0000', { placeholder: "__/__/____" });
            $('.cpf-cnpj-mask').on('keyup', function() {
                try {
                    $(this).unmask();
                } catch(e) {
                    alert('Ops, algo deu errado!');
                };
                var isLength = $(this).val().length;
                //9 is number optional, is fake the transtion two types mask
                isLength <= 11 ? $(this).mask('000.000.000-009') : $(this).mask('00.000.000/0000-00');
            });
        }());

        function unmaskField($el, val) {
            if(val === undefined) {
                val = true
            }
            try {
                if (val === true) {
                    var $el = $el.val();
                }
                return $el.replace(/[/ -. ]+/g, '').trim();
            } catch(e) {
                alert('Ops, algo deu errado! Recarregue a página');
            };
        };

        (function() {
            $('#card_num').on('paste', function (e) {
                e.preventDefault();
                return false;
            });
        }());

        (function calcTotal() {
            //Update the total value according with installments
            $('#card_installments').on('change', function() {
                var currency = parseFloat($(this).val()).toFixed(2);
                $('#card_total').text('R$ ' + currency);
            });
        }());
    });
</script>
