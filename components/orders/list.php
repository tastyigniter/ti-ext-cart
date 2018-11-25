<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th><?= lang('igniter.cart::default.orders.column_id'); ?></th>
            <th><?= lang('igniter.cart::default.orders.column_status'); ?></th>
            <th><?= lang('igniter.cart::default.orders.column_location'); ?></th>
            <th><?= lang('igniter.cart::default.orders.column_date'); ?></th>
            <th><?= lang('igniter.cart::default.orders.column_order'); ?></th>
            <th><?= lang('igniter.cart::default.orders.column_items'); ?></th>
            <th><?= lang('igniter.cart::default.orders.column_total'); ?></th>
            <th></th>
            <?php if ($showReviews) { ?>
                <th></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php if (count($customerOrders)) { ?>
            <?php foreach ($customerOrders as $order) { ?>
                <tr>
                    <td>
                        <a
                            href="<?= site_url($ordersPage, ['orderId' => $order->order_id]); ?>"
                        ><?= $order->order_id; ?></a>
                    </td>
                    <td><?= $order->status ? $order->status->status_name : ''; ?></td>
                    <td><?= $order->location ? $order->location->location_name : ''; ?></td>
                    <td><?= $order->order_time.' '.day_elapsed($order->order_date); ?></td>
                    <td><?= $order->order_type_name; ?></td>
                    <td><?= $order->total_items; ?></td>
                    <td><?= currency_format($order->order_total); ?></td>
                    <td>
                        <button
                            class="btn btn-primary re-order"
                            title="<?= lang('igniter.cart::default.orders.text_reorder'); ?>"
                            data-request="<?= $__SELF__.'::onReOrder'; ?>"
                            data-request-data="orderId: <?= $order->order_id; ?>"
                        ><i class="fa fa-mail-reply"></i></button>
                    </td>
                    <?php if ($showReviews) { ?>
                        <td>
                            <a
                                class="btn btn-warning leave-review"
                                title="<?= lang('igniter.cart::default.orders.text_leave_review'); ?>"
                                href="<?= site_url($addReviewsPage, [
                                    'saleType' => 'order',
                                    'saleId' => $order->order_id,
                                ]); ?>"
                            ><i class="fa fa-heart"></i></a>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="9"><?= lang('igniter.cart::default.orders.text_empty'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<div class="pagination-bar text-right">
    <div class="links"><?= $customerOrders->links(); ?></div>
</div>
