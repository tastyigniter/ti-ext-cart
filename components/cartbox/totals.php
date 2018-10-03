<?php if ($cart->count()) { ?>
    <div class="cart-total">
        <div class="table-responsive">
            <table class="table table-none">
                <tbody>

                <tr>
                    <td>
                    <span class="text-muted">
                        <?= lang('igniter.cart::default.text_sub_total'); ?>:
                   </span>
                    </td>
                    <td class="text-right">
                        <?= currency_format($cart->subtotal()); ?>
                    </td>
                </tr>

                <?php foreach ($cart->conditions() as $id => $condition) { ?>
                    <tr>
                        <td>
                        <span class="text-muted">
                            <?= e($condition->getLabel()); ?>:
                            <?php if ($condition->removeable) { ?>
                                <button
                                    type="button"
                                    class="btn btn-light btn-sm"
                                    data-cart-condition-id="<?= $id; ?>"
                                    data-cart-control="remove-condition"
                                ><i class="fa fa-times"></i></button>
                            <?php } ?>
                       </span>
                        </td>
                        <td class="text-right">
                            <?= is_numeric($result = $condition->getValue())
                                ? currency_format($result)
                                : '--'; ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
                    <td>
                    <span class="text-muted">
                        <?= lang('igniter.cart::default.text_order_total'); ?>:
                   </span>
                    </td>
                    <td class="text-right">
                        <?= currency_format($cart->total()); ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
