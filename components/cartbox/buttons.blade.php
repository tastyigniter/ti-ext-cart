@php $locationIsClosed = ($__SELF__->locationIsClosed() || $__SELF__->hasMinimumOrder()); @endphp
<button
    class="checkout-btn btn btn-primary {{ $locationIsClosed ? 'disabled' : '' }} btn-block btn-lg"
    data-attach-loading="disabled"
    @if ($pageIsCheckout && !$locationIsClosed)
    data-checkout-control="confirm-checkout"
    data-request-form="#checkout-form"
    @elseif (!$locationIsClosed)
    data-request="{{ $checkoutEventHandler }}"
    data-request-data="locationId: '{{ $__SELF__->getLocationId() }}'"
    @endif
>{{ $__SELF__->buttonLabel($checkout ?? null) }}</button>
