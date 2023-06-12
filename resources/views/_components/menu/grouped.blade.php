<div class="menu-group">
    @forelse ($groupedMenuItems as $categoryId => $menuList)
        <div @class(['menu-group-item'])>
            @if ($categoryId > 0)
                @php
                    $menuCategory = array_get($menuListCategories, $categoryId);
                    $menuCategoryAlias = strtolower(str_slug($menuCategory->name));
                @endphp
                <div id="category-{{ $menuCategoryAlias }}-heading" role="tab">
                    <h4
                        @class(['category-title cursor-pointer', 'collapsed' => $loop->iteration >= $menuCollapseCategoriesAfter])
                        data-bs-toggle="collapse"
                        data-bs-target="#category-{{ $menuCategoryAlias }}-collapse"
                        aria-expanded="false"
                        aria-controls="category-{{ $menuCategoryAlias }}-heading"
                    >{{ $menuCategory->name }}<i class="fa fa-chevron-down pull-right"></i></h4>
                </div>
                <div
                    id="category-{{ $menuCategoryAlias }}-collapse"
                    class="collapse {{ $loop->iteration < $menuCollapseCategoriesAfter ? 'show' : '' }}"
                    role="tabpanel" aria-labelledby="{{ $menuCategoryAlias }}"
                >
                    <div class="menu-category">
                        @if (strlen($menuCategory->description))
                            <p>{!! nl2br($menuCategory->description) !!}</p>
                        @endif

                        @if ($menuCategory->hasMedia('thumb'))
                            <div class="image">
                                <img
                                    class="img-fluid"
                                    src="{{ $menuCategory->getThumb(['width' => $menuCategoryWidth, 'height' => $menuCategoryHeight]) }}"
                                    alt="{{ $menuCategory->name }}"
                                />
                            </div>
                        @endif
                    </div>

                    @themePartial('@items', ['menuItems' => $menuList])
                </div>
            @else
                @themePartial('@items', ['menuItems' => $menuList])
            @endif
        </div>
    @empty
        <div class="menu-group-item">
            <p>@lang('igniter.local::default.text_no_category')</p>
        </div>
    @endforelse
</div>
