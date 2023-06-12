<div id="menu{{ $menuItem->menu_id }}" class="menu-item">
    <div class="d-flex flex-row">
        @if ($showMenuImages == 1 && $menuItemObject->hasThumb)
            <div
                class="col-3 p-0 me-3 menu-item-image align-self-center"
                style="
                    background: url('{{ $menuItem->getThumb() }}') no-repeat center center;
                    background-size: cover;
                    width: {{$menuImageWidth}}px;
                    height: {{$menuImageHeight}}px;
                    ">
            </div>
        @endif

        <div class="menu-content flex-grow-1 me-3">
            <h6 class="menu-name">{{ $menuItem->menu_name }}</h6>
            <p class="menu-desc text-muted mb-0">
                {!! nl2br($menuItem->menu_description) !!}
            </p>
        </div>
        <div class="menu-detail d-flex justify-content-end col-3 p-0">
            @if ($menuItemObject->specialIsActive)
                <div class="menu-meta text-muted pe-2">
                    <i
                        class="fa fa-star text-warning"
                        title="{!! sprintf(lang('igniter.local::default.text_end_elapsed'), $menuItemObject->specialDaysRemaining) !!}"
                    ></i>
                </div>
            @endif

            <div class="menu-price pe-3">
                @if ($menuItemObject->specialIsActive)
                    <s>{!! currency_format($menuItemObject->menuPriceBeforeSpecial) !!}</s>
                @endif
                {!! $menuItemObject->menuPrice > 0
                    ? '<b>'.currency_format($menuItemObject->menuPrice).'</b>'
                    : sprintf(lang('igniter.local::default.text_price_form'), '<b>'.currency_format($menuItem->menu_price_from).'</b>')
                !!}
            </div>

            @isset ($updateCartItemEventHandler)
                <div class="menu-button">
                    @themePartial('@button', ['menuItem' => $menuItem, 'menuItemObject' => $menuItemObject ])
                </div>
            @endisset
        </div>
    </div>
    <div class="layout-scrollable w-100">
        <div class="d-flex align-items-center py-2 allergens">
            @themePartial('@allergens', ['menuItem' => $menuItem, 'menuItemObject' => $menuItemObject])
        </div>
    </div>
</div>
