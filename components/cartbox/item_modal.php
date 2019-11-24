<div
    class="modal-dialog "
    data-control="cart-item"
    data-min-quantity="<?= $menuItem->minimum_qty; ?>"
>
    <form method="POST" data-request="<?= $formHandler; ?>">
        <div class="modal-content">
            <?php if ($showCartItemThumb AND $menuItem->hasMedia('thumb')) { ?>
                <div class="modal-top">
                    <img class="img-fluid" src="<?= $menuItem->thumb->getThumb([
                        'width' => $cartItemThumbWidth,
                        'height' => $cartItemThumbHeight,
                    ]); ?>">
                </div>
            <?php } ?>

            <div class="modal-body">
                <button
                    type="button"
                    class="close px-2"
                    data-dismiss="modal"
                ><span aria-hidden="true">&times;</span></button>
                <h4><?= e($menuItem->getBuyableName()); ?></h4>
                <?php if (strlen($menuItem->menu_description)) { ?>
                    <p class="text-muted"><?= nl2br($menuItem->menu_description); ?></p>
                <?php } ?>

                <input type="hidden" name="menuId" value="<?= $menuItem->getBuyableIdentifier(); ?>"/>
                <input type="hidden" name="rowId" value="<?= $cartItem ? $cartItem->rowId : null; ?>"/>

                <div
                    id="menu-options"
                    class="menu-options"
                    data-control="item-options"
                >
                    <?= partial('@item_options'); ?>
                </div>

                <div class="menu-comment">
                <textarea
                    name="comment"
                    class="form-control"
                    rows="2"
                    placeholder="<?= lang('igniter.cart::default.label_add_comment'); ?>"
                ><?= $cartItem ? $cartItem->comment : null; ?></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <div class="row no-gutters w-100">
                    <div class="col-sm-5 pb-3 pb-sm-0">
                        <div class="input-group" data-cart-toggle="quantity">
                            <div class="input-group-prepend">
                                <button
                                    class="btn btn-light"
                                    data-operator="minus"
                                    type="button"
                                ><i class="fa fa-minus"></i></button>
                            </div>
                            <input
                                type="number"
                                name="quantity"
                                class="form-control text-center"
                                value="<?= $cartItem ? $cartItem->qty : $menuItem->minimum_qty; ?>"
                            >
                            <div class="input-group-append">
                                <button
                                    class="btn btn-light"
                                    data-operator="plus"
                                    type="button"
                                ><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-7 pl-sm-2">
                        <button type="submit" class="btn btn-primary btn-block" data-attach-loading>
                            <?= $cartItem
                                ? lang('igniter.cart::default.button_update')
                                : lang('igniter.cart::default.button_add_to_order');
                            ?>
                            <span class="pull-right">
                                <?= currency_format($cartItem
                                    ? $cartItem->subtotal
                                    : $menuItem->getBuyablePrice());
                                ?>
                            </span>&nbsp;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>