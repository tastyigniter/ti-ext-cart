<?php

namespace Igniter\Cart\Tests\Notifications;

use Igniter\Cart\Models\Order;
use Igniter\Cart\Notifications\OrderCreatedNotification;
use Igniter\User\Models\User;

it('returns enabled users with location', function() {
    User::factory()->create(['status' => 1]);
    $order = Order::factory()->create();

    $result = OrderCreatedNotification::make()->subject($order)->getRecipients();

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1);
});

it('returns correct notification title', function() {
    expect(OrderCreatedNotification::make()->getTitle())
        ->toEqual(lang('igniter.cart::default.checkout.notify_order_created_title'));
});

it('returns correct notification URL with subject', function() {
    $order = Order::factory()->create();
    $result = OrderCreatedNotification::make()->subject($order)->getUrl();

    expect($result)->toBe(admin_url('orders/edit/'.$order->getKey()));
});

it('returns correct notification URL without subject', function() {
    $result = OrderCreatedNotification::make()->getUrl();

    expect($result)->toBe(admin_url('orders'));
});

it('returns correct notification message', function() {
    $order = Order::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $result = OrderCreatedNotification::make()->subject($order)->getMessage();

    expect($result)->toBe(sprintf(lang('igniter.cart::default.checkout.notify_order_created'), 'John Doe'));
});

it('returns correct notification icon', function() {
    $result = OrderCreatedNotification::make()->getIcon();

    expect($result)->toBe('fa-clipboard-list');
});

it('returns correct notification alias', function() {
    $result = OrderCreatedNotification::make()->getAlias();

    expect($result)->toBe('order-created');
});
