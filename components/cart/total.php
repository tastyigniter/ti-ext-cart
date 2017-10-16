<div class="cart-total">
    <div class="table-responsive">
        <table class="table table-none">
            <tbody>
            <?php foreach ($cartTotals as $name => $total) { ?>
                <!--                --><?php //if (!is_array($total) OR !count($total)) continue; ?>
                <tr
                    data-cart-total-code="<?= $total->code; ?>"
                >
                    <td>
                        <span class="text-muted">
                            <?php if ($name == 'order_total') { ?>
                                <b><?= $total->label; ?>:</b>
                            <?php }
                            else if ($name == 'coupon') { ?>
                                <?= $total->label; ?>:&nbsp;&nbsp;
                                <a data-control="remove-cart-total"><span class="fa fa-times"></span></a>
                            <?php }
                            else { ?>
                                <?= $total->label; ?>:
                            <?php } ?>
                        </span>
                    </td>
                    <td class="text-right">
                        <?php
                        $totalAmount = isset($total->valueFrom) ? $cart->{$total->valueFrom} : $cart->{$total->code};
                        ?>
                        <?php if ($name == 'order_total') { ?>
                            <b><span class="order-total"><?= currency_format($totalAmount); ?></span></b>
                        <?php }
                        else { ?>
                            <?= currency_format($totalAmount); ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
