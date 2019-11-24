<?php
$fullyClosed = FALSE;
$cartTotalIsAboveMinTotal = $location->checkMinimumOrder($cart->subtotal());
$unAvailable = (!$location->checkOrderType() OR ($location->orderTypeIsDelivery() AND !$cartTotalIsAboveMinTotal));
$workingSchedule = $location->workingSchedule($location->orderType());
if ($workingSchedule->isClosed() AND !$location->current()->hasFutureOrder()) {
    $buttonLang = 'igniter.cart::default.text_is_closed';
    $fullyClosed = TRUE;
}
else if (!$pageIsCheckout) {
    $buttonLang = 'igniter.cart::default.button_order';
}
else {
    $buttonLang = 'igniter.cart::default.button_confirm';
}

if ($unAvailable) {
    $fullyClosed = TRUE;
}
?>
<button
    class="checkout-btn btn btn-primary <?= ($fullyClosed) ? 'disabled' : ''; ?> btn-block btn-lg"
    <?php if ($pageIsCheckout) { ?>
        data-cart-control="confirm-checkout"
        data-request-form="#checkout-form"
    <?php } else if (!$fullyClosed) { ?>
        data-request="<?= $checkoutEventHandler; ?>"
        data-request-data="locationId: '<?= $location->getId() ?>'"
    <?php } ?>
><?= lang($buttonLang); ?></button>
