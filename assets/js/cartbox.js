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

    CartBox.prototype.refreshCart = function ($el) {
        $.request(this.options.refreshCartHandler)
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

    CartBox.prototype.applyTip = function ($el) {
        var $input = this.$el.find('[name="amount"]'),
            amountType = this.$el.find('[name="amount_type"]').val()

        $.request(this.options.applyTipHandler, {
            data: {amount: $input.val(), amount_type: amountType}
        })
    }

    CartBox.prototype.updateTipAmount = function ($el) {
        var tipAmountType = $el.data('tipAmountType'),
            tipValue = $el.data('tipValue'),
            $tipCustomInput = this.$el.find('[data-tip-custom]')

        if ($el.hasClass('active'))
            return

        $tipCustomInput.hide();
        this.$el.find('[name="amount_type"]').val(tipAmountType);
        this.$el.find('[name="amount"]').val(tipValue !== undefined ? tipValue : 0);

        if (tipAmountType === 'custom') {
            $tipCustomInput.show();
            this.$el.find('[data-tip-amount-type]').removeClass('active')
            this.$el.find('[data-tip-amount-type="custom"]').addClass('active')
            return
        }

        this.$el.find('[data-cart-control="tip-amount"]').prop('disabled', true)
        this.applyTip()
    }

    CartBox.prototype.removeCondition = function ($el) {
        $.request(this.options.removeConditionHandler, {
            data: {conditionId: $el.data('cartConditionId')}
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
                this.refreshCart($el)
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
            case 'apply-tip':
                this.applyTip($el)
                break
            case 'tip-amount':
                this.updateTipAmount($el)
                break
        }

        return false
    }

    CartBox.DEFAULTS = {
        alias: 'cart',
        loadItemHandler: null,
        updateItemHandler: null,
        removeItemHandler: null,
        applyCouponHandler: null,
        applyTipHandler: null,
        removeConditionHandler: null
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
