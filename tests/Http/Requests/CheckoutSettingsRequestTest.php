<?php

namespace Igniter\Cart\Tests\Http\Requests;

use Igniter\Cart\Http\Requests\CheckoutSettingsRequest;
use Illuminate\Support\Facades\Validator;

it('returns correct attribute labels', function () {
    $request = new CheckoutSettingsRequest();

    $attributes = $request->attributes();

    expect($attributes)->toHaveKey('guest_order', lang('igniter.cart::default.label_guest_order'))
        ->and($attributes)->toHaveKey('limit_orders', lang('igniter.local::default.label_limit_orders'))
        ->and($attributes)->toHaveKey('limit_orders_count', lang('igniter.local::default.label_limit_orders_count'))
        ->and($attributes)->toHaveKey('payments.*', lang('igniter.payregister::default.label_payments'));
});

it('returns correct validation rules', function () {
    $request = new CheckoutSettingsRequest();

    $rules = $request->rules();

    expect($rules)->toHaveKey('guest_order')
        ->and($rules)->toHaveKey('limit_orders')
        ->and($rules)->toHaveKey('limit_orders_count')
        ->and($rules)->toHaveKey('payments.*')
        ->and($rules['guest_order'])->toContain('integer')
        ->and($rules['limit_orders'])->toContain('boolean')
        ->and($rules['limit_orders_count'])->toContain('integer', 'min:1', 'max:999')
        ->and($rules['payments.*'])->toContain('string');
});
