<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Http\Controllers;

use Igniter\Admin\Models\Status;
use Igniter\Cart\Models\Order;
use Igniter\Flame\Exception\FlashException;
use Igniter\Local\Facades\Location;

it('accepts order and updates status', function(): void {
    $order = Order::factory()->create();
    $status = Status::factory()->create();
    setting()->set([
        'accepted_order_status' => $status->getKey(),
    ]);
    Location::setCurrent($order->location);

    actingAsSuperUser()
        ->post(route('igniter.cart.status_workflows', ['slug' => 'accept/'.$order->getKey()]))
        ->assertOk()
        ->assertJson([
            'message' => lang('igniter.cart::default.orders.alert_order_accepted'),
        ]);

    expect($order->fresh()->status_id)->toBe($status->getKey());
});

it('accepts order and updates status with delay', function(): void {
    $order = Order::factory()->create([
        'order_time' => '12:00:00',
    ]);
    $status = Status::factory()->create();
    setting()->set([
        'accepted_order_status' => $status->getKey(),
        'delay_times' => [
            ['time' => 15, 'comment' => 'Delayed by 15 minutes'],
        ],
    ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.status_workflows', ['slug' => 'accept/'.$order->getKey()]), [
            'minutes' => 15,
        ])
        ->assertOk()
        ->assertJson([
            'message' => lang('igniter.cart::default.orders.alert_order_accepted'),
        ]);

    expect($order->fresh())
        ->status_id->toBe($status->getKey())
        ->order_time->toBe('12:15:00')
        ->status_history->last()->comment->toBe('Delayed by 15 minutes');
});

it('rejects order and updates status with reason', function(): void {
    $order = Order::factory()->create();
    $status = Status::factory()->create();
    setting()->set([
        'rejected_reasons' => [
            ['code' => 'out_of_stock', 'comment' => 'Out of stock', 'status_id' => $status->getKey()],
        ],
    ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.status_workflows', ['slug' => 'reject/'.$order->getKey()]), [
            'reasonCode' => 'out_of_stock',
        ])
        ->assertOk()
        ->assertJson([
            'message' => lang('igniter.cart::default.orders.alert_order_rejected'),
        ]);

    expect($order->fresh())->status_id->toBe($status->getKey())
        ->status_history->last()->comment->toBe('Out of stock');
});

it('throws flash exception when accepted status is missing', function(): void {
    $order = Order::factory()->create();
    setting()->set(['accepted_order_status' => null]);
    Location::setCurrent($order->location);

    expect(fn() => actingAsSuperUser()
        ->withoutExceptionHandling()
        ->post(route('igniter.cart.status_workflows', ['slug' => 'accept/'.$order->getKey()])))
        ->toThrow(FlashException::class, lang('igniter.cart::default.orders.alert_accepted_status_missing'));
});

it('throws flash exception when reject reason code is missing', function(): void {
    $order = Order::factory()->create();
    Location::setCurrent($order->location);

    expect(fn() => actingAsSuperUser()
        ->withoutExceptionHandling()
        ->post(route('igniter.cart.status_workflows', ['slug' => 'reject/'.$order->getKey()]), [
            'reasonCode' => '',
        ]))
        ->toThrow(FlashException::class, lang('igniter.cart::default.orders.alert_missing_reject_code'));
});

it('accepts order with unknown delay minutes', function(): void {
    $order = Order::factory()->create(['order_time' => '12:00:00']);
    $status = Status::factory()->create();
    setting()->set([
        'accepted_order_status' => $status->getKey(),
        'delay_times' => [['time' => 30, 'comment' => 'Half hour']],
    ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.status_workflows', ['slug' => 'accept/'.$order->getKey()]), [
            'minutes' => 15,
        ])
        ->assertOk();

    expect($order->fresh())
        ->status_id->toBe($status->getKey())
        ->order_time->toBe('12:15:00');
});
