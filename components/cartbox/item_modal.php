<div class="modal-dialog"
     data-control="cart-item"
>
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?= $cartItem
                    ? lang('sampoyigi.cart::default.text_update_heading')
                    : lang('sampoyigi.cart::default.text_add_heading'); ?></h4>
        </div>

        <div id="menu-options"
             class="modal-body"
             data-control="item-options"
        >
            <form method="POST" data-request="<?= $formHandler; ?>">
                <input type="hidden" name="menuId" value="<?= $menuItem->getBuyableIdentifier(); ?>"/>
                <input type="hidden" name="rowId" value="<?= $cartItem ? $cartItem->rowId : null; ?>"/>

                <div class="media">
                    <div class="media-left">
                        <a href="#">
                            <img class="media-object" src="<?= $menuItem->menu_image; ?>">
                        </a>
                    </div>
                    <div class="media-body">
                        <h3 class="media-heading">
                            <?= $menuItem->getBuyableName(); ?>
                            <span class="pull-right"><?= currency_format($menuItem->getBuyablePrice()); ?></span>
                        </h3>
                        <?php if (strlen($menuItem->menu_description)) { ?>
                            <p><?= $menuItem->menu_description; ?></p>
                        <?php } ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="quantity"><?= lang('sampoyigi.cart::default.label_menu_quantity'); ?></label>
                    <div
                        class="input-group"
                        data-cart-toggle="quantity"
                        data-min-quantity="<?= $menuItem->minimum_qty; ?>"
                    >
                            <span class="input-group-btn">
                                <button
                                    class="btn btn-default"
                                    data-operator="minus"
                                    type="button"
                                ><i class="fa fa-minus"></i></button>
                            </span>
                        <input
                            type="number"
                            name="quantity"
                            class="form-control text-center"
                            value="<?= $cartItem ? $cartItem->qty : $menuItem->minimum_qty; ?>">
                        <span class="input-group-btn">
                            <button
                                class="btn btn-default"
                                data-operator="plus"
                                type="button"
                            ><i class="fa fa-plus"></i></button>
                        </span>
                    </div>
                </div>

                <div class="menu-options">
                    <div class="menu-options">
                        <?php foreach ($menuItem->menu_options as $index => $menuOption) { ?>
                            <input
                                type="hidden"
                                name="menu_options[<?= $index; ?>][menu_option_id]"
                                value="<?= $menuOption->menu_option_id; ?>"
                            />

                            <?= partial('@item_option_'.$menuOption->display_type, [
                                'index' => $index,
                                'cartItem' => $cartItem,
                                'menuOption' => $menuOption
                            ]); ?>
                        <?php } ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comment"><?= lang('sampoyigi.cart::default.label_add_comment'); ?></label>
                    <textarea
                        name="comment"
                        class="form-control"
                        rows="3"
                    ><?= $cartItem ? $cartItem->comment : null; ?></textarea>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <button
                            type="submit"
                            class="btn btn-success btn-block"
                        ><?= $cartItem
                                ? lang('sampoyigi.cart::default.button_update')
                                : lang('sampoyigi.cart::default.button_add_to_order');
                            ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>