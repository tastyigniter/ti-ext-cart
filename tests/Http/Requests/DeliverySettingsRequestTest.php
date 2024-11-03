<?php

namespace Igniter\Cart\Tests\Http\Requests;

use Igniter\Cart\Http\Requests\DeliverySettingsRequest;

it('returns correct attribute labels', function() {
    $request = new DeliverySettingsRequest();

    $attributes = $request->attributes();

    expect($attributes)->toHaveCount(10)
        ->and($attributes)->toHaveKey('is_enabled', lang('igniter.local::default.label_is_enabled'))
        ->and($attributes)->toHaveKey('delivery_time_interval', lang('igniter.local::default.label_delivery_time_interval'))
        ->and($attributes)->toHaveKey('delivery_lead_time', lang('igniter.local::default.label_delivery_lead_time'))
        ->and($attributes)->toHaveKey('future_orders.enable_delivery', lang('igniter.local::default.label_future_delivery_order'))
        ->and($attributes)->toHaveKey('future_orders.min_delivery_days', lang('igniter.local::default.label_future_min_delivery_days'))
        ->and($attributes)->toHaveKey('future_orders.delivery_days', lang('igniter.local::default.label_future_delivery_days'))
        ->and($attributes)->toHaveKey('delivery_time_restriction', lang('igniter.local::default.label_delivery_time_restriction'))
        ->and($attributes)->toHaveKey('delivery_cancellation_timeout', lang('igniter.local::default.label_delivery_cancellation_timeout'))
        ->and($attributes)->toHaveKey('delivery_add_lead_time', lang('igniter.local::default.label_delivery_add_lead_time'))
        ->and($attributes)->toHaveKey('delivery_min_order_amount', lang('igniter.local::default.label_delivery_min_order_amount'));
});

it('returns correct validation rules', function() {
    $request = new DeliverySettingsRequest();

    $rules = $request->rules();

    expect($rules)->toHaveCount(10)
        ->and($rules)->toHaveKey('is_enabled')
        ->and($rules)->toHaveKey('delivery_time_interval')
        ->and($rules)->toHaveKey('delivery_lead_time')
        ->and($rules)->toHaveKey('future_orders.enable_delivery')
        ->and($rules)->toHaveKey('future_orders.min_delivery_days')
        ->and($rules)->toHaveKey('future_orders.delivery_days')
        ->and($rules)->toHaveKey('delivery_time_restriction')
        ->and($rules)->toHaveKey('delivery_add_lead_time')
        ->and($rules)->toHaveKey('delivery_cancellation_timeout')
        ->and($rules)->toHaveKey('delivery_min_order_amount')
        ->and($rules['is_enabled'])->toContain('boolean')
        ->and($rules['delivery_time_interval'])->toContain('integer', 'min:5')
        ->and($rules['delivery_lead_time'])->toContain('integer', 'min:5')
        ->and($rules['future_orders.enable_delivery'])->toContain('boolean')
        ->and($rules['future_orders.min_delivery_days'])->toContain('integer', 'min:0')
        ->and($rules['future_orders.delivery_days'])->toContain('integer', 'min:0', 'gt:future_orders.min_delivery_days')
        ->and($rules['delivery_time_restriction'])->toContain('nullable', 'integer', 'max:2')
        ->and($rules['delivery_add_lead_time'])->toContain('boolean')
        ->and($rules['delivery_cancellation_timeout'])->toContain('integer', 'min:0', 'max:999')
        ->and($rules['delivery_min_order_amount'])->toContain('numeric', 'min:0');
});
