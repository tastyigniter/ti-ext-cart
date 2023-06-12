<div class="menu-items">
    @forelse ($menuItems as $menuItemObject)
        @themePartial('@item', ['menuItem' => $menuItemObject->model, 'menuItemObject' => $menuItemObject])
    @empty
        <p>@lang('igniter.local::default.text_empty_menus')</p>
    @endforelse
</div>
