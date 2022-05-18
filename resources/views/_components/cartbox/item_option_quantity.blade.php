@foreach ($optionValues as $optionIndex => $optionValue)
    @php $menuOptionValueId = $optionValue->menu_option_value_id @endphp
    <div @class(['form-quantity d-flex align-items-center', 'py-2' => !$loop->first || !$loop->last])>
        <input
            type="hidden"
            name="menu_options[{{ $index }}][option_values][{{ $optionIndex }}][id]"
            value="{{ $optionValue->menu_option_value_id }}"
        />
        <div
            class="form-quantity-input d-flex align-items-center"
            data-toggle="quantity"
        >
            <button
                class="btn btn-outline-secondary btn-sm lh-sm p-0 border-2 rounded-circle"
                data-operator="minus"
                type="button"
            ><i class="fa fa-minus fa-fw"></i></button>
            <input
                type="text"
                class="form-control bg-transparent shadow-none border-0 text-center p-0"
                id="menuOptionQuantity{{ $menuOptionValueId }}"
                name="menu_options[{{ $index }}][option_values][{{ $optionIndex }}][qty]"
                value="{{ $__SELF__->getOptionQuantityTypeValue($cartItem, $optionValue) }}"
                data-option-price="{{ $optionValue->price }}"
                inputmode="numeric"
                pattern="[0-9]*"
                min="0"
                autocomplete="off"
                readonly
                style="max-width:40px;"
            >
            <button
                class="btn btn-outline-secondary btn-sm lh-sm p-0 border-2 rounded-circle"
                data-operator="plus"
                type="button"
            ><i class="fa fa-plus fa-fw"></i></button>
        </div>
        <label class="form-quantity-label ps-3 w-100">
            {{ $optionValue->name }}
            @if ($optionValue->price > 0 || !$hideZeroOptionPrices)
                <span class="float-end fw-light">@lang('main::lang.text_plus'){{ currency_format($optionValue->price) }}</span>
            @endif
        </label>
    </div>
@endforeach
