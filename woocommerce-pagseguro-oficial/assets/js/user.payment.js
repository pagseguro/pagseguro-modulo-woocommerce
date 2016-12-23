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

});