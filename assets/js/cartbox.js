+function ($) {
    "use strict"

    var CartBox = function (element, options) {
        this.$el = $(element)
        this.options = options || {}

        this.init()
    }

    CartBox.prototype.init = function () {
        $(document).on('click', '[data-cart-control]', $.proxy(this.onControlClick, this))
        this.$el.on('change', '[data-cart-toggle="order-type"]', $.proxy(this.onOrderTypeToggle, this))
    }

    CartBox.prototype.refreshCart = function (event) {
        console.log('refreshCart')
    }

    CartBox.prototype.loadItem = function ($el) {
        var modalOptions = $.extend({}, this.options, $el.data(), {
            onSubmit: function () {
                this.hide()
            }
        })

        new $.fn.cartBox.modal(modalOptions)
    }

    CartBox.prototype.addItem = function ($el) {
        $.request(this.options.updateItemHandler, {
            data: $el.data()
        }).fail(function (xhr) {
            $.ti.flashMessage({class: 'danger', text: xhr.responseText})
        })
    }

    CartBox.prototype.removeItem = function ($el) {
        $.request(this.options.removeItemHandler, {
            data: $el.data()
        }).fail(function (xhr) {
            $.ti.flashMessage({class: 'danger', text: xhr.responseText})
        })
    }

    CartBox.prototype.applyCoupon = function ($el) {
        var $input = this.$el.find('[name="coupon_code"]')

        $.request(this.options.applyCouponHandler, {
            data: {code: $input.val()}
        }).fail(function (xhr) {
            $.ti.flashMessage({class: 'danger', text: xhr.responseText})
        })
    }

    CartBox.prototype.removeCondition = function ($el) {
        $.request(this.options.removeConditionHandler, {
            data: {conditionId: $el.data('cartConditionId')}
        }).fail(function (xhr) {
            $.ti.flashMessage({class: 'danger', text: xhr.responseText})
        })
    }

    CartBox.prototype.confirmCheckout = function ($el) {
        var _event = jQuery.Event('submitCheckoutForm'),
            $checkoutForm = $($el.data('request-form'))

        $checkoutForm.trigger(_event)
        if (_event.isDefaultPrevented()) return

        $checkoutForm.request().fail(function (xhr) {
            $.ti.flashMessage({class: 'danger', text: xhr.responseText})
        })
    }

    // EVENT HANDLERS
    // ============================

    CartBox.prototype.onControlClick = function (event) {
        var $el = $(event.currentTarget),
            control = $el.data('cartControl')

        switch (control) {
            case 'load-item':
                this.loadItem($el)
                break
            case 'add-item':
                this.addItem($el)
                break
            case 'refresh':
                this.refresh($el)
                break
            case 'remove-item':
                this.removeItem($el)
                break
            case 'remove-condition':
                this.removeCondition($el)
                break
            case 'apply-coupon':
                this.applyCoupon($el)
                break
            case 'confirm-checkout':
                this.confirmCheckout($el)
                break
        }

        return false
    }

    CartBox.prototype.onOrderTypeToggle = function (event) {
        var $el = $(event.currentTarget)
        $.request(this.options.changeOrderTypeHandler, {
            data: {'type': $el.val()}
        }).fail(function (xhr) {
            $.ti.flashMessage({class: 'danger', text: xhr.responseText})
        })
    }

    CartBox.DEFAULTS = {
        alias: 'cart',
        checkoutHandler: null,
        loadItemHandler: null,
        updateItemHandler: null,
        removeItemHandler: null,
        applyCouponHandler: null,
        removeConditionHandler: null,
        changeOrderTypeHandler: null,
    }

    // PLUGIN DEFINITION
    // ============================

    var old = $.fn.cartBox

    $.fn.cartBox = function (option) {
        var args = arguments

        return this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.cartBox')
            var options = $.extend({}, CartBox.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.cartBox', (data = new CartBox(this, options)))
            if (typeof option == 'string') data[option].apply(data, args)
        })
    }

    $.fn.cartBox.Constructor = CartBox

    $.fn.cartBox.noConflict = function () {
        $.fn.cartBox = old
        return this
    }

    $(document).ready(function () {
        $('[data-control="cart-box"]').cartBox()
    })

}(window.jQuery)
