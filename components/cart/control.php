<div class="location-control text-center text-muted">
    <div id="my-postcode" style="display:<?= (!$hasSearchQuery) ? 'block' : 'none'; ?>">
        <div class="btn-group btn-group-md text-center order-type" data-toggle="buttons">
            <?php if ($hasDelivery) { ?>
                <label
                    class="btn <?= ($orderType == '1') ? 'btn-default btn-primary active' : 'btn-default'; ?>"
                    data-btn="btn-primary">
                    <input
                        type="radio"
                        name="order_type"
                        data-cart-toggle="order-type"
                        value="1" <?= ($orderType == '1') ? 'checked="checked"' : ''; ?>
                    >&nbsp;&nbsp;
                    <strong><?= lang('text_delivery'); ?></strong>
                    <span
                        class="small center-block">
                        <?php if ($deliveryStatus === 'open') { ?>
                            <?= sprintf(lang('cart.text_in_minutes'), $deliveryTime); ?>
                        <?php }
                        else if ($deliveryStatus === 'opening') { ?>
                            <?= sprintf(lang('cart.text_starts'), $deliveryTime); ?>
                        <?php }
                        else { ?>
                            <?= lang('cart.text_is_closed'); ?>
                        <?php } ?>
                    </span>
                </label>
            <?php } ?>
            <?php if ($hasCollection) { ?>
                <label
                    class="btn <?= ($orderType == '2') ? 'btn-default btn-primary active' : 'btn-default'; ?>"
                    data-btn="btn-primary">
                    <input
                        type="radio"
                        name="order_type"
                        data-cart-toggle="order-type"
                        value="2" <?= ($orderType == '2') ? 'checked="checked"' : ''; ?>
                    >&nbsp;&nbsp;
                    <strong><?= lang('text_collection'); ?></strong>
                    <span
                        class="small center-block">
                        <?php if ($collectionStatus === 'open') { ?>
                            <?= sprintf(lang('cart.text_in_minutes'), $collectionTime); ?>
                        <?php }
                        else if ($collectionStatus === 'opening') { ?>
                            <?= sprintf(lang('cart.text_starts'), $collectionTime); ?>
                        <?php }
                        else { ?>
                            <?= lang('cart.text_is_closed'); ?>
                        <?php } ?>
                    </span>
                </label>
            <?php } ?>
        </div>
    </div>
</div>
