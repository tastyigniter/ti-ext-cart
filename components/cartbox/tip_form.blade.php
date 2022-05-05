@if ($cart->count())
    <form
        id="tip-form"
        method="POST"
        role="form"
        data-request="{{ $applyTipEventHandler }}"
    >
        <div class="cart-tip">
            <div class="overflow-auto">
                @php
                    $tipAmountType = $__SELF__->tippingSelectedType();
                    $currentAmount = $__SELF__->tippingSelectedAmount();
                @endphp
                @if ($tipAmounts = $__SELF__->tippingAmounts())
                    <div class="btn-group btn-group-toggle w-100 text-center tip-amounts">
                        <button
                            type="button"
                            class="btn btn-light text-nowrap{{ $tipAmountType == 'none' ? ' active' : '' }}"
                            data-cart-control="tip-amount"
                            data-tip-amount-type="none"
                        >@lang('igniter.cart::default.text_no_tip')</button>
                        @foreach ($tipAmounts as $tipAmount)
                            <button
                                type="button"
                                class="btn btn-light text-nowrap{{ $currentAmount == $tipAmount->value ? ' active' : '' }}"
                                data-cart-control="tip-amount"
                                data-tip-amount-type="amount"
                                data-tip-value="{{ $tipAmount->value }}"
                            ><strong>{{ $tipAmount->valueType != 'F' ? round($tipAmount->value).'%' : currency_format($tipAmount->value) }}</strong></button>
                        @endforeach
                        <button
                            type="button"
                            class="btn btn-light{{ $tipAmountType == 'custom' ? ' active' : '' }}"
                            data-cart-control="tip-amount"
                            data-tip-amount-type="custom"
                        >@lang('igniter.cart::default.text_edit_tip')</button>
                    </div>
                @endif
                <input type="hidden" name="amount_type" value="{{ $tipAmountType }}">
            </div>
            <div
                class="input-group{{ $tipAmounts ? ' mt-2' : '' }}"
                data-tip-custom
                {!! ($tipAmounts && $tipAmountType != 'custom') ? 'style="display: none;"' : '' !!}
            >
                <input
                    type="number"
                    name="amount"
                    class="form-control"
                    value="{{ $currentAmount }}"
                    placeholder="@lang('igniter.cart::default.text_apply_tip')"
                    step="{{ 1 / (10 ** app('currency')->getDefault()->decimal_position) }}"
                />
                <button
                    type="submit"
                    class="btn btn-light"
                    data-replace-loading="fa fa-spinner fa-spin"
                    title="@lang('igniter.cart::default.button_apply_tip')"
                ><i class="fa fa-check"></i></button>
            </div>
        </div>
    </form>
@endif
