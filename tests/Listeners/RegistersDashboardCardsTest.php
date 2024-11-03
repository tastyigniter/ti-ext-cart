<?php

namespace Igniter\Cart\Tests\Listeners;

use Igniter\Cart\Listeners\RegistersDashboardCards;
use Igniter\Cart\Models\Order;

beforeEach(function() {
    $this->listener = new RegistersDashboardCards();
    $this->startDate = now()->subMonth();
    $this->endDate = now();
    $this->callback = function($query) {};
});

it('returns correct dashboard cards', function() {
    $cards = ($this->listener)();

    expect($cards)->toHaveKey('sale')
        ->and($cards)->toHaveKey('lost_sale')
        ->and($cards)->toHaveKey('cash_payment')
        ->and($cards)->toHaveKey('order')
        ->and($cards)->toHaveKey('delivery_order')
        ->and($cards)->toHaveKey('collection_order')
        ->and($cards)->toHaveKey('completed_order')
        ->and($cards['sale'])->toHaveKey('label', 'lang:igniter::admin.dashboard.text_total_sale')
        ->and($cards['sale'])->toHaveKey('icon', ' text-success fa fa-4x fa-line-chart')
        ->and($cards['sale'])->toHaveKey('valueFrom')
        ->and($cards['lost_sale'])->toHaveKey('label', 'lang:igniter::admin.dashboard.text_total_lost_sale')
        ->and($cards['lost_sale'])->toHaveKey('icon', ' text-danger fa fa-4x fa-line-chart')
        ->and($cards['lost_sale'])->toHaveKey('valueFrom')
        ->and($cards['cash_payment'])->toHaveKey('label', 'lang:igniter::admin.dashboard.text_total_cash_payment')
        ->and($cards['cash_payment'])->toHaveKey('icon', ' text-warning fa fa-4x fa-money-bill')
        ->and($cards['cash_payment'])->toHaveKey('valueFrom')
        ->and($cards['order'])->toHaveKey('label', 'lang:igniter::admin.dashboard.text_total_order')
        ->and($cards['order'])->toHaveKey('icon', ' text-success fa fa-4x fa-shopping-cart')
        ->and($cards['order'])->toHaveKey('valueFrom')
        ->and($cards['delivery_order'])->toHaveKey('label', 'lang:igniter::admin.dashboard.text_total_delivery_order')
        ->and($cards['delivery_order'])->toHaveKey('icon', ' text-primary fa fa-4x fa-truck')
        ->and($cards['delivery_order'])->toHaveKey('valueFrom')
        ->and($cards['collection_order'])->toHaveKey('label', 'lang:igniter::admin.dashboard.text_total_collection_order')
        ->and($cards['collection_order'])->toHaveKey('icon', ' text-info fa fa-4x fa-shopping-bag')
        ->and($cards['collection_order'])->toHaveKey('valueFrom')
        ->and($cards['completed_order'])->toHaveKey('label', 'lang:igniter::admin.dashboard.text_total_completed_order')
        ->and($cards['completed_order'])->toHaveKey('icon', ' text-success fa fa-4x fa-receipt')
        ->and($cards['completed_order'])->toHaveKey('valueFrom');
});

it('calculates total sale sum correctly', function() {
    setting()->set(['canceled_order_status' => 2]);
    Order::factory()->create(['order_total' => 100, 'status_id' => 1]);
    Order::factory()->create(['order_total' => 100, 'status_id' => 2]);

    $result = $this->listener->getValue('sale', $this->startDate, $this->endDate, $this->callback);

    expect($result)->toBe(currency_format(100));
});

it('calculates total lost sale sum correctly', function() {
    setting()->set(['canceled_order_status' => 2]);
    Order::factory()->create(['order_total' => 50, 'status_id' => 2]);
    Order::factory()->create(['order_total' => 150, 'status_id' => 1]);

    $result = $this->listener->getValue('lost_sale', $this->startDate, $this->endDate, $this->callback);

    expect($result)->toBe(currency_format(50));
});

it('calculates total cash payment sum correctly', function() {
    setting()->set(['canceled_order_status' => 2]);
    Order::factory()->create(['order_total' => 75, 'status_id' => 1, 'payment' => 'cod']);
    Order::factory()->create(['order_total' => 150, 'status_id' => 1, 'payment' => 'stripe']);

    $result = $this->listener->getValue('cash_payment', $this->startDate, $this->endDate, $this->callback);

    expect($result)->toBe(currency_format(75));
});

it('calculates total order count correctly', function() {
    setting()->set(['canceled_order_status' => 2]);
    Order::factory()->create(['order_total' => 10, 'status_id' => 1, 'payment' => 'cod']);
    Order::factory()->create(['order_total' => 150, 'status_id' => 2, 'payment' => 'stripe']);

    $result = $this->listener->getValue('order', $this->startDate, $this->endDate, $this->callback);

    expect($result)->toBe(2);
});

it('calculates total completed order count correctly', function() {
    setting()->set(['completed_order_status' => [5]]);
    Order::factory()->count(5)->create(['order_total' => 5, 'status_id' => 5, 'payment' => 'cod']);
    Order::factory()->create(['order_total' => 10, 'status_id' => 2, 'payment' => 'stripe']);


    $result = $this->listener->getValue('completed_order', $this->startDate, $this->endDate, $this->callback);

    expect($result)->toBe(5);
});

it('calculates total delivery order sum correctly', function() {
    setting()->set(['completed_order_status' => [5]]);
    Order::factory()->create(['order_total' => 200, 'status_id' => 5, 'order_type' => 'delivery']);
    Order::factory()->create(['order_total' => 100, 'status_id' => 2, 'order_type' => 'collection']);

    $result = $this->listener->getValue('delivery_order', $this->startDate, $this->endDate, $this->callback);

    expect($result)->toBe(currency_format(200));
});

it('calculates total collection order sum correctly', function() {
    setting()->set(['completed_order_status' => [5]]);
    Order::factory()->create(['order_total' => 100, 'status_id' => 2, 'order_type' => 'delivery']);
    Order::factory()->create(['order_total' => 150, 'status_id' => 5, 'order_type' => 'collection']);

    $result = $this->listener->getValue('collection_order', $this->startDate, $this->endDate, $this->callback);

    expect($result)->toBe(currency_format(150));
});
