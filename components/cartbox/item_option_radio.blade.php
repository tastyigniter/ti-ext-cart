@foreach ($optionValues as $optionValue)
    <div class="custom-control custom-radio">
        <input
            type="radio"
            id="menuOptionRadio{{ $menuOptionValueId = $optionValue->menu_option_value_id }}"
            class="custom-control-input"
            name="menu_options[{{ $index }}][option_values][]"
            value="{{ $optionValue->menu_option_value_id }}"
            data-option-price="{{ $optionValue->price }}"
            @if (($cartItem && $cartItem->hasOptionValue($menuOptionValueId)) || $optionValue->isDefault())
            checked="checked"
            @endif
        >
        <label
            class="custom-control-label w-100"
            for="menuOptionRadio{{ $menuOptionValueId }}"
        >
            {{ $optionValue->name }}
            @if ($optionValue->price > 0 || !$hideZeroOptionPrices)
                <span class="pull-right">@lang('main::lang.text_plus'){{ currency_format($optionValue->price) }}</span>
            @endif
        </label>
    </div>
@endforeach
