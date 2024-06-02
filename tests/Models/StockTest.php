<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\Stock;
use Igniter\Local\Models\Location;
use Illuminate\Support\Facades\Event;

it('updates stock correctly', function() {
    Event::fake();

    $stock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => true,
    ]);

    $stock->updateStock(5, Stock::STATE_SOLD);

    expect($stock->quantity)->toBe(5);

    Event::assertDispatched('admin.stock.updated', function($event, $args) use ($stock) {
        [$updatedStock, $history, $stockQty] = $args;
        return $updatedStock->getKey() === $stock->getKey();
    });
});

it('does not update stock when not tracked', function() {
    Event::fake();

    $stock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => false,
    ]);

    $stock->updateStock(5, Stock::STATE_SOLD);

    expect($stock->quantity)->toBe(10);
    Event::assertNotDispatched('admin.stock.updated');
});

it('checks stock correctly', function() {
    $stock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => true,
    ]);

    expect($stock->checkStock(5))->toBeTrue()
        ->and($stock->checkStock(15))->toBeFalse();
});

it('checks if stock is out of stock correctly', function() {
    $stock = Stock::factory()->create([
        'quantity' => 0,
        'is_tracked' => true,
    ]);

    expect($stock->outOfStock())->toBeTrue();
});

it('checks if stock has low stock correctly', function() {
    $stock = Stock::factory()->create([
        'quantity' => 5,
        'low_stock_threshold' => 10,
        'is_tracked' => true,
    ]);

    expect($stock->hasLowStock())->toBeTrue();
});

it('gets mail recipients correctly', function() {
    $location = Location::factory()->create();
    $stock = Stock::factory()->for($location)->create();

    $recipients = $stock->mailGetRecipients('location');

    expect($recipients)->toBe([[$location->location_email, $location->location_name]]);
});

it('configures stock model correctly', function() {
    $stock = new Stock;
    expect($stock->getTable())->toBe('stocks')
        ->and($stock->getKeyName())->toBe('id')
        ->and($stock->getGuarded())->toBe(['quantity'])
        ->and($stock->timestamps)->toBeTrue();
});
