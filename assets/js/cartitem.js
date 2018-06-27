+function ($) {
    "use strict"

    if ($.fn === undefined) $.fn = {}

    if ($.fn.cartItem === undefined)
        $.fn.cartItem = {}

    var CartItem = function (element, options) {
        this.$el = $(element)
        this.$form = this.$el.find('form')

        this.options = options

        this.$qtyControlElement = null

        this.init()
    }

    CartItem.prototype.constructor = CartItem

    CartItem.prototype.dispose = function () {
        this.unregisterHandlers()

        this.$el = null
        this.$form = null
        this.$qtyControlElement = null
    }

    CartItem.prototype.init = function () {
        this.$qtyControlElement = this.$el.find('[data-cart-toggle="quantity"]')

        this.registerHandlers()
    }

    CartItem.prototype.registerHandlers = function () {
        this.$el.on('click', '[data-operator]', $.proxy(this.onControlQuantity, this))

        this.$form.on('submit', $.proxy(this.onSubmitForm, this))
    }

    CartItem.prototype.unregisterHandlers = function () {

    }

    CartItem.prototype.onSubmitForm = function (event) {
        // event.preventDefault()
        // this.$form.submit()
    }

    CartItem.prototype.onControlQuantity = function (event) {
        var $button = $(event.currentTarget),
            $input = this.$qtyControlElement.find('input[name="quantity"]'),
            oldValue = parseFloat($input.val())

        if ($button.data('operator') === 'plus') {
            $input.val(oldValue + this.options.minQuantity)
        } else {
            $input.val((oldValue > 0) ? oldValue - this.options.minQuantity : 0)
        }
    }

    CartItem.DEFAULTS = {
        url: window.location,
        minQuantity: 1,
    }

    var old = $.fn.cartItem

    $.fn.cartItem = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result = undefined

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.cartItem')
            var options = $.extend({}, CartItem.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.cartItem', (data = new CartItem(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.cartItem.Constructor = CartItem

    // CART ITEM NO CONFLICT
    // =================

    $.fn.cartItem.noConflict = function () {
        $.fn.cartItem = old
        return this
    }
}(window.jQuery)
