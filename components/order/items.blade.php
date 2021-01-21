<h2 class="h4 mb-0 font-weight-normal">@lang('igniter.cart::default.checkout.text_order_details')</h2>

<div class="cart-items pt-2">
    <ul>
        @foreach ($order->getOrderMenusWithOptions() as $orderItem)
            <li>
                <span class="price pull-right">{{ currency_format($orderItem->subtotal) }}</span>
                <span class="name">
                    @if ($orderItem->quantity > 1)
                        <span class="quantity font-weight-bold">
                            {{ $orderItem->quantity }} @lang('igniter.cart::default.text_times')
                        </span>
                    @endif
                    {{ $orderItem->name }}
                </span>
                @if ($itemOptions = $orderItem->menu_options)
                    <ul class="list-unstyled small text-muted">
                        @foreach ($itemOptions as $itemOption)
                            <li>
                                @if ($itemOption->quantity > 1)
                                    {{ $itemOption->quantity }} @lang('igniter.cart::default.text_times')
                                @endif
                                {{ $itemOption->order_option_name }}&nbsp;
                                @if ($itemOption->order_option_price > 0)
                                    ({{ currency_format($itemOption->quantity * $itemOption->order_option_price) }})
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if (!empty($orderItem->comment))
                    <p class="comment text-muted small">
                        {!! $orderItem->comment !!}
                    </p>
                @endif
            </li>
        @endforeach
    </ul>
</div>

<div class="cart-totals">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <tbody>
            <tr>
                <td class="border-top p-0" colspan="99999"></td>
            </tr>
            @foreach ($order->getOrderTotals() as $orderTotal)
                @continue($order->isCollectionType() AND $orderTotal->code == 'delivery')
                @php($thickLine = ($orderTotal->code == 'order_total' OR $orderTotal->code == 'total'))
                <tr>
                    <td class="px-0 {{ $thickLine ? 'border-top lead font-weight-bold' : 'text-muted border-0' }}">
                        {{ $orderTotal->title }}
                    </td>
                    <td class="text-right px-0 {{ $thickLine ? 'border-top lead font-weight-bold' : 'border-0' }}">
                        {{ currency_format($orderTotal->value) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
