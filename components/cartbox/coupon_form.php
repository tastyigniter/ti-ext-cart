<?php if ($cart->count()) { ?>
    <form
        id="coupon-form"
        method="POST"
        role="form"
        data-request="<?= $applyCouponEventHandler; ?>"
    >
        <div class="cart-coupon">
            <div
                class="input-group">
                <input
                    type="text"
                    name="code"
                    class="form-control"
                    value="<?= ($coupon = $cart->getCondition('coupon')) ? $coupon->getMetaData('code') : '' ?>"
                    placeholder="<?= lang('igniter.cart::default.text_apply_coupon'); ?>"/>

                <span class="input-group-append">
                <button
                    type="submit"
                    class="btn btn-light"
                    data-replace-loading="fa fa-spinner fa-spin"
                    title="<?= lang('igniter.cart::default.button_apply_coupon'); ?>"
                ><i class="fa fa-check"></i></button>
            </span>
            </div>
        </div>
    </form>
<?php } ?>