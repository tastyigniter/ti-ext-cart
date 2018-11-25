<div class="table-responsive">
    <table class="table table-borderless">
        <tr>
            <td style="width:20%;"><b><?= lang('igniter.cart::default.orders.column_id'); ?>:</b></td>
            <td><?= $customerOrder->order_id; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.cart::default.orders.column_date'); ?>:</b></td>
            <td><?= day_elapsed($customerOrder->order_date).' - '.$customerOrder->order_time; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.cart::default.orders.column_date_added'); ?>:</b></td>
            <td><?= day_elapsed($customerOrder->date_added); ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.cart::default.orders.column_order'); ?>:</b></td>
            <td><?= $customerOrder->order_type_name; ?></td>
        </tr>
        <?php if ($customerOrder->isDeliveryType()) { ?>
            <tr>
                <td><b><?= lang('igniter.cart::default.orders.column_delivery'); ?>:</b></td>
                <td><?= format_address($customerOrder->address); ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td><b><?= lang('igniter.cart::default.orders.column_payment'); ?>:</b></td>
            <td><?= $customerOrder->payment_method
                    ? $customerOrder->payment_method->name
                    : lang('igniter.cart::default.checkout.text_no_payment');
                ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.cart::default.orders.column_location'); ?>:</b></td>
            <td>
                <?= $customerOrder->location->location_name; ?><br/>
                <?= format_address($customerOrder->location->getAddress()); ?>
            </td>
        </tr>
    </table>
</div>

<div class="text-center">
    <h4><?= lang('igniter.cart::default.orders.text_order_menus'); ?></h4>
</div>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th style="width:7%"></th>
            <th class="text-left"
                width="65%"><?= lang('igniter.cart::default.orders.column_menu_name'); ?></th>
            <th class="text-right"><?= lang('igniter.cart::default.orders.column_menu_price'); ?></th>
            <th class="text-right"><?= lang('igniter.cart::default.orders.column_menu_subtotal'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $menuItemsOptions = $customerOrder->getOrderMenuOptions();
        foreach ($customerOrder->getOrderMenus() as $menu) { ?>
            <tr id="<?= $menu->menu_id; ?>">
                <td><?= $menu->quantity; ?> x</td>
                <td class="text-left"><?= $menu->name; ?><br/>
                    <?php if ($menuItemOptions = $menuItemsOptions->get($menu->menu_id)) { ?>
                        <div>
                            <?php foreach ($menuItemOptions as $menuItemOption) { ?>
                                <small>
                                    <?= lang('main::lang.text_plus'); ?>
                                    <?= $menuItemOption->order_option_name; ?>
                                    <?= lang('main::lang.text_equals'); ?>
                                    <?= currency_format($menuItemOption->order_option_price); ?>
                                </small><br>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if (!empty($menu->options)) { ?>
                        <div>
                            <small><?= $menu->options; ?></small>
                        </div>
                    <?php } ?>
                    <div>
                        <small><b><?= $menu->comment; ?></b></small>
                    </div>
                </td>
                <td class="text-left"><?= currency_format($menu->price); ?></td>
                <td class="text-right"><?= currency_format($menu->subtotal); ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td class="thick-line" colspan="99999"></td>
        </tr>
        <?php foreach ($customerOrder->getOrderTotals() as $total) { ?>
            <?php if ($customerOrder->isCollectionType() AND $total->code == 'delivery') continue; ?>
            <?php $thickLine = ($total->code == 'order_total' OR $total->code == 'total'); ?>
            <tr>
                <td class="<?= $thickLine ? 'thick' : 'no' ?>-line"></td>
                <td class="<?= $thickLine ? 'thick' : 'no' ?>-line"></td>
                <td class="text-right <?= $thickLine ? 'thick' : 'no' ?>-line">
                    <?= $total->title; ?>
                </td>
                <td class="text-right <?= $thickLine ? 'thick' : 'no' ?>-line">
                    <?= currency_format($total->value); ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<div class="buttons p-3">
    <a
        class="btn btn-primary"
        data-request="<?= $__SELF__.'::onReOrder'; ?>"
        data-request-data="orderId: <?= $customerOrder->order_id; ?>"
    >
        <i class="fa fa-mail-reply"></i>
        <?= lang('igniter.cart::default.orders.button_reorder'); ?>
    </a>
    <?php if ($showReviews) { ?>
        <td>
            <a
                class="btn btn-warning leave-review"
                href="<?= site_url($addReviewsPage, [
                    'saleType' => 'order',
                    'saleId' => $customerOrder->order_id,
                ]); ?>"
            >
                <i class="fa fa-heart"></i>
                <?= lang('igniter.cart::default.orders.text_leave_review'); ?>
            </a>
        </td>
    <?php } ?>
</div>
