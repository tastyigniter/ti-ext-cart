@php $customerAddresses = $order->listCustomerAddresses() @endphp
<div class="form-group @if(!count($customerAddresses)) d-none @endif">
    <label for="">@lang('igniter.cart::default.checkout.text_delivery_address')</label>
    <select
        class="form-select"
        name="address_id"
    >
        <option value="0">@lang('igniter.cart::default.checkout.text_address')</option>
        @foreach ($customerAddresses as $address)
            <option
                value="{{ $address->address_id }}"
                {!! $order->address_id == $address->address_id ? 'selected="selected"' : '' !!}
            >{!! $address->formatted_address !!}</option>
        @endforeach
    </select>
    {!! form_error('address_id', '<span class="text-danger">', '</span>') !!}
</div>

<div
    @if (count($customerAddresses))
    class="mt-3"
    data-trigger="[name='address_id']"
    data-trigger-action="show"
    data-trigger-condition="value[0]"
    data-trigger-closest-parent="form"
    @endif
>
    <input
        type="hidden"
        name="address[address_id]"
        value="{{ set_value('address.address_id', $order->address['address_id'] ?? '') }}"
    >
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="">@lang('igniter.cart::default.checkout.label_address_1')</label>
                <input
                    type="text"
                    name="address[address_1]"
                    class="form-control"
                    value="{{ set_value('address[address_1]', $order->address['address_1'] ?? '') }}"/>
                {!! form_error('address.address_1', '<span class="text-danger">', '</span>') !!}
            </div>
        </div>
        @if ($showAddress2Field)
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="">@lang('igniter.cart::default.checkout.label_address_2')</label>
                    <input
                        type="text"
                        name="address[address_2]"
                        class="form-control"
                        value="{{ set_value('address[address_2]', $order->address['address_2'] ?? '') }}"/>
                    {!! form_error('address.address_2', '<span class="text-danger">', '</span>') !!}
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        @if ($showCityField)
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="">@lang('igniter.cart::default.checkout.label_city')</label>
                    <input
                        type="text"
                        name="address[city]"
                        class="form-control"
                        value="{{ set_value('address[city]', $order->address['city'] ?? '') }}"/>
                    {!! form_error('address.city', '<span class="text-danger">', '</span>') !!}
                </div>
            </div>
        @endif
        @if ($showStateField)
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="">@lang('igniter.cart::default.checkout.label_state')</label>
                    <input
                        type="text"
                        name="address[state]"
                        class="form-control"
                        value="{{ set_value('address[state]', $order->address['state'] ?? '') }}"/>
                    {!! form_error('address.state', '<span class="text-danger">', '</span>') !!}
                </div>
            </div>
        @endif
        @if ($showPostcodeField)
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="">@lang('igniter.cart::default.checkout.label_postcode')</label>
                    <input
                        type="text"
                        name="address[postcode]"
                        class="form-control"
                        value="{{ set_value('address[postcode]', $order->address['postcode'] ?? '') }}"/>
                    {!! form_error('address.postcode', '<span class="text-danger">', '</span>') !!}
                </div>
            </div>
        @endif
    </div>
    @if ($showCountryField)
        <div class="form-group">
            <label for="">@lang('igniter.cart::default.checkout.label_country')</label>
            <select
                name="address[country_id]"
                class="form-select"
            >
                @foreach (countries('country_name') as $key => $value)
                    <option
                        value="{{ $key }}"
                        {!! ($key == $order->address['country_id']) ? 'selected="selected"' : '' !!}
                    >{{ $value }}</option>
                @endforeach
            </select>
            {!! form_error('address.country_id', '<span class="text-danger">', '</span>') !!}
        </div>
    @endif
</div>
