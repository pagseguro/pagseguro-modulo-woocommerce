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
            <div class="alert alert-danger hide" id="ps-alert-error" role="alert" data-redirect="<?php echo get_home_url()?>">
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
