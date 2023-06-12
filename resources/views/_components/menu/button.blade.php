<button
    class="btn btn-light btn-sm btn-cart{{ $menuItemObject->mealtimeIsNotAvailable ? ' disabled' : '' }}"
    @if (!$menuItemObject->mealtimeIsNotAvailable)
        @if ($menuItemObject->hasOptions)
            data-cart-control="load-item"
    @else
        data-request="{{ $updateCartItemEventHandler }}"
    data-request-data="menuId: '{{ $menuItem->menu_id }}', quantity: '{{ $menuItem->minimum_qty }}'"
    data-replace-loading="fa fa-spinner fa-spin"
    @endif
    data-menu-id="{{ $menuItem->menu_id }}"
    data-quantity="{{ $menuItem->minimum_qty }}"
    @else
        title="{{ implode("\r\n", $menuItemObject->mealtimeTitles) }}"
    @endif
>
    <i @class(['fa fa-plus' => $menuItemObject->mealtimeIsAvailable, 'far fa-clock' => $menuItemObject->mealtimeIsNotAvailable])></i>
</button>
