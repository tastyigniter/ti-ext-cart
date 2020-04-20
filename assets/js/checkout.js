+function ($) {
    "use strict"

    if ($.fn.checkout === undefined)
        $.fn.checkout = {}

    var Checkout = function (element, options) {
        this.$el = $(element)
        this.options = options || {}
        this.$checkoutBtn = $(document).find(this.options.buttonSelector)
        this.paymentInputSelector = 'input[name='+this.options.paymentInputName+']'

        this.init()
    }

    Checkout.prototype.init = function () {
        $(document).on('click', '[data-checkout-control]', $.proxy(this.onControlClick, this))
        $(this.paymentInputSelector + ':checked', document).trigger('change')

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

    Checkout.prototype.confirmCheckout = function ($el) {
        this.$checkoutBtn.prop('disabled', true)
        $($el.data('request-form')).submit()
    }

    Checkout.prototype.choosePayment = function ($el) {
        var self = this,
            $paymentToggle = $el.closest('[data-toggle="payments"]')

        if ($paymentToggle.hasClass('in-progress') || $el.find(this.paymentInputSelector).is(':checked'))
            return

        this.$checkoutBtn.prop('disabled', true)
        $el.request(this.options.choosePaymentHandler, {
            data: {code: $el.data('paymentCode')}
        }).done(function (json) {
            $paymentToggle.find('.list-group-item').removeClass('bg-light')
            $el.closest('.list-group-item').addClass('bg-light')
            self.triggerPaymentInputChange($el)
        }).always(function () {
            self.$checkoutBtn.prop('disabled', false)
        })
    }

    Checkout.prototype.deletePaymentProfile = function ($el) {
        var self = this

        this.$checkoutBtn.prop('disabled', true)
        $el.request(this.options.deletePaymentHandler, {
            data: {code: $el.data('paymentCode')}
        }).done(function (json) {
            self.triggerPaymentInputChange($el)
        }).always(function () {
            self.$checkoutBtn.prop('disabled', false)
        })
    }

    Checkout.prototype.triggerPaymentInputChange = function ($el) {
        var paymentInputSelector = this.paymentInputSelector + '[value=' + $el.data('paymentCode') + ']';
        setTimeout(function () {
            $(paymentInputSelector, document).prop('checked', true).trigger('change')
        }, 1)
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
            case 'delete-payment-profile':
                this.deletePaymentProfile($el)
                break
        }

        return false
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
        choosePaymentHandler: undefined,
        deletePaymentHandler: undefined,
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

    $(document)
        .on('ajaxPromise', '[data-payment-code]', function() {
            var $indicatorContainer = $(this).closest('.progress-indicator-container')
            $indicatorContainer.prepend('<div class="progress-indicator"></div>')
            $indicatorContainer.addClass('is-loading')
        })
        .on('ajaxFail ajaxDone', '[data-payment-code]', function() {
            var $indicatorContainer = $(this).closest('.progress-indicator-container')
            $('div.progress-indicator', $indicatorContainer).remove()
            $indicatorContainer.removeClass('is-loading')
        })
}(window.jQuery)