<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Http\Requests;

use Igniter\Cart\Http\Requests\MealtimeRequest;

it('returns correct attribute labels', function(): void {
    $request = new MealtimeRequest;

    $attributes = $request->attributes();

    expect($attributes)->toHaveKey('mealtime_name', lang('igniter.cart::default.mealtimes.label_mealtime_name'))
        ->and($attributes)->toHaveKey('start_time', lang('igniter.cart::default.mealtimes.label_start_time'))
        ->and($attributes)->toHaveKey('end_time', lang('igniter.cart::default.mealtimes.label_end_time'))
        ->and($attributes)->toHaveKey('mealtime_status', lang('igniter::admin.label_status'))
        ->and($attributes)->toHaveKey('locations.*', lang('igniter::admin.label_location'));
});

it('returns correct validation rules', function(): void {
    $request = new MealtimeRequest;

    $rules = $request->rules();

    expect($rules)->toHaveKey('mealtime_name')
        ->and($rules)->toHaveKey('start_time')
        ->and($rules)->toHaveKey('end_time')
        ->and($rules)->toHaveKey('mealtime_status')
        ->and($rules)->toHaveKey('locations')
        ->and($rules)->toHaveKey('locations.*')
        ->and($rules['mealtime_name'])->toContain('required', 'string', 'between:2,255')
        ->and($rules['start_time'])->toContain('required', 'date_format:H:i')
        ->and($rules['end_time'])->toContain('required', 'date_format:H:i')
        ->and($rules['mealtime_status'])->toContain('required', 'boolean')
        ->and($rules['locations'])->toContain('nullable', 'array')
        ->and($rules['locations.*'])->toContain('integer');
});
