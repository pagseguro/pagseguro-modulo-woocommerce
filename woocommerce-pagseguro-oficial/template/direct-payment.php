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
    delete_user_meta(get_current_user_id(), '_pagseguro_data');
} else {
    echo "<script>window.location = history.back(-1);</script>";
}

?>
<section class="ps-wrap">
    <section class="ps-tabs clearfix">

        <h2 class="title-payment">Finalizando sua compra com PagSeguro</h2>

        <section id="direct-payment" class="row" data-url="<?php echo get_home_url().'/index.php/wp-content/plugins/woocommerce-pagseguro-oficial/woocommerce-pagseguro-oficial.php?ajax=true';?>">
            <h2 class="title-payment">Formas de pagamento</h2>
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
            <div class="tab-content col-xs-12 col-md-8 col-md-offset-2">
                <div role="tabpanel" class="tab-pane active" id="credit-card">
                    <h3 class="title-tab">Cartão de Crédito</h3>
                    <form class="form-horizontal clearfix" name="form-credit">
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control cpf-cnpj-mask" id="document-credit-card" name="document" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_num">Número do cartão</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control credit-card-mask" id="card_num" name="card_num" type="text" required>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_holder_name">Nome impresso no cartão</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control" id="card_holder_name" name="card_holder_name" type="text" required>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_holder_birthdate">Data de nascimento</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control date-mask" id="card_holder_birthdate" name="card_holder_birthdate" type="text" required="">
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
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">Código de segurança</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control code-card-mask" id="card_cod" name="card_cod" type="text">
                            </div>
                        </div><!-- /.form-group -->
                        <div class="form-group display-none">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Parcelas</label>
                            <div class="col-xs-12 col-sm-10">
                                <select id="card_installments" name="card_installments" class="form-control">
                                    <option value="" disabled selected>Escolha o N° de parcelas</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group credit-total display-none">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Total</label>
                            <div class="col-xs-12 col-sm-10">
                                <span id="card_total">R$ 00,00</span>
                            </div>
                        </div>
                        <button class="btn-pagseguro --align-right" id="payment-credit-card">Concluir</button>
                    </form>
                </div><!-- /.item-tab#credit-card -->
                <div role="tabpanel" class="tab-pane" id="debit-online">
                    <h3 class="title-tab">Débito On-line</h3>
                    <form class="form-horizontal clearfix" name="form-debit">
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control cpf-cnpj-mask" id="document-debit" name="document" type="text">
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
                        </div><!-- /.form-group -->
                        <button class="btn-pagseguro --align-right" id="payment-debit">Concluir</button>
                    </form>
                </div><!-- /.item-tab#debit-online -->
                <div role="tabpanel" class="tab-pane" id="bilet">
                    <h3 class="title-tab">Boleto</h3>
                    <form class="form-horizontal clearfix" name="form-bilit">
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                            <div class="col-xs-12 col-sm-10">
                                <input class="form-control cpf-cnpj-mask" id="document-boleto" name="document" type="text">
                            </div>
                        </div>
                        <button class="btn-pagseguro --align-right" id="payment-boleto">Concluir</button>
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
<script type="text/javascript">
    jQuery(document).ready(function($){

        PagSeguroDirectPayment.setSessionId($('#session-code').attr('data-target'));

        //Event buttons methods buy types
        $('#payment-boleto').on('click', function(e){
            e.preventDefault();
            //@todo start loading modal
            // load_elements('boleto') doesn't load bank and credit-card options.
            var elements = load_elements('boleto');
            set_session_code(elements.session_code);
            request_boleto(elements);
        });

        $('#payment-debit').on('click', function(e){
            e.preventDefault();
            //@todo start loading modal
            // load_elements('debit', true) doesn't credit-card options only bank options.
            var elements = load_elements('debit', true);
            set_session_code(elements.session_code);
            request_debit(elements);
        });

        $('#payment-credit-card').on('click', function(e){
            e.preventDefault();
            //@todo start loading modal
            // load_elements('credit-card', true, true) load all options.
            var elements = load_elements('credit-card', true, true);
            set_session_code(elements.session_code);
            request_cc_card(elements);
        });

        function set_session_code(session_id) {
            PagSeguroDirectPayment.setSessionId(
                session_id
            );
        }

        function request_boleto(elements) {
            var hash = null;
            setTimeout(function () {
                hash = PagSeguroDirectPayment.getSenderHash();
                WS.Ajax.Direct.Boleto.Payment(elements.url, elements.order_id, hash, elements.document);
            }, 1000);
        }

        function request_debit(elements) {
            var hash = null;
            setTimeout(function () {
                hash = PagSeguroDirectPayment.getSenderHash();
                WS.Ajax.Direct.OnlineDebit.Payment(elements.url, elements.order_id, hash, elements.document, elements.bank);
            }, 1000);
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
            var hash = null;
            setTimeout(function () {
                hash = PagSeguroDirectPayment.getSenderHash();
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
                    elements.holderBirthdate
                );
            }, 1000);
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

        ;(function() {
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
        }($));

        // Masks functions
        ;(function masksInputs($, undefined) {
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
        }($));

        function unmaskField($el, val = true) {
            try {
                if (val === true) {
                    var $el = $el.val();
                }
                return $el.replace(/[/ -. ]+/g, '').trim();
            } catch(e) {
                alert('Ops, algo deu errado! Recarregue a página');
            };
        };

        ;(function() {
            $('#card_num').on('paste', function (e) {
                e.preventDefault();
                return false;
            });
        }($));
    });
</script>
