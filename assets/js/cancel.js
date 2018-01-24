'use strict';
;(function(win, doc, $, undefined) {
    doc.addEventListener('DOMContentLoaded', function() {
        $('#wp-pagseguro-cancel-table').DataTable({
            bStateSave: true,
            info: false,
            lengthChange: false,
            searching: false,
            pageLength: 10,
            responsive: true,
            oLanguage: {
                sEmptyTable:"Realize uma pesquisa.",
                oPaginate: {
                    sNext: 'Próximo',
                    sLast: 'Último',
                    sFirst: 'Primeiro',
                    sPrevious: 'Anterior'
                }
            },
            "columnDefs": [{
                "targets": [0, 4],
                "orderable": false
            }],
            "fnDrawCallback": function(oSettings) {
                //disable pagination if one page
                if ($('#wp-pagseguro-cancel-table tr').length < 11) {
                    $('.dataTables_paginate').hide();
                }

                $('#wp-pagseguro-cancel-table tbody tr').off().on('click', function() {
                    var _self  = $(this);
                    var t =  $('#wp-pagseguro-cancel-table').DataTable();
                    if (t.page.info().recordsTotal > 0) {
                        Modal.show('confirm', 'Tem certeza que deseja cancelar?', function(check) {

                            if(check == true) {
                                Modal.show('loading');
                                var data = _self.find('.wp-pagseguro-cancel-execute').attr('data-target');
                                execute(data, _self);
                            }
                        });
                    }
                });
            }
        });
    }, false);

    $("#wp-pagseguro-cancel-button-search").on('click', search);
    function search() {
        Modal.show('loading');

        var srcUrl = doc.getElementById('pg-app').getAttribute('data-url-search');
        $.ajax({
                type:"POST",
                url: srcUrl,
                data: {
                    action : 'cancel',
                    method : 'search',
                    date   : $("#wp-pagseguro-cancel-search").val()
                },
                success: function(results){
                    Modal.hide(true);
                    var result = JSON.parse(results);
                    var t =  $('#wp-pagseguro-cancel-table').DataTable();
                    t.clear();
                    if (result.success) {
                        if (result.payload.data.length > 0) {
                            result.payload.data.forEach(function (data) {
                                t.row.add([
                                    data.date,
                                    data.wordpress_id,
                                    data.pagseguro_id,
                                    data.wordpress_status,
                                    '<a href="#" class="wp-pagseguro-cancel-execute" data-target="' + data.details + '"> Cancelar <a/>'
                                ]).draw(true);
                            })
                        } else {
                            Modal.show('success', 'Sem resultados para o período solicitado.');
                        }
                    } else {
                        Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
                    }
                },
                error: function () {
                    Modal.hide(false);
                    Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
                }
        });
    };

    function execute(data, row) {
        var srcUrl = doc.getElementById('pg-app').getAttribute('data-url-execute');
        $.ajax({
            type:"POST",
            url: srcUrl,
            data: {
                action : 'cancel',
                method : 'execute',
                data   : data
            },
            success:function(result){
                Modal.hide(true);
                var result = JSON.parse(result);
                if (result.success) {
                    var t =  $('#wp-pagseguro-cancel-table').DataTable();
                    t.row( row ).remove().draw();
                    Modal.show('success', 'Transações canceladas com sucesso!');

                } else if (! result.success && result.payload.error == 'need to conciliate') {
                    Modal.hide(false);
                    Modal.show('error', 'Não foi possível executar esta ação. Utilize a conciliação de transações primeiro ou tente novamente mais tarde.');
                } else {
                    Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
                }
            },
            error: function () {
                Modal.hide(false);
                Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
            }
        });
    };
}(window, document, jQuery, undefined));
