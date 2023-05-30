<div class="label label-secondary mb-3">
    <span class="h6">
        <i class="fa fa-clock"></i>&nbsp;
        {{ $order->order_datetime->isoFormat($orderDateTimeFormat) }}
    </span>
</div>
<h5>@lang('igniter.cart::default.checkout.text_order_no'){{$order->order_id}}</h5>
@if ($order->status)
    <div class="row justify-content-center">
        <div class="col-sm-6 py-3">
            <div class="row">
                @foreach ($__SELF__->getStatusWidthForProgressBars() as $group => $width)
                    <div class="col-4">
                        <div class="progress" style="height: 8px">
                            <div
                                class="progress-bar progress-bar-striped"
                                role="progressbar"
                                data-status-group="{{ $group }}"
                                data-status-width="{{ $width }}"
                                style="width: {{ $width }}%;"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <h3 style="color: {{ $order->status->status_color }};">{{ $order->status->status_name }}</h3>
    <p class="lead">{!! $order->status->status_comment !!}</p>
@else
    <h3>--</h3>
@endif

<p class="mb-0">@lang('igniter.cart::default.checkout.text_success_message')</p>

<div class="mt-3">
    @if (!$hideReorderBtn)
        <button
            class="btn btn-primary re-order"
            data-request="{{ $__SELF__.'::onReOrder' }}"
            data-request-data="orderId: {{ $order->order_id }}"
            data-attach-loading
        >@lang('igniter.cart::default.orders.button_reorder')</button>
    @endif
    @if (!$__SELF__->showCancelButton())
        @partial($__SELF__.'::cancel_modal')
    @endif
</div>
