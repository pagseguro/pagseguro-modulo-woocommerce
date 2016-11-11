'use strict';
;(function(win, doc, $, undefined) {
    var message;
    doc.addEventListener('DOMContentLoaded', function() {
        $('#wp-pagseguro-conciliation-table').DataTable({
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
                "targets": [0, 5],
                "orderable": false
            }],

            "fnDrawCallback": function(oSettings) {
                //disable pagination if one page
                if ($('#wp-pagseguro-conciliation-table tr').length < 11) {
                    $('.dataTables_paginate').hide();
                }
            }
        });
    }, false);

    $("#wp-pagseguro-conciliation-button-search").on('click', search);
    function search() {
        Modal.show('loading');

        var srcUrl = doc.getElementById('pg-app').getAttribute('data-url-search');
        $.ajax({
            type: "POST",
            url: srcUrl,
            data: {
                action : 'conciliation',
                method : 'search',
                date   : $("#wp-pagseguro-conciliation-search").val()
            },
            success:function(results){
                var result = JSON.parse(results);
                var t =  $('#wp-pagseguro-conciliation-table').DataTable();
                t.clear();

                if (result.success) {
                    if (result.payload.data.length > 0) {
                        result.payload.data.forEach(function (data) {
                            t.row.add([
                                '<input data="true" data-target="'+data.details+'" type="checkbox">',
                                data.date,
                                data.wordpress_id,
                                data.pagseguro_id,
                                data.wordpress_status,
                                data.pagseguro_status,
                                '<a href="'+data.action+'"> Detalhes <a/>'
                            ]).draw(true);
                        });
                        Modal.hide(true);
                    } else {
                        Modal.hide(false);
                        Modal.show('success', 'Sem resultados para o período solicitado.');
                    }
                } else {
                    Modal.hide(false);
                    Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
                }
            },
            error: function () {
                Modal.hide(false);
                Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
            }
        });
    };

    function execute(data) {
        var srcUrl = doc.getElementById('pg-app').getAttribute('data-url-execute');
        $.ajax({
            type:"POST",
            url: srcUrl,
            data: {
                action : 'conciliation',
                method : 'execute',
                data   : data
            },
            success: function(results){
                Modal.hide(false);

                var result = JSON.parse(results);
                if (result.success) {
                    Modal.show('success', 'Transações conciliadas com sucesso!');
                } else {
                    Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
                }
            },
            error: function () {
                Modal.hide(false);
                Modal.show('error', 'Não foi possível executar esta ação. Tente novamente mais tarde.');
            }
        });
    }

    $(".check-all").click(function() {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });

    $("#wp-pagseguro-conciliation-execute").click(function() {
        Modal.show('loading');

        var data = [];
        var t =  $('#wp-pagseguro-conciliation-table').DataTable();
        $.each($("input[type='checkbox']:checked"), function(){
            var value = $(this).attr('data-target');
            if (value != '' && value != undefined) {
                var tr = $($(this)).parent().parent();
                t.row( tr ).remove().draw();
                data.push(value);
            }
        });
        execute(data);
    });
}(window, document, jQuery, undefined));
