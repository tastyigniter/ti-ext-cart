<?php

namespace Igniter\Cart\Tests\Http\Controllers;

use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Location;

it('loads orders page', function() {
    actingAsSuperUser()
        ->get(route('igniter.cart.orders'))
        ->assertOk();
});

it('loads edit order page', function() {
    $order = Order::factory()->create();
    $order->location = Location::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.orders', ['slug' => 'edit/'.$order->getKey()]))
        ->assertOk();
});

it('loads order preview page', function() {
    $order = Order::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.orders', ['slug' => 'edit/'.$order->getKey()]))
        ->assertOk();
});

it('deletes order', function() {
    $order = Order::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.orders', ['slug' => 'edit/'.$order->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Order::find($order->getKey()))->toBeNull();
});
