<?php if ($paymentGateways) { ?>
    <div class="row">
        <div class="col-sm-8">
            <input
                type="hidden"
                name="payment"
                value="">
            <div class="form-group">
                <label for=""><?= lang('igniter.cart::default.checkout.label_payment_method'); ?></label><br/>
                <div class="list-group">
                    <?php foreach ($paymentGateways as $paymentGateway) { ?>
                        <div class="list-group-item">
                            <div
                                class="custom-control custom-radio<?= set_value('payment') == $paymentGateway->code ? ' active' : ''; ?>"
                            >
                                <input
                                    type="radio"
                                    id="payment-<?= $paymentGateway->code ?>"
                                    class="custom-control-input"
                                    name="payment"
                                    value="<?= $paymentGateway->code ?>"
                                    autocomplete="off"
                                    <?= set_radio('payment', $paymentGateway->code) ?>
                                />
                                <label
                                    class="custom-control-label d-block"
                                    for="payment-<?= $paymentGateway->code ?>"
                                ><?= $paymentGateway->name; ?></label>
                                <?php if (!$paymentGateway->isApplicable($order->order_total, $paymentGateway)) { ?>
                                    <p class="text-info font-weight-normal mb-0">
                                        <?= sprintf(
                                            lang('igniter.payregister::default.alert_min_order_total'),
                                            currency_format($paymentGateway->order_total),
                                            $paymentGateway->name
                                        ); ?>
                                    </p>
                                <?php } ?>
                            </div>
                            <?= $paymentGateway->renderPaymentForm($this->controller); ?>
                        </div>
                    <?php } ?>
                </div>
                <?= form_error('payment', '<span class="text-danger">', '</span>'); ?>
            </div>
        </div>
    </div>
<?php } ?>
