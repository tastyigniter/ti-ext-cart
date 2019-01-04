<?php if ($cart->count()) { ?>
    <div class="cart-coupon">
        <div
            class="input-group">
            <input
                type="text"
                name="coupon_code"
                class="form-control"
                value="<?= ($coupon = $cart->getCondition('coupon')) ? $coupon->getMetaData('code') : '' ?>"
                placeholder="<?= lang('igniter.cart::default.text_apply_coupon'); ?>"/>

            <span class="input-group-btn">
                <a
                    class="btn btn-outline-default"
                    data-cart-control="apply-coupon"
                    title="<?= lang('igniter.cart::default.button_apply_coupon'); ?>"
                ><i class="fa fa-check"></i></a>
            </span>
        </div>
    </div>
<?php } ?>