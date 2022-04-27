@if (!$order)
    <div class="card mb-1">
        <div class="card-body text-center" id="ti-order-status">
            No order found
        </div>
    </div>
@else
    <div class="card mb-1">
        <div class="card-body text-center" id="ti-order-status">
            @partial($__SELF__.'::status')
        </div>
    </div>

    @if ($session && !$session->customer())
        <div class="card mb-1">
            <div class="card-body text-center">
                <a
                    href="{{ $session->loginUrl() }}"
                >@lang('igniter.cart::default.orders.text_login_to_view_more')</a>
            </div>
        </div>
    @else
        @if ($showReviews && !empty($reviewable))
            <div class="card mb-1">
                <div class="card-body">
                    @partial('localReview::form')
                </div>
            </div>
        @endif

        <div class="row g-0">
            <div class="col-sm-7 pe-sm-1">
                <div class="card mb-1">
                    <div class="card-body">
                        @partial($__SELF__.'::restaurant', ['location' => $order->location])
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        @partial($__SELF__.'::items')
                    </div>
                </div>
            </div>

            <div class="col-sm-5">
                @partial($__SELF__.'::details')
            </div>
        </div>
    @endif
@endif
