<div class="modal-dialog"
     data-control="cart-item"
     data-min-quantity="<?= $menuItem->minimum_qty; ?>"
>
    <div class="modal-content">
        <form method="POST" data-request="<?= $formHandler; ?>">
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
                <h5 class="modal-title"><b><?= $menuItem->getBuyableName(); ?></b></h5>
                <?php if (strlen($menuItem->menu_description)) { ?>
                    <p class="text-muted"><?= $menuItem->menu_description; ?></p>
                <?php } ?>

                <div
                    id="menu-options"
                    data-control="item-options"
                >
                    <input type="hidden" name="menuId" value="<?= $menuItem->getBuyableIdentifier(); ?>"/>
                    <input type="hidden" name="rowId" value="<?= $cartItem ? $cartItem->rowId : null; ?>"/>

                    <div class="menu-options">
                        <div class="menu-options">
                            <?php foreach ($menuItem->menu_options->sortBy('priority') as $index => $menuOption) { ?>
                                <div class="form-group">
                                    <input
                                        type="hidden"
                                        name="menu_options[<?= $index; ?>][menu_option_id]"
                                        value="<?= $menuOption->menu_option_id; ?>"
                                    />
                                    <div class="option option-<?= $menuOption->display_type; ?>">
                                        <label class="font-weight-bold"><?= $menuOption->option_name; ?></label>

                                        <?= partial('@item_option_'.$menuOption->display_type, [
                                            'index' => $index,
                                            'cartItem' => $cartItem,
                                            'menuOption' => $menuOption,
                                        ]); ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
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
                            <div class="input-group-btn">
                                <button
                                    class="btn btn-outline-default"
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
                            <div class="input-group-btn">
                                <button
                                    class="btn btn-outline-default"
                                    data-operator="plus"
                                    type="button"
                                ><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-7 pl-sm-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <?= $cartItem
                                ? lang('igniter.cart::default.button_update')
                                : lang('igniter.cart::default.button_add_to_order');
                            ?>
                            <span class="small ml-4">
                                <?= currency_format($cartItem
                                    ? $cartItem->subtotal
                                    : $menuItem->getBuyablePrice());
                                ?>
                            </span>&nbsp;
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>