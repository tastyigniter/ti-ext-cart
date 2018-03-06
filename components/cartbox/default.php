<div
    class="hidden-xs"
    data-control="cart-box"
    data-load-item-handler="<?= $loadCartItemEventHandler; ?>"
    data-update-item-handler="<?= $updateCartItemEventHandler; ?>"
    data-apply-coupon-handler="<?= $applyCouponEventHandler; ?>"
    data-change-order-type-handler="<?= $changeOrderTypeEventHandler; ?>"
    data-remove-item-handler="<?= $removeCartItemEventHandler; ?>"
    data-remove-condition-handler="<?= $removeConditionEventHandler; ?>"
>
    <div id="cart-box" class="module-box">
        <div class="panel panel-default panel-cart <?= ($pageIsCheckout) ? 'hidden-xs' : ''; ?>">
            <div class="panel-heading">
                <h3 class="panel-title"><?= lang('sampoyigi.cart::default.text_heading'); ?></h3>
            </div>

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
            </div>
        </div>

        <div id="cart-buttons">
            <?= partial('@buttons'); ?>
        </div>
    </div>
</div>
<div
    id="cart-mobile-buttons"
    class="<?= (!$pageIsCheckout) ? 'visible-xs' : 'hide'; ?>"
>
    <a
        class="btn btn-default cart-toggle text-nowrap"
        href="<?= site_url('cart') ?>"
    >
        <?= lang('sampoyigi.cart::default.text_heading'); ?>
        <span class="order-total"><?= $cartTotal; ?></span>
    </a>
</div>