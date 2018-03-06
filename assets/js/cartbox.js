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
        $($el.data('request-form')).request().fail(function (xhr) {
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

//    var alert_close = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'
//
//    var cartHeight = pageHeight - (65 / 100 * pageHeight)
//
//    $(document).on('ready', function () {
//        $('.cart-alert-wrap .alert').fadeTo('slow', 0.1).fadeTo('slow', 1.0).delay(5000).slideUp('slow')
//        $('#cart-info .cart-items').css({
//            "height": "auto",
//            "max-height": cartHeight,
//            "overflow": "auto",
//            "margin-right": "-15px",
//            "padding-right": "5px"
//        })
//
//        $(window).bind("load resize", function () {
//            var sideBarWidth = $('#content-right .side-bar').width()
//            $('#cart-box-affix').css('width', sideBarWidth)
//        })
//    })

//    function addToCart(menu_id, quantity) {
//        if ($('#menu-options' + menu_id).length) {
//            var data = $('#menu-options' + menu_id + ' input:checked, #menu-options' + menu_id + ' input[type="hidden"], #menu-options' + menu_id + ' select, #menu-options' + menu_id + ' textarea, #menu-options' + menu_id + '  input[type="text"]')
//        } else {
//            var data = 'menu_id=' + menu_id + '&quantity=' + quantity
//        }
//
//        $('#menu' + menu_id + ' .add_cart').removeClass('failed')
//        $('#menu' + menu_id + ' .add_cart').removeClass('added')
//        if (!$('#menu' + menu_id + ' .add_cart').hasClass('loading')) {
//            $('#menu' + menu_id + ' .add_cart').addClass('loading')
//        }
//
//        $.ajax({
//            url: js_site_url('cart/cart/add'),
//            type: 'post',
//            data: data,
//            dataType: 'json',
//            success: function (json) {
//                $('#menu' + menu_id + ' .add_cart').removeClass('loading')
//                $('#menu' + menu_id + ' .add_cart').removeClass('failed')
//                $('#menu' + menu_id + ' .add_cart').removeClass('added')
//
//                if (json['option_error']) {
//                    $('#cart-options-alert .alert').remove()
//
//                    $('#cart-options-alert').append('<div class="alert" style="display: none;">' + alert_close + json['option_error'] + '</div>')
//                    $('#cart-options-alert .alert').fadeIn('slow')
//
//                    $('#menu' + menu_id + ' .add_cart').addClass('failed')
//                } else {
//                    $('#optionsModal').modal('hide')
//
//                    if (json['error']) {
//                        $('#menu' + menu_id + ' .add_cart').addClass('failed')
//                    }
//
//                    if (json['success']) {
//                        $('#menu' + menu_id + ' .add_cart').addClass('added')
//                    }
//
//                    updateCartBox(json)
//                }
//            }
//        })
//    }
//
//    function openMenuOptions(menu_id, row_id) {
//        if (menu_id) {
//            var row_id = (row_id) ? row_id : ''
//
//            $.ajax({
//                url: js_site_url('cart/cart/options?menu_id=' + menu_id + '&row_id=' + row_id),
//                dataType: 'html',
//                success: function (html) {
//                    $('#optionsModal').remove()
//                    $('body').append('<div id="optionsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>')
//                    $('#optionsModal').html(html)
//
//                    $('#optionsModal').modal()
//                    $('#optionsModal').on('hidden.bs.modal', function (e) {
//                        $('#optionsModal').remove()
//                    })
//                },
//                error: function (xhr, ajaxOptions, thrownError) {
//                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText)
//                }
//            })
//        }
//    }
//
//    function removeCart(menu_id, row_id, quantity) {
//        $.ajax({
//            url: js_site_url('cart/cart/remove'),
//            type: 'post',
//            data: 'menu_id' + menu_id + '&row_id=' + row_id + '&quantity=' + quantity,
//            dataType: 'json',
//            success: function (json) {
//                updateCartBox(json)
//            }
//        })
//    }
//
//    function applyCoupon() {
//        var coupon_code = $('#cart-box input[name="coupon_code"]').val()
//        $.ajax({
//            url: js_site_url('cart/cart/coupon'),
//            type: 'post',
//            data: 'action=add&code=' + coupon_code,
//            dataType: 'json',
//            success: function (json) {
//                updateCartBox(json)
//            }
//        })
//    }
//
//    function clearCoupon(coupon_code) {
//        $('input[name=\'coupon\']').attr('value', '')
//
//        $.ajax({
//            url: js_site_url('cart/cart/coupon'),
//            type: 'post',
//            data: 'action=remove&code=' + coupon_code,
//            dataType: 'json',
//            success: function (json) {
//                updateCartBox(json)
//            }
//        })
//    }
//
//    function updateCartBox(json) {
//        var alert_message = ''
//
//        if (json['redirect']) {
//            window.location.href = json['redirect']
//        }
//
//        if (json['error']) {
//            alert_message = '<div class="alert">' + alert_close + json['error'] + '</div>'
//            updateCartAlert(alert_message)
//        } else {
//            if (json['success']) {
//                alert_message = '<div class="alert">' + alert_close + json['success'] + '</div>'
//            }
//
//            $('#cart-box').load(js_site_url('cart/cart #cart-box > *'), function (response) {
//                updateCartAlert(alert_message)
//            })
//        }
//    }
//
//    function updateCartAlert(alert_message) {
//        if (alert_message != '') {
//            $('.cart-alert-wrap .alert, .cart-alert-wrap .cart-alert').empty()
//            $('.cart-alert-wrap .cart-alert').append(alert_message)
//            $('.cart-alert-wrap .alert').slideDown('slow').fadeTo('slow', 0.1).fadeTo('slow', 1.0).delay(5000).slideUp('slow')
//        }
//
//        if ($('#cart-info .order-total').length > 0) {
//            $('#cart-box-affix .navbar-toggle .order-total').html(" - " + $('#cart-info .order-total').html())
//        }
//
//        $('#cart-info .cart-items').css({
//            "height": "auto",
//            "max-height": cartHeight,
//            "overflow": "auto",
//            "margin-right": "-15px",
//            "padding-right": "5px"
//        })
//    }
