<?php if ($cartItemsCount) { ?>
    <div class="cart-total">
        <div class="table-responsive">
            <table class="table table-none">
                <tbody>

                <tr>
                    <td>
                    <span class="text-muted">
                        <?= lang('sampoyigi.cart::default.text_sub_total'); ?>:
                   </span>
                    </td>
                    <td class="text-right">
                        <?= currency_format($subTotal = $cartSubtotal); ?>
                    </td>
                </tr>

                <?php foreach ($cartConditions as $id => $condition) { ?>
                    <tr>
                        <td>
                        <span class="text-muted">
                            <?= e($condition->label); ?>:
                            <?php if ($condition->removeable) { ?>
                                <a
                                    data-cart-condition-id="<?= $id; ?>"
                                    data-cart-control="remove-condition"
                                >
                                    <span class="fa fa-times"></span>
                                </a>
                            <?php } ?>
                       </span>
                        </td>
                        <td class="text-right">
                            <?= ($result = $condition->result())
                                ? currency_format($result)
                                : lang('main::default.text_free'); ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
                    <td>
                    <span class="text-muted">
                        <?= lang('sampoyigi.cart::default.text_order_total'); ?>:
                   </span>
                    </td>
                    <td class="text-right">
                        <?= currency_format($cartTotal); ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
