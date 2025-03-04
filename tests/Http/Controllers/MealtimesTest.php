<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Http\Controllers;

use Igniter\Cart\Models\Mealtime;

it('loads mealtimes page', function(): void {
    actingAsSuperUser()
        ->get(route('igniter.cart.mealtimes'))
        ->assertOk();
});

it('loads create mealtime page', function(): void {
    actingAsSuperUser()
        ->get(route('igniter.cart.mealtimes', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit mealtime page', function(): void {
    $mealtime = Mealtime::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.mealtimes', ['slug' => 'edit/'.$mealtime->getKey()]))
        ->assertOk();
});

it('loads mealtime preview page', function(): void {
    $mealtime = Mealtime::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.mealtimes', ['slug' => 'preview/'.$mealtime->getKey()]))
        ->assertOk();
});

it('updates mealtime', function(): void {
    $mealtime = Mealtime::factory()->create();

    $url = route('igniter.cart.mealtimes', ['slug' => 'edit/'.$mealtime->getKey()]);
    actingAsSuperUser()
        ->post($url, [
            'Mealtime' => [
                'mealtime_name' => 'Updated Mealtime',
                'start_time' => '12:00',
                'end_time' => '14:00',
                'mealtime_status' => true,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Mealtime::find($mealtime->getKey()))->mealtime_name->toBe('Updated Mealtime');
});

it('deletes mealtime', function(): void {
    $mealtime = Mealtime::factory()->create();

    $url = route('igniter.cart.mealtimes', ['slug' => 'edit/'.$mealtime->getKey()]);
    actingAsSuperUser()
        ->post($url, [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Mealtime::find($mealtime->getKey()))->toBeNull();
});
