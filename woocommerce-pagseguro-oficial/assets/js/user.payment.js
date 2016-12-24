jQuery(function () {
    var $ = jQuery;
    $(document)
        .ajaxStart(function () {
            $.blockUI({
                message: $('#loader'),
                overlayCSS: {
                    background: '#fff', opacity: 0.6
                }
            })
        })
        .ajaxStop($.unblockUI);

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
    }

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
        var hash = PagSeguroDirectPayment.getSenderHash();
        WS.Ajax.Direct.Boleto.Payment(elements.url, elements.order_id, hash, elements.document, errorPaymentProcess);
    }

    function request_debit(elements) {
        var hash = PagSeguroDirectPayment.getSenderHash();
        WS.Ajax.Direct.OnlineDebit.Payment(elements.url, elements.order_id, hash, elements.document, elements.bank, errorPaymentProcess);
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
        var val = val || true;
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