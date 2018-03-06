<div class="row">
    <div class="col-sm-8">
        <div class="form-group">
            <label for=""><?= lang('sampoyigi.cart::default.checkout.label_payment_method'); ?></label><br/>
            <div class="list-group">
                <?php foreach ($paymentGateways as $paymentGateway) { ?>
                    <div class="list-group-item"><?= $paymentGateway->renderPaymentForm($this->controller); ?></div>
                <?php } ?>
            </div>
            <?= form_error('payment', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
</div>
