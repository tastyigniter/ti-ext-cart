<div
    data-control="checkout"
    data-choose-payment-handler="{{ $choosePaymentEventHandler }}"
    data-delete-payment-handler="{{ $deletePaymentEventHandler }}"
    data-partial="checkoutForm"
>
    @partial('@form')
</div>