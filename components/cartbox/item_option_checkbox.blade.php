@php $i = 0 @endphp

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
    @if ($i == 2 && count($optionValues) > 3)
        <div class="show-more-options-checkbox py-2" style="display: none;">
    @endif

    @php $i++ @endphp
@endforeach

@if (count($optionValues) > 3)
    </div>
    <a href="#" class="show-more-link-checkbox">Show more</a>
    <script>
        $(document).ready(function() {
            $('.show-more-link-checkbox').click(function(e) {
                e.preventDefault();
                $('.show-more-options-checkbox').show();
                $('.show-more-link-checkbox').hide();
            });
        });
    </script>
@endif
