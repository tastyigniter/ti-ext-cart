<?= form_open(current_url(),
    [
        'id'           => 'checkout-form',
        'role'         => 'form',
        'method'       => 'POST',
        'data-request' => $confirmCheckoutEventHandler,
    ]
); ?>

<?= partial('@customer_fields'); ?>

<?= partial('@timepicker'); ?>

<?php if ($orderType == 'delivery') { ?>
    <?= partial('@address_fields'); ?>
<?php } ?>

<?= partial('@payments'); ?>

<div class="form-group wrap-top">
    <label for="comment"><?= lang('sampoyigi.cart::default.checkout.label_comment'); ?></label>
    <textarea
        name="comment"
        id="comment"
        rows="3"
        class="form-control"
    ><?= set_value('comment', $order->comment); ?></textarea>
</div>

<?php if ($agreeTermsPage) { ?>
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon button-checkbox">
                <button
                    type="button"
                    class="btn"
                    data-color="info"
                    tabindex="7">&nbsp;&nbsp;<?= lang('button_agree_terms'); ?></button>
                <input
                    type="checkbox"
                    name="terms_condition"
                    id="terms-condition"
                    class="hidden"
                    value="1" <?= set_checkbox('terms_condition', '1'); ?>>
            </span>
            <span class="form-control"><?= sprintf(lang('label_terms'), page_url($agreeTermsPage)); ?></span>
        </div>
        <?= form_error('terms_condition', '<span class="text-danger col-xs-12">', '</span>'); ?>
    </div>
    <div
        class="modal fade"
        id="terms-modal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="agreeTermsModal"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<div class="form-group">
    <label for=""><?= lang('sampoyigi.cart::default.checkout.label_ip'); ?></label>
    <?= $order->ip_address; ?><br/>
    <small><?= lang('sampoyigi.cart::default.checkout.text_ip_warning'); ?></small>
</div>

<?= form_close(); ?>
