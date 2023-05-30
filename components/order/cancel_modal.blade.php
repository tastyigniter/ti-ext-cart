<button
    type="button"
    class="btn btn-light text-danger"
    data-bs-toggle="modal"
    data-bs-target="#cancelOrderModal"
>@lang('igniter.cart::default.orders.button_cancel')</button>
<div
    class="modal fade"
    id="cancelOrderModal"
    aria-labelledby="cancelOrderModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <form method="POST" data-request="{{ $__SELF__.'::onCancel' }}">
            <input type="hidden" name="orderId" value="{{ $order->order_id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="cancelOrderModalLabel">@lang('igniter.cart::default.orders.text_title_cancel')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <textarea
                    class="form-control"
                    name="cancel_reason"
                    id="cancelOrderReason"
                    rows="3"
                    placeholder="@lang('igniter.cart::default.orders.label_cancel_reason')"
                ></textarea>
                </div>
                <div class="modal-footer">
                    <button
                        type="submit"
                        class="btn btn-primary"
                        data-attach-loading
                    >@lang('igniter.cart::default.orders.button_cancel')</button>
                </div>
            </div>
        </form>
    </div>
</div>
