<h2 class="h4 mb-0 font-weight-normal"><?= lang('igniter.cart::default.checkout.text_order_details'); ?></h2>

<div class="cart-items pt-2">
    <ul>
        <?php
        $orderItemOptions = $order->getOrderMenuOptions();
        foreach ($order->getOrderMenus() as $orderItem) { ?>
            <li>
                <span class="price pull-right"><?= currency_format($orderItem->subtotal); ?></span>
                <span class="name">
                    <?php if ($orderItem->quantity > 1) { ?>
                        <span class="quantity font-weight-bold">
                            <?= $orderItem->quantity.' '.lang('igniter.cart::default.text_times'); ?>
                        </span>
                    <?php } ?>
                    <?= $orderItem->name; ?>
                </span>
                <?php if ($itemOptions = $orderItemOptions->get($orderItem->order_menu_id)) { ?>
                    <ul class="list-unstyled small text-muted">
                        <?php foreach ($itemOptions as $itemOption) { ?>
                            <li><?= $itemOption->order_option_name; ?>&nbsp;
                                <?php if ($itemOption->order_option_price > 0) { ?>
                                    (<?= currency_format($itemOption->order_option_price); ?>)
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php if (!empty($orderItem->comment)) { ?>
                    <p class="comment text-muted small">
                        <?= $orderItem->comment; ?>
                    </p>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
</div>

<div class="cart-totals">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <tbody>
            <tr>
                <td class="border-top p-0" colspan="99999"></td>
            </tr>
            <?php foreach ($order->getOrderTotals() as $orderTotal) { ?>
                <?php if ($order->isCollectionType() AND $orderTotal->code == 'delivery') continue; ?>
                <?php $thickLine = ($orderTotal->code == 'order_total' OR $orderTotal->code == 'total'); ?>
                <tr>
                    <td class="px-0 <?= $thickLine ? 'border-top lead font-weight-bold' : 'text-muted border-0' ?>">
                        <?= $orderTotal->title; ?>
                    </td>
                    <td class="text-right px-0 <?= $thickLine ? 'border-top lead font-weight-bold' : 'border-0' ?>">
                        <?= currency_format($orderTotal->value); ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
