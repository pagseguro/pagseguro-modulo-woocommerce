/* This is an example for you usage the modal component
*
* @Modal is a Global Object, that has three methods (type(their types, show, and hide));
*
* ABOUT SHOW METHOD - Modal.show('type', message, check);
*
* =======================================================================================================
* @param STRING - Modal.show('loading, succes, error, warning, confirm') - binding to show modal type.
* =======================================================================================================
* @param VAR contains a string type value - var message = 'Example' - Modal.show(type, message) -
* not necessary in some for example loading modal type.
* =======================================================================================================
* @param FUNCTION is require in Modal confirm type = Modal.show('confirm', message, callback) -
* set VAR check at global scope for you to use in your condition.
* =======================================================================================================
*
* ABOUT HIDE METHOD - Modal.hide(true or false);
*
* @param removeOverlay - binding for the method to know whether or not to remove the overlay in the
* event of a lock and then immediately open another modal.
*
*/

'use strict';
;(function(win, doc, $, undefined) {

    $('.ps-button').click(function() {
        Modal.hide(true);
    });

    var _self;
    var Modal = {
        methods: {
            loading: function() {
                _self = $('.ps-modal.ps-loading');
                _self.addClass('show');
            },

            success: function(message) {
                _self = $('.ps-modal.ps-success');
                _self.find('.content > .message').text(message);
                _self.addClass('show');
            },

            error: function (message) {
                _self  = $('.ps-modal.ps-error');
                _self.find('.content > .message').text(message);
                _self.addClass('show');
            },

            warning: function (message) {
                _self = $('.ps-modal.ps-warning');
                _self.find('.content > .message').text(message);
                _self.addClass('show');
            },

            confirm: function(message, callback) {
                _self = $('.ps-modal.ps-confirm');
                _self.find('.content > .message').text(message);
                _self.addClass('show');

                var $buttons = _self.find('.ps-button');

                /*
                * Call off() Jquery method because every time at running this method
                * added one listner the button .ps-button, this is a problem.
                * as will increasing the instructions.
                */
                $buttons.off().on('click', function(e){
                    e.preventDefault();
                    
                    var check;
                    var _is = $(this).attr('id');
                    _is === 'ps-btn-confirm' ? check = true : check = false;
                    typeof(callback) === 'function' ? callback(check) : console.info('Parametro de Callback inválido');
                    Modal.hide(true);
                });
            }
        },

        show: function(method, message, callback) {
            $('body').addClass('ps-modal-opened');

            switch (method) {
                case 'loading':
                    this.methods.loading();
                    return;

                case 'success':
                    this.methods.success(message);
                    break;

                case 'error':
                    this.methods.error(message);
                    return;

                case 'warning':
                    this.methods.warning(message);
                    return;

                case 'confirm':
                    this.methods.confirm(message, callback);
                    return;

                default:
                    console.info('tipo de modal não existente');
                    Modal.hide(true);
                    return;
            };
        },

        hide: function(removeOverlay) {
            if(removeOverlay) {
                $('body').removeClass('ps-modal-opened');
                $('.ps-modal').removeClass('show');
            } else {
                $('.ps-modal').removeClass('show');
            }

        }
    };

    win.Modal = Modal;
}(window, document, jQuery, undefined));
