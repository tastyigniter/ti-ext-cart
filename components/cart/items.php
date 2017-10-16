<div class="cart-items">
    <ul>
        <?php foreach ($cartItems as $cartItem) { ?>
            <li
                data-item-rowid="<?= $cartItem->rowid; ?>"
                data-item-menu_id="<?= $cartItem->menu_id; ?>"
            >
                <a
                    class="cart-btn text-muted small"
                    data-cart-control="remove-item"
                ><i class="fa fa-minus-circle"></i></a>

                <a
                    class="name-image"
                    data-cart-control="update-item"
                >
                    <?php if ($showCartImage AND strlen($cartItem->image) > 0) { ?>
                        <img
                            class="image img-responsive img-thumbnail"
                            width="<?= $cartImageWidth; ?>"
                            height="<?= $cartImageHeight; ?>"
                            alt="<?= $cartItem->name; ?>"
                            src="<?= $cartItem->image; ?>"
                        >
                    <?php } ?>
                    <span class="name">
                        <span class="quantity"><?= $cartItem->qty.lang('text_times'); ?></span>
                        <?= $cartItem->name; ?>
                    </span>
                    <?php if (strlen($cartItem->options) > 0) { ?>
                        <span class="options text-muted small"><?= $cartItem->options; ?></span>
                    <?php } ?>
                </a>
                <p class="comment-amount">
                    <span class="amount pull-right"><?= $cartItem->sub_total; ?></span>
                    <?php if (!empty($cartItem->comment)) { ?>
                        <span class="comment text-muted small">[<?= $cartItem->comment; ?>]</span>
                    <?php } ?>
                </p>
            </li>
        <?php } ?>
    </ul>
</div>
