var WS = {
    'Ajax' : {
        'Direct' : {
            'Boleto': {
                'Payment' : function (base_url, order_id, hash, document, error) {
                    jQuery.ajax({
                        url: ajax_object.ajax_url+"?wc-pagseguro-ajax=true",
                        data: {
                            order_id: order_id,
                            sender_hash : hash,
                            sender_document : document,
                            checkout_type : 'boleto'
                        },
                        type: 'POST'
                    }).success(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            var newForm = jQuery('<form>', {
                                'action': response.payload.data.url,
                                'target': '_top',
                                'method': 'POST'
                            }).append(jQuery('<input>', {
                                'name': 'payment_link',
                                'value': response.payload.data.payment_link,
                                'type': 'hidden'
                            })).append(jQuery('<input>', {
                                'name': 'checkout_type',
                                'value': 'boleto',
                                'type': 'hidden'
                            })).append(jQuery('<input>', {
                                'name': 'order_id',
                                'value': response.payload.data.order_id,
                                'type': 'hidden'
                            }));
                            jQuery(newForm).submit();
                        } else {
                            error();
                        }
                    }).error(function(){
                        //@todo show error message and redirect to shop
                    });
                }
            },
            'OnlineDebit': {
                'Payment' : function (base_url, order_id, hash, document, bank, error) {
                    jQuery.ajax({
                        url: ajax_object.ajax_url+"?wc-pagseguro-ajax=true",
                        data: {
                            order_id: order_id,
                            sender_hash : hash,
                            sender_document : document,
                            bank_name:bank,
                            checkout_type : 'debit'
                        },
                        type: 'POST'
                    }).success(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            var newForm = jQuery('<form>', {
                                'action': response.payload.data.url,
                                'target': '_top',
                                'method': 'POST'
                            }).append(jQuery('<input>', {
                                'name': 'payment_link',
                                'value': response.payload.data.payment_link,
                                'type': 'hidden'
                            })).append(jQuery('<input>', {
                                'name': 'checkout_type',
                                'value': 'debit',
                                'type': 'hidden'
                            })).append(jQuery('<input>', {
                                'name': 'order_id',
                                'value': response.payload.data.order_id,
                                'type': 'hidden'
                            }));
                            jQuery(newForm).submit();
                        } else {
                            error();
                        }
                    }).error(function(){
                        //@todo show error message and redirect to shop
                    });
                }
            },
            'CreditCard': {
                'Installments' : function (url, id, brand, isInternational) {
                    jQuery.ajax({
                        url: ajax_object.ajax_url+"?wc-pagseguro-ajax=true",
                        data: {
                            order_id: id,
                            credit_card_brand : brand,
                            credit_card_international : isInternational,
                            checkout_type : 'installments'
                        },
                        type: 'POST'
                    }).success(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {

                            //remove if already exists installment options
                            jQuery('#card_installments option').each(function () {
                                if (!jQuery(this).val() === false) {
                                    jQuery(this).remove();
                                }
                            });
                            //add installments options
                            jQuery.each(response.payload.data, function (i, item) {
                                jQuery('#card_installments').append(jQuery('<option>', {
                                    value: item.totalAmount,
                                    text: item.text,
                                    'data-amount': item.amount,
                                    'data-quantity': item.quantity
                                }));
                            });
                            //add card international status
                            jQuery('#card-international').attr('data-target', response.payload.data.cardInternational);
                            //add card brand
                            jQuery('#card-brand').attr('data-target', response.payload.data.cardBrand);
                            //show installments option and total amount of it
                            jQuery('.display-none').show();

                            //@todo close loading modal
                        } else {
                            //@todo show error message and redirect to shop
                        }
                    }).error(function () {
                        //@todo show error message and redirect to shop
                    });
                },
                'Payment' : function (url, id, hash, document, token, international, quantity, amount, holderName, holderBirthdate, error) {
                    jQuery.ajax({
                        url: ajax_object.ajax_url+"?wc-pagseguro-ajax=true",
                        data: {
                            order_id: id,
                            sender_hash : hash,
                            sender_document : document,
                            card_token: token,
                            card_international: international,
                            installment_quantity: quantity,
                            installment_amount: amount,
                            holder_name: holderName,
                            holder_birthdate: holderBirthdate,
                            checkout_type : 'credit_card'
                        },
                        type: 'POST'
                    }).success(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            var newForm = jQuery('<form>', {
                                'action': response.payload.data.url,
                                'target': '_top',
                                'method': 'POST'
                            }).append(jQuery('<input>', {
                                'name': 'checkout_type',
                                'value': 'credit-card',
                                'type': 'hidden'
                            })).append(jQuery('<input>', {
                                'name': 'order_id',
                                'value': response.payload.data.order_id,
                                'type': 'hidden'
                            }))
                            jQuery(newForm).submit();
                        } else {
                            error();
                            //@todo show error message and redirect to shop
                        }
                    }).error(function(){
                        //@todo show error message and redirect to shop
                    });
                }
            }
        }
    }
}
