<?php
$fullyClosed = ($isClosed OR !$canAcceptOrder);
if ($fullyClosed)
    $buttonLang = 'sampoyigi.cart::default.text_is_closed';
else if (!$pageIsCheckout)
    $buttonLang = 'sampoyigi.cart::default.button_order';
else
    $buttonLang = 'sampoyigi.cart::default.button_confirm';
?>
<div class="center-block">
    <button
        class="btn <?= $fullyClosed ? 'btn-default' : 'btn-primary'; ?> btn-block btn-lg"
        <?php if ($pageIsCheckout) { ?>
            data-cart-control="confirm-checkout"
            data-request-form="#checkout-form"
        <?php } else if (!$fullyClosed) { ?>
            data-request="<?= $checkoutEventHandler; ?>"
            data-request-data="locationId: '<?= $currentLocation->getKey() ?>'"
        <?php } ?>
    >
        <?= lang($buttonLang); ?>
    </button>
</div>
