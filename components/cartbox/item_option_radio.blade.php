@php $i = 0 @endphp

@foreach ($optionValues as $optionValue)
    <div @class(['form-check', 'py-2' => !$loop->first || !$loop->last])>
        <input
            type="radio"
            id="menuOptionRadio{{ $menuOptionValueId = $optionValue->menu_option_value_id }}"
            class="form-check-input"
            name="menu_options[{{ $index }}][option_values][]"
            value="{{ $optionValue->menu_option_value_id }}"
            data-option-price="{{ $optionValue->price }}"
            @if (($cartItem && $cartItem->hasOptionValue($menuOptionValueId)) || $optionValue->isDefault())
            checked="checked"
            @endif
        >

        <label
            class="form-check-label ps-2 w-100"
            for="menuOptionRadio{{ $menuOptionValueId }}"
        >
            {{ $optionValue->name }}
            @if ($optionValue->price > 0 || !$hideZeroOptionPrices)
                <span class="float-end fw-light">@lang('main::lang.text_plus'){{ currency_format($optionValue->price) }}</span>
            @endif
        </label>
    </div>
    @if ($i == 2 && count($optionValues) > 3)
        <div class="show-more-options-radio py-2" style="display: none;">
    @endif

    @php $i++ @endphp
@endforeach

@if (count($optionValues) > 3)
    </div>
    <a href="#" class="show-more-link-radio">@lang('igniter.cart::default.button_show_more')</a>
    <script>
        $(document).ready(function() {
            $('.show-more-link-radio').click(function(e) {
                e.preventDefault();
                $('.show-more-options-radio').show();
                $('.show-more-link-radio').hide();
            });
        });
    </script>
@endif
