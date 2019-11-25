<?php if (count($customerOrders)) { ?>
    <div class="table-responsive">
        <table class="table table-borderless">
            <thead>
            <tr>
                <th><?= lang('igniter.cart::default.orders.column_location'); ?></th>
                <th><?= lang('igniter.cart::default.orders.column_status'); ?></th>
                <th><?= lang('igniter.cart::default.orders.column_date'); ?></th>
                <th><?= lang('igniter.cart::default.orders.column_total'); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($customerOrders as $order) { ?>
                <tr>
                    <td><?= $order->location ? $order->location->location_name : ''; ?></td>
                    <td><b><?= $order->status ? $order->status->status_name : ''; ?></b></td>
                    <td><?= $order->order_date->setTimeFromTimeString($order->order_time)->isoFormat($orderDateTimeFormat); ?></td>
                    <td><?= currency_format($order->order_total); ?>
                        (<?= $order->total_items.' '.lang('igniter.cart::default.orders.column_items'); ?>)
                    </td>
                    <td>
                        <a
                            class="btn btn-light"
                            href="<?= site_url($orderPage, ['orderId' => $order->order_id, 'hash' => $order->hash]); ?>"
                        ><i class="fa fa-receipt"></i>&nbsp;&nbsp;<?= lang('igniter.cart::default.orders.button_view_order'); ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-bar text-right">
        <div class="links"><?= $customerOrders->links(); ?></div>
    </div>
<?php } else { ?>
    <p><?= lang('igniter.cart::default.orders.text_empty'); ?></p>
<?php } ?>