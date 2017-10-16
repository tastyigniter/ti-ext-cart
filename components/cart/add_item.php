<?php
var_dump($menuItem);
?>
<div class="modal-dialog modal-menu-options">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?= $addItemTitle; ?></h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div id="cart-options-alert">
                        <?php if (!empty($cart_option_alert)) { ?>
                            <?= $cart_option_alert; ?>
                        <?php } ?>
                    </div>

                    <div class="media">
                        <div class="media-left">
                            <a href="#">
                                <img class="media-object" src="<?= $menuItem->menu_image; ?>">
                            </a>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading" id="media-heading"><?= $menuItem->menu_name; ?></h4>
                            <?php if ($menuItem->description) { ?>
                                <p class="description"><?= $menuItem->description; ?></p>
                                <p class="price"><?= $menuItem->menu_price; ?></p>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="form-group menu-quantity">
                        <label for="quantity"><?= lang('label_menu_quantity'); ?></label>
                        <div class="input-group quantity-control">
                            <span class="input-group-btn">
                                <button
                                    class="btn btn-default"
                                    data-dir="dwn"
                                    type="button"><i class="fa fa-minus"></i>
                                </button>
                            </span>
                            <input
                                type="text"
                                name="quantity"
                                id="quantity"
                                class="form-control text-center"
                                value="<?= $menuItem->quantity ? $menuItem->quantity : $menuItem->min_quantity; ?>"
                            />
                            <span class="input-group-btn">
                                <button
                                    class="btn btn-default"
                                    data-dir="up"
                                    type="button"><i class="fa fa-plus"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <?php if ($menuItem->menu_options) { ?>
                        <?= partial('@item_options'); ?>
                    <?php } ?>

                    <div class="form-group">
                        <label for="comment"><?= lang('label_add_comment'); ?></label>
                        <textarea name="comment" class="form-control" rows="3"><?= $menuItem->comment; ?></textarea>
                    </div>

                    <div class="row">
                        <a
                            class="btn btn-success btn-block"
                            data-menu-id="<?= $menuItem->menu_id; ?>"
                            data-row-id="<?= $menuItem->row_id; ?>"
                            data-control="update-cart-item"
                            <?php if ($menuItem->row_id) { ?>
                            title="<?= lang('text_update'); ?>"><?= lang('button_update'); ?>
                            <?php }
                            else { ?>
                                title="<?= lang('text_add_to_order'); ?>"><?= lang('button_add_to_order'); ?>
                            <?php } ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
