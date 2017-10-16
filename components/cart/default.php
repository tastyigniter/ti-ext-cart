<?php
$pageIsCheckout = ($this->controller->getClass() == 'checkout');
$fullyClosed = ($isClosed OR !$canAcceptOrder);
if ($fullyClosed)
    $buttonLang = 'sampoyigi.cart::default.text_is_closed';
else if (!$pageIsCheckout)
    $buttonLang = 'sampoyigi.cart::default.button_order';
//else if ($showPaymentButton)
//    $buttonLang = 'sampoyigi.cart::default.button_payment';
else
    $buttonLang = 'sampoyigi.cart::default.button_confirm';
?>
<div
    class="hidden-xs"
    data-alias="<?= $cartAlias; ?>"
    data-control="cart-box">
    <div id="cart-box" class="module-box">
        <div class="panel panel-default panel-cart <?= ($pageIsCheckout) ? 'hidden-xs' : ''; ?>">
            <div class="panel-heading">
                <h3 class="panel-title"><?= lang('sampoyigi.cart::default.text_heading'); ?></h3>
            </div>

            <div class="panel-body">
                <?php if ($hasDelivery OR $hasCollection) { ?>
                    <?= partial('@control'); ?>
                <?php } ?>

                <div id="cart-info">
                    <?php if ($countCartItems) { ?>
                        <?= partial('@items'); ?>

                        <?= partial('@coupon'); ?>

                        <?= partial('@total'); ?>
                    <?php }
                    else { ?>
                        <div class="panel-body"><?= lang('text_no_cart_items'); ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="cart-buttons wrap-none">
            <div class="center-block">
                <a
                    class="btn <?= $fullyClosed ? 'btn-default' : 'btn-primary'; ?> btn-block btn-lg"
                    <?= (!$fullyClosed AND !$pageIsCheckout)
                        ? 'href="'.site_url('checkout').'""' : 'data-control="submit-checkout"'; ?>
                >
                    <?= lang($buttonLang); ?>
                </a>
            </div>
        </div>
    </div>
</div>
<div
    id="cart-buttons"
    class="<?= (!$pageIsCheckout) ? 'visible-xs' : 'hide'; ?>"
>
    <a
        class="btn btn-default cart-toggle text-nowrap"
        href="<?= site_url('cart') ?>"
    >
        <?= lang('sampoyigi.cart::default.text_heading'); ?><span class="order-total"><?= $cart->total(); ?></span>
    </a>
</div>
<script type="text/javascript"><!--
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
    //
    //    $(document).on('change', 'input[name="order_type"]', function () {
    //        if (typeof this.value !== 'undefined') {
    //            var order_type = this.value
    //
    //            $.ajax({
    //                url: js_site_url('cart/cart/order_type'),
    //                type: 'post',
    //                data: 'order_type=' + order_type,
    //                dataType: 'json',
    //                success: function (json) {
    //                    if (json['redirect'] && json['order_type'] == order_type) {
    //                        window.location.href = json['redirect']
    //                    }
    //                }
    //            })
    //        }
    //    })
    //
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
    //--></script>