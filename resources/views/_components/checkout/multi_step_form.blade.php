{!! form_open([
    'id' => 'checkout-form',
    'role' => 'form',
    'method' => 'POST',
    'data-handler' => $confirmCheckoutEventHandler,
]) !!}

@if ($__SELF__->checkoutStep !== 'pay')
    @partial('@customer_fields')

    @if ($order->isDeliveryType())
        @partial('@address_fields')
    @endif

    <div class="form-group">
        <label for="comment">@lang('igniter.cart::default.checkout.label_comment')</label>
        <textarea
            name="comment"
            id="comment"
            rows="3"
            class="form-control"
        >{!! set_value('comment', $order->comment) !!}</textarea>
    </div>
@else
    <div data-partial="checkoutPayments">
        @partial('@payments')
    </div>

    @if ($agreeTermsSlug)
        <div class="form-group">
            <div class="form-check">
                <input
                    id="terms-condition"
                    type="checkbox"
                    name="terms_condition"
                    value="1"
                    class="form-check-input" {!! set_checkbox('terms_condition', '1') !!}
                >
                <label class="form-check-label" for="terms-condition">
                    {!! sprintf(lang('igniter.cart::default.checkout.label_terms'), url($agreeTermsSlug)) !!}
                </label>
            </div>
            {!! form_error('terms_condition', '<span class="text-danger col-xs-12">', '</span>') !!}
        </div>
    @endif

    <div class="form-group">
        <label for="">@lang('igniter.cart::default.checkout.label_ip')</label>
        {{ $order->ip_address }}<br/>
        <small>@lang('igniter.cart::default.checkout.text_ip_warning')</small>
    </div>
@endif

{!! form_close() !!}
