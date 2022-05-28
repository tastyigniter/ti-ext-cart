<div
    data-control="cart-box"
    data-load-item-handler="{{ $loadCartItemEventHandler }}"
    data-update-item-handler="{{ $updateCartItemEventHandler }}"
    data-apply-coupon-handler="{{ $applyCouponEventHandler }}"
    data-apply-tip-handler="{{ $applyTipEventHandler }}"
    data-remove-item-handler="{{ $removeCartItemEventHandler }}"
    data-remove-condition-handler="{{ $removeConditionEventHandler }}"
    data-refresh-cart-handler="{{ $refreshCartEventHandler }}"
>
    <div id="cart-box" class="module-box">
        <div id="cart-items">
            @themePartial('@items')
        </div>

        <div id="cart-coupon">
            @themePartial('@coupon_form')
        </div>

        <?php if ($__SELF__->tippingEnabled()) { ?>
        <div id="cart-tip">
            @themePartial('@tip_form')
        </div>
        <?php } ?>

        <div id="cart-totals">
            @themePartial('@totals')
        </div>

        <div id="cart-buttons" class="mt-3">
            @themePartial('@buttons')
        </div>
    </div>
</div>
