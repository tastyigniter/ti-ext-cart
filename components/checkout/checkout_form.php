<?= form_open(current_url(),
    [
        'id' => 'checkout-form',
        'role' => 'form',
        'method' => 'POST',
        'data-request' => $confirmCheckoutEventHandler,
    ]
); ?>

<?= partial('@customer_fields'); ?>

<?php if ($order->isDeliveryType()) { ?>
    <?= partial('@address_fields'); ?>
<?php } ?>

<?= partial('@payments'); ?>

<div class="form-group wrap-top">
    <label for="comment"><?= lang('igniter.cart::default.checkout.label_comment'); ?></label>
    <textarea
        name="comment"
        id="comment"
        rows="3"
        class="form-control"
    ><?= set_value('comment', $order->comment); ?></textarea>
</div>

<?php if ($agreeTermsPage) { ?>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input
                id="terms-condition"
                type="checkbox"
                name="terms_condition"
                value="1"
                class="custom-control-input" <?= set_checkbox('terms_condition', '1'); ?>
            >
            <label class="custom-control-label" for="terms-condition">
                <?= sprintf(lang('igniter.cart::default.checkout.label_terms'), page_url($agreeTermsPage)); ?>
            </label>
        </div>
        <?= form_error('terms_condition', '<span class="text-danger col-xs-12">', '</span>'); ?>
    </div>
<?php } ?>

<div class="form-group">
    <label for=""><?= lang('igniter.cart::default.checkout.label_ip'); ?></label>
    <?= $order->ip_address; ?><br/>
    <small><?= lang('igniter.cart::default.checkout.text_ip_warning'); ?></small>
</div>

<?= form_close(); ?>
