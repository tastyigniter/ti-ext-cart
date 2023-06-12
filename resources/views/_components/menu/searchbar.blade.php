<div class="py-3 px-4 border-top border-bottom">
    <form
        id="menu-search"
        method="GET"
        role="form"
        action="{{ current_url() }}"
    >
        <div class="input-group">
            @if (strlen($menuSearchTerm))
                <a
                    class="btn btn-light"
                    href="{{ current_url() }}"
                ><i class="fa fa-times"></i></a>
            @else
                <span class="input-group-text"><i class="fa fa-search"></i></span>
            @endif
            <input
                type="search"
                class="form-control"
                name="q"
                placeholder="@lang('igniter.local::default.label_menu_search')"
                value="{{ $menuSearchTerm }}"
                autocomplete="off"
            >
        </div>
    </form>
</div>
