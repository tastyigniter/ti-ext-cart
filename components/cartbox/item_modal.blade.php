<div
    class="modal-dialog "
    data-control="cart-item"
    data-min-quantity="{{ $menuItem->minimum_qty }}"
    data-price-amount="{{ $cartItem ? $cartItem->price : $menuItem->getBuyablePrice() }}"
>
    <form method="POST" data-request="{{ $formHandler }}">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ $menuItem->getBuyableName() }}</h4>
                <button
                    type="button"
                    class="btn-close px-2"
                    data-bs-dismiss="modal"
                ></button>
            </div>
            @if ($showCartItemThumb && $menuItem->hasMedia('thumb'))
                <div class="modal-top">
                    <img
                        class="img-fluid"
                        src="{!! $menuItem->thumb->getThumb([
                              'width' => $cartItemThumbWidth,
                              'height' => $cartItemThumbHeight,
                            ]) !!}"
                        alt="{{ $menuItem->getBuyableName() }}"
                    />
                </div>
            @endif

            <div class="modal-body">
                @if (strlen($menuItem->menu_description))
                    <p class="text-muted">{!! nl2br($menuItem->menu_description) !!}</p>
                @endif

                <input type="hidden" name="menuId" value="{{ $menuItem->getBuyableIdentifier() }}"/>
                <input type="hidden" name="rowId" value="{{ $cartItem ? $cartItem->rowId : null }}"/>

                <div
                    id="menu-options"
                    class="menu-options"
                    data-control="item-options"
                >
                    @partial('@item_options')
                </div>

                <div class="menu-comment">
                    <textarea
                        name="comment"
                        class="form-control"
                        rows="2"
                        placeholder="@lang('igniter.cart::default.label_add_comment')"
                    >{{ $cartItem ? $cartItem->comment : null }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <div class="row g-0 w-100">
                    <div class="col-sm-5 pb-3 pb-sm-0">
                        <div class="input-group input-group-lg" data-toggle="quantity">
                            <button
                                class="btn btn-light"
                                data-operator="minus"
                                type="button"
                            ><i class="fa fa-minus"></i></button>
                            <input
                                type="number"
                                name="quantity"
                                class="form-control text-center"
                                value="{{ $cartItem ? $cartItem->qty : $menuItem->minimum_qty }}"
                                min="0"
                                autocomplete="off"
                            >
                            <button
                                class="btn btn-light"
                                data-operator="plus"
                                type="button"
                            ><i class="fa fa-plus fa-fw"></i></button>
                        </div>
                    </div>
                    <div class="col-sm-7 ps-sm-3">
                        <button type="submit" class="btn btn-primary btn-lg text-white w-100" data-attach-loading>
                            <div class="d-flex align-items-center">
                                <div class="col"></div>
                                <div class="col text-nowrap">{!! $cartItem
                                    ? lang('igniter.cart::default.button_update')
                                    : lang('igniter.cart::default.button_add_to_order')
                                !!}</div>
                                <div class="col text-end fw-normal fs-6" data-item-subtotal>
                                    {!! currency_format($cartItem
                                        ? $cartItem->subtotal
                                        : $menuItem->getBuyablePrice()) !!}
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
