@foreach ($menuItem->menu_options->sortBy('priority') as $index => $menuOption)
    <div
        class="menu-option"
        data-control="item-option"
        data-option-type="{{ $menuOption->display_type }}"
        data-option-minimum="{{ $menuOption->min_selected }}"
        data-option-maximum="{{ $menuOption->max_selected }}"
    >
        <div class="option option-{{ $menuOption->display_type }}">
            <div class="option-details">
                <h5 class="mb-0">
                    {{ $menuOption->option_name }}
                    @if ($menuOption->required == 1)
                        <span
                            class="small pull-right text-muted">@lang('igniter.cart::default.text_required')</span>
                    @endif
                </h5>
                @if ($menuOption->min_selected > 0 || $menuOption->max_selected > 0)
                    <p class="mb-0">{!! sprintf(lang('igniter.cart::default.text_option_summary'), $menuOption->min_selected, $menuOption->max_selected) !!}</p>
                @endif
            </div>

            @if (count($optionValues = $menuOption->menu_option_values))
                <input
                    type="hidden"
                    name="menu_options[{{ $index }}][menu_option_id]"
                    value="{{ $menuOption->menu_option_id }}"
                />
                <div class="option-group">
                    @if($menuOption->display_type !== 'select' && $limitCartItemOptionsValues && $optionValues->count() >= $limitCartItemOptionsValues)
                        @partial('@item_option_'.$menuOption->display_type, [
                            'index' => $index,
                            'cartItem' => $cartItem,
                            'optionValues' => $optionValues->sortBy('priority')->slice(0, $limitCartItemOptionsValues),
                        ])

                        <div class="hidden-item-options" style="display: none;">
                            @partial('@item_option_'.$menuOption->display_type, [
                                'index' => $index,
                                'cartItem' => $cartItem,
                                'optionValues' => $optionValues->sortBy('priority')->slice($limitCartItemOptionsValues),
                            ])
                        </div>
                        <button
                            type="button"
                            data-toggle="more-options"
                            class="btn btn-link"
                        >@lang('igniter.cart::default.button_show_more_options')</button>
                    @else
                        @partial('@item_option_'.$menuOption->display_type, [
                            'index' => $index,
                            'cartItem' => $cartItem,
                            'optionValues' => $optionValues->sortBy('priority'),
                        ])
                    @endif
                </div>
            @endif
        </div>
    </div>
@endforeach
