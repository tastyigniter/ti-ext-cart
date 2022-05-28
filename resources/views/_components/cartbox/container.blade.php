<div class="{{ $pageIsCart ?: 'affix-cart d-none d-lg-block' }}">
    <div class="panel panel-cart">
        <div class="panel-body">
            @if (has_component($__SELF__->property('localBoxAlias')))
                <div class="local-timeslot">
                    @themePartial($__SELF__->property('localBoxAlias').'::timeslot')
                </div>
                <div class="local-control mt-3">
                    @themePartial($__SELF__->property('localBoxAlias').'::control')
                </div>
            @endif

            @themePartial($__SELF__.'::default')
        </div>
    </div>
</div>
@themePartial($__SELF__.'::mobile')
