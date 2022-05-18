<div
    id="cart-mobile-buttons"
    class="{{ !$pageIsCheckout ? 'fixed-bottom' : 'mt-3' }}{{ $pageIsCart ? 'hide' : ' d-block d-lg-none' }}"
>
    @if ($pageIsCheckout)
        @partial('@buttons')
    @elseif (!$pageIsCart)
        <a
            class="btn btn-primary btn-block btn-lg radius-none cart-toggle text-nowrap"
            href="{{ site_url('cart') }}"
        >
            @lang('igniter.cart::default.text_heading'):
            <span data-cart-total class="fw-bold">{{ currency_format($cart->total()) }}</span>
        </a>
    @endif
</div>
