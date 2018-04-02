<p>
    <?= sprintf(
        lang('sampoyigi.cart::default.checkout.text_success_message'),
        $order->order_id, site_url($ordersPage, ['orderId' => $order->order_id])
    ); ?>
</p>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <strong><?= lang('sampoyigi.cart::default.checkout.text_order_details'); ?></strong>
        </h4>
    </div>
    <div class="panel-body">
        <div class="col-sm-4">
            <p>
                <?= sprintf(
                    lang('sampoyigi.cart::default.checkout.text_order_info'),
                    $order->order_type,
                    mdate($orderDateFormat, strtotime($order->date_added)),
                    ucwords($order->order_type),
                    mdate("{$orderDateFormat} {$orderTimeFormat}", strtotime("{$order->order_date} {$order->order_time}")),
                    $order->payment_method
                        ? $order->payment_method->name
                        : lang('sampoyigi.cart::default.checkout.text_no_payment')
                ); ?>
            </p>
        </div>
        <div class="col-sm-4">
            <?php if ($order->address) { ?>
                <strong><?= lang('sampoyigi.cart::default.checkout.text_delivery_address'); ?>:</strong>
                <address><?= format_address($order->address->toArray()); ?></address>
            <?php } ?>
        </div>
        <div class="col-sm-4">
            <strong><?= lang('sampoyigi.cart::default.checkout.text_your_local'); ?></strong><br/>
            <strong><?= $order->location->location_name; ?></strong><br/>
            <address><?= format_address($order->location->getAddress()); ?></address>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <strong><?= lang('sampoyigi.cart::default.checkout.text_order_items'); ?></strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($menuItems = $order->getOrderMenus()) {
            $menuItemsOptions = $order->getOrderMenuOptions();
            ?>
            <div class="table-responsive">
                <table class="table table-condensed">
                    <tbody>
                    <?php foreach ($menuItems as $menuItem) { ?>
                        <tr>
                            <td><?= $menuItem->quantity; ?> x</td>
                            <td class="text-left" width="65%">
                                <?= $menuItem->name; ?>
                                <?php if ($menuItemOptions = $menuItemsOptions->get($menuItem->menu_id)) { ?>
                                    <div>
                                        <?php foreach ($menuItemOptions as $menuItemOption) { ?>
                                            <small>
                                                <?= lang('main::default.text_plus'); ?>
                                                <?= $menuItemOption->order_option_name; ?>
                                                <?= lang('main::default.text_equals'); ?>
                                                <?= currency_format($menuItemOption->order_option_price); ?>
                                            </small><br>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                                <div>
                                    <small><b><?= $menuItem->comment; ?></b></small>
                                </div>
                            </td>
                            <td class="text-right"><?= currency_format($menuItem->price); ?></td>
                            <td class="text-right"><?= currency_format($menuItem->subtotal); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="thick-line" colspan="9999"></td>
                    </tr>
                    <?php foreach ($order->getOrderTotals() as $total) { ?>
                        <?php if ($order->isCollectionType() AND $total->code == 'delivery') continue; ?>
                        <?php $thickLine = ($total->code == 'order_total' OR $total->code == 'total'); ?>
                        <tr>
                            <td class="<?= $thickLine ? 'thick' : 'no'; ?>-line" width="1"></td>
                            <td class="<?= $thickLine ? 'thick' : 'no'; ?>-line"></td>
                            <td class="<?= $thickLine ? 'thick' : 'no'; ?>-line text-left">
                                <?= $total->title; ?>
                            </td>
                            <td class="<?= $thickLine ? 'thick' : 'no'; ?>-line text-right">
                                <?= currency_format($total->value); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        <p><?= lang('sampoyigi.cart::default.checkout.text_thank_you'); ?></p>
    </div>
</div>
