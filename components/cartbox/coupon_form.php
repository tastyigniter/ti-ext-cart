<?php if ($cartItemsCount) { ?>
    <div class="cart-coupon">
        <div
            class="input-group">
            <input
                type="text"
                name="coupon_code"
                class="form-control"
                value=""
                placeholder="<?= lang('sampoyigi.cart::default.text_apply_coupon'); ?>"/>

            <span class="input-group-btn">
                <a
                    class="btn btn-default"
                    data-cart-control="apply-coupon"
                    title="<?= lang('sampoyigi.cart::default.button_apply_coupon'); ?>"
                ><i class="fa fa-check"></i></a>
            </span>
        </div>
    </div>
<?php } ?>