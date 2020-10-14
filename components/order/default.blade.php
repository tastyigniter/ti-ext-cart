<div class="card mb-1">
    <div class="card-body text-sm-center" id="ti-order-status">
        @partial('@status')
    </div>
</div>

@if (!$session->customer())
    <div class="card mb-1">
        <div class="card-body text-sm-center">
            <a
                href="{{ $session->loginUrl() }}"
            >@lang('igniter.cart::default.orders.text_login_to_view_more')</a>
        </div>
    </div>
@else
    @if ($showReviews AND !empty($reviewable))
        <div class="card mb-1">
            <div class="card-body">
                @partial('localReview::form')
            </div>
        </div>
    @endif

    <div class="row no-gutters">
        <div class="col-sm-7 pr-sm-1">
            <div class="card mb-1">
                <div class="card-body">
                    @partial('@restaurant', ['location' => $order->location])
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @partial('@items')
                </div>
            </div>
        </div>

        <div class="col-sm-5">
            @partial($__SELF__.'::details')
        </div>
    </div>
@endif