+function ($) {
    "use strict"

    var CartBox = function (element, options) {
        this.$el = $(element)
        this.options = options || {}

        this.init()
        this.initAffix()
    }

    CartBox.prototype.init = function () {
        $(document).on('click', '[data-cart-control]', $.proxy(this.onControlClick, this))
        this.$el.on('change', '[data-cart-toggle="order-type"]', $.proxy(this.onOrderTypeToggle, this))

        $(document).on('change', 'input[name="payment"]', $.proxy(this.onChoosePayment, this))
        $('input[name="payment"]:checked', document).trigger('change')

        $(document)
            .on('ajaxPromise', '.checkout-btn', function () {
                $(this).prop('disabled', true)
            })
            .on('ajaxFail ajaxDone', '.checkout-btn', function () {
                $(this).prop('disabled', false)
            })
            .on('ajaxPromise', '#checkout-form', $.proxy(this.onSubmitCheckoutForm, this))
            .on('ajaxFail ajaxDone', '#checkout-form', function () {
                $('.checkout-btn').prop('disabled', false)
            })
    }

    CartBox.prototype.initAffix = function () {
        var $affixEl = this.$el.closest('.affix-cart'),
            offsetTop = $('.navbar-top').height(),
            offsetBottom = $('footer.footer').outerHeight(true),
            cartWidth = $affixEl.parent().width()

        $affixEl.affix({
            offset: {top: offsetTop, bottom: offsetBottom}
        })

        $affixEl.on('affixed.bs.affix', function () {
            $affixEl.css('width', cartWidth)
        })
    }

    CartBox.prototype.refreshCart = function (event) {
    }

    CartBox.prototype.loadItem = function ($el) {
        var modalOptions = $.extend({}, this.options, $el.data(), {
            onSuccess: function () {
                this.hide()
            }
        })

        new $.fn.cartBox.modal(modalOptions)
    }

    CartBox.prototype.addItem = function ($el) {
        $.request(this.options.updateItemHandler, {
            data: $el.data(),
        })
    }

    CartBox.prototype.removeItem = function ($el) {
        $.request(this.options.removeItemHandler, {
            data: $el.data()
        })
    }

    CartBox.prototype.applyCoupon = function ($el) {
        var $input = this.$el.find('[name="coupon_code"]')

        $.request(this.options.applyCouponHandler, {
            data: {code: $input.val()}
        })
    }

    CartBox.prototype.removeCondition = function ($el) {
        $.request(this.options.removeConditionHandler, {
            data: {conditionId: $el.data('cartConditionId')}
        })
    }

    CartBox.prototype.confirmCheckout = function ($el) {
        var $checkoutForm = $($el.data('request-form'))

        $checkoutForm.request()
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
        var $el = $(event.currentTarget),
            $parentEl = $el.closest('#cart-control')

        $parentEl.find('[data-cart-toggle="order-type"]').attr('disabled', true)
        $parentEl.find('.btn').addClass('disabled')
        $.request(this.options.changeOrderTypeHandler, {
            data: {'type': $el.val()}
        }).always(function () {
            $parentEl.find('[data-cart-toggle="order-type"]').attr('disabled', false)
            $parentEl.find('.btn').removeClass('disabled')
        })
    }

    CartBox.prototype.onChoosePayment = function (event) {
        var $el = $(event.currentTarget),
            $parentEl = $el.closest('.list-group')

        $parentEl.find('.list-group-item').removeClass('bg-light')
        $el.closest('.list-group-item').addClass('bg-light')
    }

    CartBox.prototype.onSubmitCheckoutForm = function (event) {
        var _event = jQuery.Event('submitCheckoutForm'),
            $checkoutForm = $(event.target),
            $checkoutBtn = $('.checkout-btn')

        $checkoutBtn.prop('disabled', true)

        $checkoutForm.trigger(_event)
        if (_event.isDefaultPrevented()) {
            $checkoutBtn.prop('disabled', false)
            return false;
        }
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

    $(document).render(function () {
        $('[data-control="cart-box"]').cartBox()
    })

}(window.jQuery)
