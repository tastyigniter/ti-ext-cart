<div class="{{ $pageIsCart ?: 'affix-cart d-none d-sm-block' }}">
    <div class="panel panel-cart">
        <div class="panel-body">
            @if (has_component($__SELF__->property('localBoxAlias')))
            <div id="local-control">
                @partial($__SELF__->property('localBoxAlias').'::control')
            </div>
            @endif

            @partial($__SELF__.'::default')
        </div>
    </div>
</div>
@partial($__SELF__.'::mobile')