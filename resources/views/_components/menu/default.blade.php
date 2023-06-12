@if (!$hideMenuSearch)
    <div class="menu-search">
        @themePartial('@searchbar')
    </div>
@endif

<div class="menu-list">
    @if ($menuIsGrouped)
        @themePartial('@grouped', ['groupedMenuItems' => $menuList])
    @else
        @themePartial('@items', ['menuItems' => $menuList])
    @endif

    <div class="pagination-bar text-right">
        <div class="links">{!! $menuList->links() !!}</div>
    </div>
</div>
