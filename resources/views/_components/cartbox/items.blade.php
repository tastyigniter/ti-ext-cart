@if ($cart->count())
    <div class="cart-items">
        <ul>
            @foreach ($cart->content()->reverse() as $cartItem)
                <li>
                    <button
                        type="button"
                        class="cart-btn btn btn-light btn-sm text-muted"
                        data-request="{{ $removeCartItemEventHandler }}"
                        data-replace-loading="fa fa-spinner fa-spin"
                        data-request-data="rowId: '{{ $cartItem->rowId }}', menuId: '{{ $cartItem->id }}'"
                    ><i class="fa fa-minus"></i></button>

                    <span class="price pull-right">
                        @if ($cartItem->hasConditions())
                            <s class="text-muted">{{currency_format($cartItem->subtotalWithoutConditions())}}</s>/
                        @endif
                        {{ currency_format($cartItem->subtotal) }}
                    </span>
                    <a
                        class="text-reset name-image"
                        data-cart-control="load-item"
                        data-row-id="{{ $cartItem->rowId }}"
                        data-menu-id="{{ $cartItem->id }}"
                    >
                        <span class="name">
                            @if ($cartItem->qty > 1)
                                <span class="quantity fw-bold">
                                    {{ $cartItem->qty }} @lang('igniter.cart::default.text_times')
                                </span>
                            @endif
                            {{ $cartItem->name }}
                        </span>
                        @if ($cartItem->hasOptions())
                            @partial('@cart_item_options', ['itemOptions' => $cartItem->options])
                        @endif
                        @if (!empty($cartItem->comment))
                            <p class="comment text-muted small">
                                {{ $cartItem->comment }}
                            </p>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@else
    <div class="panel-body text-center">@lang('igniter.cart::default.text_no_cart_items')</div>
@endif
