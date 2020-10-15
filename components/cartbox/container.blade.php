<div class="{{ $pageIsCart ?: 'affix-cart d-none d-sm-block' }}">
    <div class="panel panel-cart">
        <div class="panel-body">
            <div id="local-control">
                @partial($__SELF__->property('localBoxAlias').'::control')
            </div>

            @partial($__SELF__.'::default')
        </div>
    </div>
</div>
@partial($__SELF__.'::mobile')