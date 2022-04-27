@if ($paymentGateways)
    <div class="row payments">
        <div class="col-sm-8">
            <div class="form-group">
                <label for="">@lang('igniter.cart::default.checkout.label_payment_method')</label><br/>
                <input type="hidden" name="payment" value=""/>
                <div
                    data-toggle="payments"
                    class="progress-indicator-container"
                >
                    <div class="list-group">
                        @foreach ($paymentGateways as $paymentGateway)
                            @php
                                $paymentIsSelected = ($order->payment == $paymentGateway->code);
                                $paymentIsNotApplicable = !$paymentGateway->isApplicable($order->order_total, $paymentGateway);
                            @endphp
                            <div
                                class="list-group-item{{ $paymentIsSelected ? ' bg-light' : '' }}{{ $paymentIsNotApplicable ? ' disabled' : '' }}"
                            >
                                <div
                                    class="form-check"
                                    data-checkout-control="choose-payment"
                                    data-payment-code="{{ $paymentGateway->code }}"
                                >
                                    <input
                                        type="radio"
                                        id="payment-{{ $paymentGateway->code }}"
                                        class="form-check-input"
                                        name="payment"
                                        value="{{ $paymentGateway->code }}"
                                        {!! $paymentIsSelected ? 'checked="checked"' : '' !!}
                                        {!! $paymentIsNotApplicable ? 'disabled="disabled"' : '' !!}
                                        data-pre-validate-checkout="{{ $paymentGateway->completesPaymentOnClient() ? 'true' : 'false' }}"
                                        autocomplete="off"
                                    />
                                    <label
                                        class="form-check-label d-block"
                                        for="payment-{{ $paymentGateway->code }}"
                                    >{{ $paymentGateway->name }}</label>
                                    @if (strlen($paymentGateway->description))
                                        <p class="hide small fw-normal mb-0">
                                            {!! $paymentGateway->description !!}
                                        </p>
                                    @endif
                                    @if ($paymentIsNotApplicable)
                                        <p class="small fw-normal mb-0">
                                            <em>
                                                {!! sprintf(
                                                    lang('igniter.payregister::default.alert_min_order_total'),
                                                    currency_format($paymentGateway->order_total),
                                                    lang('igniter.payregister::default.text_this_payment')
                                                ) !!}
                                            </em>
                                        </p>
                                    @endif
                                    @if ($paymentGateway->hasApplicableFee())
                                        <p class="small fw-normal mb-0">
                                            <em>
                                                {!! sprintf(
                                                    lang('igniter.payregister::default.alert_order_fee'),
                                                    $paymentGateway->getFormattedApplicableFee(),
                                                    lang('igniter.payregister::default.text_this_payment')
                                                ) !!}
                                            </em>
                                        </p>
                                    @endif
                                </div>
                                @if ($paymentIsSelected)
                                    {!! $paymentGateway->renderPaymentForm($this->controller) !!}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                {!! form_error('payment', '<span class="text-danger">', '</span>') !!}
            </div>
        </div>
    </div>
@endif
