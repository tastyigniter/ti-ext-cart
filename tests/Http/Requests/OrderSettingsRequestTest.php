<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Http\Requests;

use Igniter\Cart\Http\Requests\OrderSettingsRequest;

it('returns correct attribute labels', function(): void {
    $request = new OrderSettingsRequest;

    $attributes = $request->attributes();

    expect($attributes)->toHaveCount(13)
        ->and($attributes)->toHaveKey('order_email.*', lang('igniter.cart::default.label_order_email'))
        ->and($attributes)->toHaveKey('processing_order_status', lang('igniter.cart::default.label_processing_order_status'))
        ->and($attributes)->toHaveKey('completed_order_status', lang('igniter.cart::default.label_completed_order_status'))
        ->and($attributes)->toHaveKey('canceled_order_status', lang('igniter.cart::default.label_canceled_order_status'))
        ->and($attributes)->toHaveKey('guest_order', lang('igniter.cart::default.label_guest_order'))
        ->and($attributes)->toHaveKey('location_order', lang('igniter.cart::default.label_location_order'))
        ->and($attributes)->toHaveKey('invoice_prefix', lang('igniter.cart::default.label_invoice_prefix'))
        ->and($attributes)->toHaveKey('invoice_logo', lang('igniter.cart::default.label_invoice_logo'))
        ->and($attributes)->toHaveKey('tax_mode', lang('igniter.cart::default.label_tax_mode'))
        ->and($attributes)->toHaveKey('tax_title', lang('igniter.cart::default.label_tax_title'))
        ->and($attributes)->toHaveKey('tax_percentage', lang('igniter.cart::default.label_tax_percentage'))
        ->and($attributes)->toHaveKey('tax_menu_price', lang('igniter.cart::default.label_tax_menu_price'))
        ->and($attributes)->toHaveKey('tax_delivery_charge', lang('igniter.cart::default.label_tax_delivery_charge'));
});

it('returns correct validation rules', function(): void {
    $request = new OrderSettingsRequest;

    $rules = $request->rules();

    expect($rules)->toHaveCount(15)
        ->and($rules)->toHaveKey('order_email.*')
        ->and($rules)->toHaveKey('processing_order_status')
        ->and($rules)->toHaveKey('completed_order_status')
        ->and($rules)->toHaveKey('processing_order_status.*')
        ->and($rules)->toHaveKey('completed_order_status.*')
        ->and($rules)->toHaveKey('canceled_order_status')
        ->and($rules)->toHaveKey('guest_order')
        ->and($rules)->toHaveKey('location_order')
        ->and($rules)->toHaveKey('invoice_prefix')
        ->and($rules)->toHaveKey('invoice_logo')
        ->and($rules)->toHaveKey('tax_mode')
        ->and($rules)->toHaveKey('tax_title')
        ->and($rules)->toHaveKey('tax_percentage')
        ->and($rules)->toHaveKey('tax_menu_price')
        ->and($rules)->toHaveKey('tax_delivery_charge')
        ->and($rules['order_email.*'])->toContain('required', 'alpha')
        ->and($rules['processing_order_status'])->toContain('required', 'array')
        ->and($rules['completed_order_status'])->toContain('required', 'array')
        ->and($rules['processing_order_status.*'])->toContain('required', 'integer')
        ->and($rules['completed_order_status.*'])->toContain('required', 'integer')
        ->and($rules['canceled_order_status'])->toContain('required', 'integer')
        ->and($rules['guest_order'])->toContain('required', 'integer')
        ->and($rules['location_order'])->toContain('required', 'integer')
        ->and($rules['invoice_prefix'])->toContain('nullable', 'regex:/^[a-zA-Z0-9-_\{\}]+$/')
        ->and($rules['invoice_logo'])->toContain('nullable', 'string')
        ->and($rules['tax_mode'])->toContain('required', 'integer')
        ->and($rules['tax_title'])->toContain('string', 'max:32')
        ->and($rules['tax_percentage'])->toContain('required_if:tax_mode,1', 'numeric')
        ->and($rules['tax_menu_price'])->toContain('nullable', 'numeric')
        ->and($rules['tax_delivery_charge'])->toContain('numeric');
});
