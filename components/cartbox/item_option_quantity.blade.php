@foreach ($optionValues as $optionIndex => $optionValue)
    <div
        class="custom-control custom-quantity"
    >
        <label
            class="custom-quantity-label w-100"
            for="menuOptionQuantity{{ $menuOptionValueId = $optionValue->menu_option_value_id }}"
        >
            {{ $optionValue->name }}
            @if ($optionValue->price > 0 || !$hideZeroOptionPrices)
                <span class="pull-right">@lang('main::lang.text_plus'){{ currency_format($optionValue->price) }}</span>
            @endif
            <input
                type="hidden"
                name="menu_options[{{ $index }}][option_values][{{ $optionIndex }}][id]"
                value="{{ $optionValue->menu_option_value_id }}"
            />
            <input
                type="number"
                class="form-control custom-quantity-input"
                id="menuOptionQuantity{{ $menuOptionValueId }}"
                name="menu_options[{{ $index }}][option_values][{{ $optionIndex }}][qty]"
                value="{{ $__SELF__->getOptionQuantityTypeValue($cartItem, $optionValue) }}"
                data-option-price="{{ $optionValue->price }}"
                inputmode="numeric"
                pattern="[0-9]*"
                min="0"
                autocomplete="off"
            />
        </label>
    </div>
@endforeach
