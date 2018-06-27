<?php
$fullyClosed = FALSE;
$unAvailable = (!$canAcceptOrder OR ($orderType == 'delivery' AND !$cartTotalIsAboveMinTotal));
if ($isClosed) {
    $buttonLang = 'sampoyigi.cart::default.text_is_closed';
    $fullyClosed = TRUE;
}
else if (!$pageIsCheckout) {
    $buttonLang = 'sampoyigi.cart::default.button_order';
}
else {
    $buttonLang = 'sampoyigi.cart::default.button_confirm';
}

if ($unAvailable) {
    $fullyClosed = TRUE;
}
?>
<button
    class="btn btn-primary <?= ($fullyClosed) ? 'disabled' : ''; ?> btn-block btn-lg"
    <?php if ($pageIsCheckout) { ?>
        data-cart-control="confirm-checkout"
        data-request-form="#checkout-form"
    <?php } else if (!$fullyClosed) { ?>
        data-request="<?= $checkoutEventHandler; ?>"
        data-request-data="locationId: '<?= $currentLocation->getKey() ?>'"
    <?php } ?>
><?= lang($buttonLang); ?></button>
