<?php if ($hasDelivery OR $hasCollection) { ?>
    <div class="location-control text-center text-muted">
        <div class="btn-group btn-group-md text-center order-type" data-toggle="buttons">
            <?php if ($hasDelivery) { ?>
                <label
                    class="btn <?= ($orderType == 'delivery') ? 'btn-default btn-primary active' : 'btn-default'; ?>"
                    data-btn="btn-primary">
                    <input
                        type="radio"
                        name="order_type"
                        data-cart-toggle="order-type"
                        value="delivery" <?= ($orderType == 'delivery') ? 'checked="checked"' : ''; ?>
                    >&nbsp;&nbsp;
                    <strong><?= lang('sampoyigi.cart::default.text_delivery'); ?></strong>
                    <span
                        class="small center-block">
                        <?php if ($deliverySchedule->isOpen()) { ?>
                            <?= sprintf(lang('sampoyigi.cart::default.text_in_minutes'), $deliveryMinutes); ?>
                        <?php }
                        else if ($deliverySchedule->isOpening()) { ?>
                            <?= sprintf(lang('sampoyigi.cart::default.text_starts'), $deliverySchedule->getOpenTime($cartBoxTimeFormat)); ?>
                        <?php }
                        else { ?>
                            <?= lang('sampoyigi.cart::default.text_is_closed'); ?>
                        <?php } ?>
                    </span>
                </label>
            <?php } ?>
            <?php if ($hasCollection) { ?>
                <label
                    class="btn <?= ($orderType == 'collection') ? 'btn-default btn-primary active' : 'btn-default'; ?>"
                    data-btn="btn-primary">
                    <input
                        type="radio"
                        name="order_type"
                        data-cart-toggle="order-type"
                        value="collection" <?= ($orderType == 'collection') ? 'checked="checked"' : ''; ?>
                    >&nbsp;&nbsp;
                    <strong><?= lang('sampoyigi.cart::default.text_collection'); ?></strong>
                    <span
                        class="small center-block">
                        <?php if ($collectionSchedule->isOpen()) { ?>
                            <?= sprintf(lang('sampoyigi.cart::default.text_in_minutes'), $collectionMinutes); ?>
                        <?php }
                        else if ($collectionSchedule->isOpening()) { ?>
                            <?= sprintf(lang('sampoyigi.cart::default.text_starts'), $collectionSchedule->getOpenTime($cartBoxTimeFormat)); ?>
                        <?php }
                        else { ?>
                            <?= lang('sampoyigi.cart::default.text_is_closed'); ?>
                        <?php } ?>
                    </span>
                </label>
            <?php } ?>
        </div>
    </div>
    <?php if ($orderType == 'delivery') { ?>
        <p class="text-muted text-center">
            <?= $minOrderTotal
                ? lang('sampoyigi.cart::default.text_min_total').':'.currency_format($minOrderTotal)
                : lang('sampoyigi.cart::default.text_no_min_total');
            ?>
        </p>
    <?php } ?>
<?php } ?>
