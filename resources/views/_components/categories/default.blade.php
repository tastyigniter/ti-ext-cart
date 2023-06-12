<ul class="nav flex-column nav-categories">
    <li class="nav-item">
        <a
            class="nav-link fw-bold{{ $selectedCategory ? '' : ' active' }}"
            href="{{ page_url('local/menus', ['category' => null]) }}"
        >@lang('igniter.local::default.text_all_categories')</a>
    </li>

    @themePartial('@items', ['categories' => $categories->toTree()])
</ul>
