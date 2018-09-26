<div
    class="<?= (!$pageIsCart) ? 'affix-cart d-none d-sm-block' : ''; ?>"
    data-control="cart-box"
    data-load-item-handler="<?= $loadCartItemEventHandler; ?>"
    data-update-item-handler="<?= $updateCartItemEventHandler; ?>"
    data-apply-coupon-handler="<?= $applyCouponEventHandler; ?>"
    data-change-order-type-handler="<?= $changeOrderTypeEventHandler; ?>"
    data-remove-item-handler="<?= $removeCartItemEventHandler; ?>"
    data-remove-condition-handler="<?= $removeConditionEventHandler; ?>"
>
    <div id="cart-box" class="module-box">
        <div class="panel panel-cart">
            <div class="panel-body">
                <div id="cart-control">
                    <?= partial('@control'); ?>
                </div>

                <div id="cart-items">
                    <?= partial('@items'); ?>
                </div>

                <div id="cart-coupon">
                    <?= partial('@coupon_form'); ?>
                </div>

                <div id="cart-totals">
                    <?= partial('@totals'); ?>
                </div>

                <div id="cart-buttons" class="mt-3">
                    <?= partial('@buttons'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div
    id="cart-mobile-buttons"
    class="<?= ($pageIsCart OR $pageIsCheckout) ? 'hide' : 'fixed-bottom d-block d-sm-none'; ?>"
>
    <a
        class="btn btn-primary btn-block cart-toggle text-nowrap"
        href="<?= site_url('cart') ?>"
    >
        <?= lang('igniter.cart::default.text_heading'); ?>:
        <span id="cart-total" class="font-weight-bold"><?= currency_format($cart->total()); ?></span>
    </a>
</div>