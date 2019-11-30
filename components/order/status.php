<div class="label label-light mb-3">
    <span class="h6">
        <i class="fa fa-clock"></i>&nbsp;
        <?= $order->order_date->setTimeFromTimeString($order->order_time)->isoFormat($orderDateTimeFormat); ?>
    </span>
</div>
<h5><?= lang('igniter.cart::default.checkout.text_order_no').$order->order_id; ?></h5>
<?php if ($order->status) { ?>
    <h3 style="color: <?= $order->status->status_color; ?>;"><?= $order->status->status_name; ?></h3>
    <p class="lead"><?= $order->status->status_comment; ?></p>
<?php } else { ?>
    <h3>--</h3>
<?php } ?>

<p class="mb-0"><?= lang('igniter.cart::default.checkout.text_success_message'); ?></p>

<div class="mt-3">
    <?php if (!$hideReorderBtn) { ?>
        <button
            class="btn btn-primary re-order"
            data-request="<?= $__SELF__.'::onReOrder'; ?>"
            data-request-data="orderId: <?= $order->order_id; ?>"
            data-attach-loading
        ><?= lang('igniter.cart::default.orders.button_reorder'); ?></button>
    <?php } ?>
</div>
