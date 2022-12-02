+function ($) {
    "use strict";

    if ($.fn.cartBox === undefined)
        $.fn.cartBox = {}

    var CartBoxModal = function (options) {
        this.modal = null
        this.$modalRootElement = null

        this.options = $.extend({}, CartBoxModal.DEFAULTS, options)

        this.init()
        this.show()
    }

    CartBoxModal.prototype.dispose = function () {
        this.modal.dispose()
        this.$modalRootElement.remove()
        this.modal = null
        this.$modalElement = null
        this.$modalRootElement = null
    }

    CartBoxModal.prototype.init = function () {
        if (this.options.alias === undefined)
            throw new Error('CartBox modal option "alias" is not set.')

        this.$modalRootElement = $('<div/>', {
            id: 'cart-box-modal',
            class: 'modal fade',
            role: 'dialog',
            tabindex: -1,
            'aria-labelled': '#cart-box-modal',
            'aria-hidden': true,
        })

        this.$modalRootElement.one('hidden.bs.modal', $.proxy(this.onModalHidden, this))
        this.$modalRootElement.one('shown.bs.modal', $.proxy(this.onModalShown, this))
    }

    CartBoxModal.prototype.show = function () {
        this.$modalRootElement.html(
            '<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="text-center">'
            +'<span class="spinner"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><div class="mt-2">Loading...</div></span>'
            +'</div></div></div></div>'
        );

        $('body').append(this.$modalRootElement)

        this.modal = new bootstrap.Modal('#cart-box-modal')
        this.modal.show()
    }

    CartBoxModal.prototype.hide = function () {
        if (this.$modalElement)
            this.modal.hide()
    }

    CartBoxModal.prototype.getCartBoxElement = function () {
        return this.$modalElement.find('[data-control="cart-options"]')
    }

    CartBoxModal.prototype.toggleSelection = function (event) {
        this.$modalElement.find('[data-control="item-option"]').each(function (id, option) {
            var $parent = $(option),
                maxSelection = $parent.data('option-maximum'),
                selectedCount = $parent.find('input[type="checkbox"][data-option-price]:checked:not([disabled])').length

            if (maxSelection <= 0)
                return;

            $parent.find('input[type="checkbox"][data-option-price]:not(:checked)').prop('disabled', selectedCount === maxSelection)
        })
    }

    CartBoxModal.prototype.onQuantityOrOptionChanged = function (event) {
        var inputEl = this.$modalElement.find('[name="quantity"]'),
            $cartItem = this.$modalElement.find('[data-control="cart-item"]');

        var price = $cartItem.data('priceAmount');

        this.$modalElement.find('input[data-option-price]:checked:not([disabled])')
            .each(function (idx, option) {
                var optionPrice = $(option).data('optionPrice')
                price += parseFloat(optionPrice === undefined ? 0 : optionPrice);
            });

        this.$modalElement.find('select[data-option-price] option:selected:not([disabled])')
            .each(function (idx, option) {
                var optionPrice = $(option).data('optionPrice')
                price += parseFloat(optionPrice === undefined ? 0 : optionPrice);
            });

        this.$modalElement.find('[data-toggle="quantity"] input[data-option-price]:not([disabled])')
            .each(function (idx, option) {
                var val = parseInt($(option).val()),
                    optionPrice = $(option).data('optionPrice');
                if (val > 0) {
                    price += val * parseFloat(optionPrice === undefined ? 0 : optionPrice);
                }
            });

        price *= inputEl.val();

        $cartItem.find('[data-item-subtotal]')
            .html(app.currencyFormat(price));

        this.toggleSelection()
    }

    CartBoxModal.prototype.onSubmitForm = function () {
        if (this.options.onSubmit !== undefined)
            this.options.onSubmit.call(this)
    }

    CartBoxModal.prototype.onSuccessForm = function () {
        if (this.options.onSuccess !== undefined)
            this.options.onSuccess.call(this)
    }

    CartBoxModal.prototype.onFailedForm = function () {
        if (this.options.onFail !== undefined)
            this.options.onFail.call(this)
    }

    CartBoxModal.prototype.onModalHidden = function (event) {
        var $cartItem = this.$modalElement.find('[data-control="cart-item"]')

        this.dispose()

        $cartItem.cartItem('dispose')
        $cartItem.remove()

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
            }
        }).done($.proxy(this.onFetchModalContent, this))
        .fail(function () {
            self.$modalElement.modal('hide')
        })
    }

    CartBoxModal.prototype.onFetchModalContent = function (json) {
        this.$modalRootElement.html(json.result);
        this.$modalRootElement.modal()

        var $cartItem = this.$modalElement.find('[data-control="cart-item"]');

        $cartItem.on('input', '[name="quantity"]', $.proxy(this.onQuantityOrOptionChanged, this))
        $cartItem.on('change', '[data-option-price]', $.proxy(this.onQuantityOrOptionChanged, this))
        $cartItem.on('input', '[data-toggle="quantity"] input[data-option-price]', $.proxy(this.onQuantityOrOptionChanged, this))

        $cartItem.on('submit', 'form', $.proxy(this.onSubmitForm, this))
        $cartItem.on('ajaxDone', 'form', $.proxy(this.onSuccessForm, this))
        $cartItem.on('ajaxFail', 'form', $.proxy(this.onFailedForm, this))

        this.onQuantityOrOptionChanged()

        $cartItem.cartItem()
    }

    CartBoxModal.DEFAULTS = {
        alias: undefined,
        menuItem: undefined,
        onSubmit: undefined,
        onClose: undefined,
        onSuccess: undefined,
        onFail: undefined
    }

    $.fn.cartBox.modal = CartBoxModal
}(window.jQuery);

