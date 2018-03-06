<?php if ($cartItemsCount) { ?>
    <div class="cart-items">
        <ul>
            <?php foreach ($cartContent->reverse() as $cartItem) { ?>
                <li>
                    <a
                        class="cart-btn text-muted small"
                        data-cart-control="remove-item"
                        data-row-id="<?= $cartItem->rowId; ?>"
                        data-menu-id="<?= $cartItem->id; ?>"
                    ><i class="fa fa-minus-circle"></i></a>

                    <a
                        class="name-image"
                        data-cart-control="load-item"
                        data-row-id="<?= $cartItem->rowId; ?>"
                        data-menu-id="<?= $cartItem->id; ?>"
                    >
                        <span class="name">
                            <span class="quantity">
                                <?= $cartItem->qty.' '.lang('sampoyigi.cart::default.text_times'); ?>
                            </span>
                            <?= $cartItem->name; ?>
                        </span>
                        <?php if ($cartItem->hasOptions()) { ?>
                            <?= partial('@cart_item_options', [
                                'itemOptions' => $cartItem->options,
                            ]); ?>
                        <?php } ?>
                    </a>
                    <p class="comment-amount">
                        <span class="amount pull-right"><?= currency_format($cartItem->subtotal); ?></span>
                        <?php if (!empty($cartItem->comment)) { ?>
                            <span class="comment text-muted small">[<?= $cartItem->comment; ?>]</span>
                        <?php } ?>
                    </p>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php }
else { ?>
    <div class="panel-body"><?= lang('sampoyigi.cart::default.text_no_cart_items'); ?></div>
<?php } ?>
