+function ($) {
    "use strict";

    if ($.fn.cartBox === undefined)
        $.fn.cartBox = {}

    var CartBoxModal = function (options) {
        this.$modalRootElement = null

        this.options = $.extend({}, CartBoxModal.DEFAULTS, options)

        this.init()
        this.show()
    }

    CartBoxModal.prototype.dispose = function () {
        this.$modalElement.modal('hide')
        this.$modalRootElement.remove()
        this.$modalElement = null
        this.$modalRootElement = null
    }

    CartBoxModal.prototype.init = function () {
        if (this.options.alias === undefined)
            throw new Error('CartBox modal option "alias" is not set.')

        this.$modalRootElement = $('<div/>', {
            id: 'cart-box-modal',
            class: 'modal',
            role: 'dialog',
            tabindex: -1,
            ariaLabelled: '#cart-box-modal',
            ariaHidden: true,
        })

        this.$modalRootElement.one('hide.bs.modal', $.proxy(this.onModalHidden, this))
        this.$modalRootElement.one('shown.bs.modal', $.proxy(this.onModalShown, this))
    }

    CartBoxModal.prototype.show = function () {
        this.$modalRootElement.html(
            '<div class="modal-dialog"><div class="modal-content"><div class="modal-body">'
            +'<span class="spinner"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></span>'
            +'</div></div></div>'
        );

        this.$modalRootElement.modal()
    }

    CartBoxModal.prototype.hide = function () {
        if (this.$modalElement)
            this.$modalElement.trigger('hide.bs.modal')
    }

    CartBoxModal.prototype.getCartBoxElement = function () {
        return this.$modalElement.find('[data-control="cart-options"]')
    }

    CartBoxModal.prototype.submitForm = function () {
        if (this.options.onSubmit !== undefined)
            this.options.onSubmit.call(this)
    }

    CartBoxModal.prototype.onModalHidden = function (event) {
        var $cartItem = this.$modalElement.find('[data-control="cart-item"]')

        $cartItem.cartItem('dispose')
        $cartItem.remove()

        this.dispose()

        if (this.options.onClose !== undefined)
            this.options.onClose.call(this)
    }

    CartBoxModal.prototype.onModalShown = function (event) {
        var self = this

        this.$modalElement = $(event.target)

        $.request(this.options.loadItemHandler, {
            data: {
                rowId: this.options.rowId,
                menuId: this.options.menuId,
            },
            success: function (json) {
                self.$modalRootElement.html(json.result);
                self.$modalRootElement.modal()

                var $cartItem = self.$modalElement.find('[data-control="cart-item"]')

                $cartItem.on('submit', 'form', $.proxy(self.submitForm, self))

                $cartItem.cartItem()
            }
        })
    }

    CartBoxModal.DEFAULTS = {
        alias: undefined,
        menuItem: undefined,
        onSubmit: undefined,
        onClose: undefined
    }

    $.fn.cartBox.modal = CartBoxModal
}(window.jQuery);

