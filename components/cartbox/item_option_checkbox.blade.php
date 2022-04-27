@foreach ($optionValues as $optionValue)
    <div @class(['form-check', 'py-2' => !$loop->first || !$loop->last])>
        <input
            type="checkbox"
            class="form-check-input"
            id="menuOptionCheck{{ $menuOptionValueId = $optionValue->menu_option_value_id }}"
            name="menu_options[{{ $index }}][option_values][]"
            value="{{ $optionValue->menu_option_value_id }}"
            data-option-price="{{ $optionValue->price }}"
            @if (($cartItem && $cartItem->hasOptionValue($menuOptionValueId)) || $optionValue->isDefault())
            checked="checked"
            @endif
        >

        <label
            class="form-check-label ps-2 w-100"
            for="menuOptionCheck{{ $menuOptionValueId }}"
        >
            {!! $optionValue->name !!}
            @if ($optionValue->price > 0 || !$hideZeroOptionPrices)
                <span class="float-end fw-light">@lang('main::lang.text_plus'){{ currency_format($optionValue->price) }}</span>
            @endif
        </label>
    </div>
@endforeach
