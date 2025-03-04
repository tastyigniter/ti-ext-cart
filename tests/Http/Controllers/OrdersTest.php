<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Http\Controllers;

use Igniter\Admin\Models\Status;
use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Location;

it('loads orders page', function(): void {
    actingAsSuperUser()
        ->get(route('igniter.cart.orders'))
        ->assertOk();
});

it('loads edit order page', function(): void {
    $order = Order::factory()->create();
    $order->location = Location::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.orders', ['slug' => 'edit/'.$order->getKey()]))
        ->assertOk();
});

it('loads order preview page', function(): void {
    $order = Order::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.orders', ['slug' => 'preview/'.$order->getKey()]))
        ->assertOk();
});

it('loads order invoice page', function(): void {
    $order = Order::factory()->create();
    $order->generateInvoice();

    actingAsSuperUser()
        ->get(route('igniter.cart.orders', ['slug' => 'invoice/'.$order->getKey()]))
        ->assertOk();
});

it('deletes order from list page', function(): void {
    $order = Order::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.orders'), ['checked' => [$order->getKey()]], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Order::find($order->getKey()))->toBeNull();
});

it('deletes order from edit page', function(): void {
    $order = Order::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.orders', ['slug' => 'edit/'.$order->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Order::find($order->getKey()))->toBeNull();
});

it('updates order status', function(): void {
    $order = Order::factory()->create();
    $status = Status::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.orders'), [
            'recordId' => $order->getKey(),
            'statusId' => $status->getKey(),
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onUpdateStatus',
        ]);

    expect(Order::find($order->getKey())->status_id)->toEqual($status->getKey());
});
