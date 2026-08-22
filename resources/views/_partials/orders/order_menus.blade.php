@php
    // Keep the original OrderMenu models available as a fallback. This makes
    // sure product comments remain available if getOrderMenusWithOptions()
    // returns a transformed item that does not expose every raw attribute.
    $rawOrderMenus = $model->getOrderMenus()->keyBy('order_menu_id');
@endphp

<div class="table-responsive">
    <table class="table mb-0">
        <thead>
        <tr>
            <th width="65%" class="border-top-0">@lang('igniter.cart::default.orders.column_name_option')</th>
            <th class="text-center border-top-0">@lang('igniter.cart::default.orders.column_quantity')</th>
            <th class="text-left border-top-0">@lang('igniter.cart::default.orders.column_price')</th>
            <th class="text-right border-top-0">@lang('igniter.cart::default.orders.column_total')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($model->getOrderMenusWithOptions() as $menuItem)
            @php
                $rawMenuItem = $rawOrderMenus->get($menuItem->order_menu_id);
                $itemComment = trim((string)($menuItem->comment ?? $rawMenuItem?->comment ?? ''));
            @endphp
            <tr>
                <td><b>{{ $menuItem->name }}</b>
                    @if($menuItem->menu_options->isNotEmpty())
                        <ul class="list-unstyled mb-0 mt-2">
                            @foreach($menuItem->menu_options as $menuItemOptionGroupName => $menuItemOptions)
                                <li>
                                    <u class="text-muted">{{ $menuItemOptionGroupName }}:</u>
                                    <ul class="list-unstyled">
                                        @foreach($menuItemOptions as $menuItemOption)
                                            <li>
                                                @if($menuItemOption->quantity > 1)
                                                    {{ $menuItemOption->quantity }}x
                                                @endif
                                                {{ $menuItemOption->order_option_name }}&nbsp;
                                                @if($menuItemOption->order_option_price > 0)
                                                    ({{ currency_format($menuItemOption->quantity * $menuItemOption->order_option_price) }}
                                                    )
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if($itemComment !== '')
                        <p class="font-weight-bold">{{ $itemComment }}</p>
                    @endif
                </td>
                <td class="text-center">{{ $menuItem->quantity }}</td>
                <td class="text-left">{{ currency_format($menuItem->price) }}</td>
                <td class="text-right"><b>{{ currency_format($menuItem->subtotal) }}</b></td>
            </tr>
        @endforeach
        <tr>
            <td class="border-top p-0" colspan="99999"></td>
        </tr>
        @foreach($model->getOrderTotals() as $total)
            @continue($model->isCollectionType() && $total->code == 'delivery')
            @php $thickLine = ($total->code == 'order_total' || $total->code == 'total') @endphp
            <tr>
                <td
                    class="border-0 text-muted text-end"
                    colspan="3"
                >
                    {{ $total->title }}
                    @if($total->code == 'subtotal')
                        <span
                            class="text-muted">({{ $formModel->total_items }} @lang('igniter.cart::default.orders.label_total_items'))</span>
                    @endif
                </td>
                <td
                    class="border-0 font-weight-bold text-right"
                >{{ currency_format($total->value) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
