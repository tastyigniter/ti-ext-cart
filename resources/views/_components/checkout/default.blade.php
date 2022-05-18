<div
    data-control="checkout"
    data-choose-payment-handler="{{ $choosePaymentEventHandler }}"
    data-delete-payment-handler="{{ $deletePaymentEventHandler }}"
    data-validate-handler="{{ $validateCheckoutEventHandler }}"
    data-partial="checkoutForm"
>
    @partial($isMultiStepCheckout ? '@multi_step_form' : '@form')
</div>
