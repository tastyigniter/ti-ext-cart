+function ($) {
    "use strict"

    var Checkout = function (element, options) {
        this.$el = $(element)
        this.options = options || {}
        this.$checkoutBtn = $(document).find(this.options.buttonSelector)

        this.init()
    }

    Checkout.prototype.init = function () {
        $(document).on('click', '[data-checkout-control]', $.proxy(this.onControlClick, this))
        $(document).on('change', 'input[name="payment"]', $.proxy(this.onChoosePayment, this))
        $('input[name="' + this.options.paymentInputName + '"]:checked', document).trigger('change')

        $(document)
            .on('ajaxPromise', this.options.buttonSelector, function () {
                $(this).prop('disabled', true)
            })
            .on('ajaxFail ajaxDone', this.options.buttonSelector, function () {
                $(this).prop('disabled', false)
            })
            .on('submit', this.options.formSelector, $.proxy(this.onSubmitCheckoutForm, this))
            .on('ajaxFail ajaxDone', this.options.formSelector, $.proxy(this.onFailCheckoutForm, this))
    }

    Checkout.prototype.choosePayment = function ($el) {
        var self = this,
            $groupEl = $el.closest('.list-group'),
            $groupItemEl = $el.closest('.list-group-item'),
            $groupItems = $groupEl.find('.list-group-item'),
            $inputItems = $groupEl.find('.list-group-item input[name="' + this.options.paymentInputName + '"]'),
            $triggerItems = $groupEl.find('.list-group-item [data-trigger]'),
            $input = $el.find('input[name="' + this.options.paymentInputName + '"]')

        if ($groupItemEl.hasClass('loading') || $input.is(':checked'))
            return

        self.$checkoutBtn.addClass('disabled')
        $groupEl.addClass('loading')
        $groupItems.css('opacity', '0.7').removeClass('bg-light')
        $inputItems.prop('checked', false).prop('readOnly', true)
        $triggerItems.addClass('hide')

        $.request(this.options.choosePaymentHandler, {
            data: {code: $el.data('paymentCode')}
        }).done(function (json) {
            $input.prop('checked', true).trigger('change')
        }).always(function () {
            self.$checkoutBtn.removeClass('disabled')
            $groupEl.removeClass('loading')
            $groupItems.css('opacity', '1')
            $inputItems.prop('readOnly', false)
        })
    }

    Checkout.prototype.confirmCheckout = function ($el) {
        $(this.options.buttonSelector).prop('disabled', true)
        $($el.data('request-form')).submit()
    }

    // EVENT HANDLERS
    // ============================

    Checkout.prototype.onControlClick = function (event) {
        var $el = $(event.currentTarget),
            control = $el.data('checkoutControl')

        switch (control) {
            case 'choose-payment':
                this.choosePayment($el)
                break
            case 'confirm-checkout':
                this.confirmCheckout($el)
                break
        }

        return false
    }

    Checkout.prototype.onChoosePayment = function (event) {
        var $el = $(event.currentTarget)

        $el.closest('.list-group').find('.list-group-item').removeClass('bg-light')
        $el.closest('.list-group-item').addClass('bg-light')
    }

    Checkout.prototype.onSubmitCheckoutForm = function (event) {
        var $checkoutForm = $(event.target),
            $checkoutBtn = $('.checkout-btn')

        $checkoutBtn.prop('disabled', true)

        event.preventDefault();

        var _event = jQuery.Event('submitCheckoutForm')
        $checkoutForm.trigger(_event)
        if (_event.isDefaultPrevented()) {
            $checkoutBtn.prop('disabled', false)
            return false;
        }

        $checkoutForm.request($checkoutForm.data('handler')).always(function () {
            $checkoutBtn.prop('disabled', false)
        })
    }

    Checkout.prototype.onFailCheckoutForm = function (event) {
        $(this.options.formSelector).prop('disabled', false)
    }

    Checkout.DEFAULTS = {
        alias: 'checkout',
        formSelector: '#checkout-form',
        buttonSelector: '.checkout-btn',
        paymentInputName: 'payment',
        choosePaymentHandler: null,
    }

    // PLUGIN DEFINITION
    // ============================

    var old = $.fn.checkout

    $.fn.checkout = function (option) {
        var args = arguments

        return this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.checkout')
            var options = $.extend({}, Checkout.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.checkout', (data = new Checkout(this, options)))
            if (typeof option == 'string') data[option].apply(data, args)
        })
    }

    $.fn.checkout.Constructor = Checkout

    $.fn.checkout.noConflict = function () {
        $.fn.checkout = old
        return this
    }

    $(document).render(function () {
        $('[data-control="checkout"]').checkout()
    })
}(window.jQuery)