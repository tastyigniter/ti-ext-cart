<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Http\Controllers;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Stock;

it('loads inventory page', function(): void {
    Stock::factory()
        ->count(5)
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->get(route('igniter.cart.inventory'))
        ->assertOk();
});

it('marks as out of stock', function(): void {
    $stock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => true,
    ]);
    $untrackedStock = Stock::factory()->create([
        'quantity' => 10,
        'is_tracked' => false,
    ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'code' => 'out_of_stock',
            'checked' => [$stock->getKey(), $untrackedStock->getKey()],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onBulkAction',
        ])
        ->assertOk();

    expect($stock->fresh()->quantity)->toBe(0)
        ->and($untrackedStock->fresh()->quantity)->toBe(10);
});

it('marks stock as out of stock indefinitely via override', function(): void {
    $stock = Stock::factory()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
            'outOfStockType' => 'indefinitely',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onMarkOutOfStock',
        ])
        ->assertOk();

    $fresh = $stock->fresh();
    expect($fresh->out_of_stock_type)->toBe(Stock::OOS_INDEFINITELY)
        ->and($fresh->out_of_stock_until)->toBeNull()
        ->and($fresh->quantity)->toBe(10);
});

it('marks stock as out of stock with duration via override', function(): void {
    $stock = Stock::factory()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
            'outOfStockType' => 'duration',
            'outOfStockDuration' => '60',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onMarkOutOfStock',
        ])
        ->assertOk();

    $fresh = $stock->fresh();
    expect($fresh->out_of_stock_type)->toBe(Stock::OOS_CUSTOM)
        ->and($fresh->out_of_stock_until)->not->toBeNull()
        ->and($fresh->quantity)->toBe(10);
});

it('marks stock as out of stock with custom date via override', function(): void {
    $until = now()->addHours(3)->format('Y-m-d\TH:i');

    $stock = Stock::factory()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
            'outOfStockType' => 'custom',
            'outOfStockUntil' => $until,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onMarkOutOfStock',
        ])
        ->assertOk();

    $fresh = $stock->fresh();
    expect($fresh->out_of_stock_type)->toBe(Stock::OOS_CUSTOM)
        ->and($fresh->out_of_stock_until)->not->toBeNull()
        ->and($fresh->quantity)->toBe(10);
});

it('rejects out of stock override with invalid type', function(): void {
    $stock = Stock::factory()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
            'outOfStockType' => 'invalid',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onMarkOutOfStock',
        ])
        ->assertInternalServerError();
});

it('rejects out of stock custom override with past date', function(): void {
    $stock = Stock::factory()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
            'outOfStockType' => 'custom',
            'outOfStockUntil' => now()->subHour()->format('Y-m-d\TH:i'),
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onMarkOutOfStock',
        ])
        ->assertInternalServerError();
});

it('rejects out of stock custom override without date', function(): void {
    $stock = Stock::factory()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
            'outOfStockType' => 'custom',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onMarkOutOfStock',
        ])
        ->assertInternalServerError();
});

it('marks stock as out of stock until closing time via override', function(): void {
    $stock = Stock::factory()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
            'is_tracked' => true,
        ]);

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
            'outOfStockType' => 'closing',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onMarkOutOfStock',
        ])
        ->assertOk();

    $fresh = $stock->fresh();
    expect($fresh->out_of_stock_type)->toBe(Stock::OOS_CUSTOM)
        ->and($fresh->out_of_stock_until)->not->toBeNull()
        ->and($fresh->quantity)->toBe(10);
});

it('clears out of stock override', function(): void {
    $stock = Stock::factory()
        ->outOfStockIndefinitely()
        ->for(Menu::factory(), 'stockable')
        ->create([
            'quantity' => 10,
        ]);

    expect($stock->hasOutOfStockOverride())->toBeTrue();

    actingAsSuperUser()
        ->post(route('igniter.cart.inventory'), [
            'recordId' => $stock->getKey(),
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onClearOutOfStock',
        ])
        ->assertOk();

    $fresh = $stock->fresh();
    expect($fresh->out_of_stock_type)->toBeNull()
        ->and($fresh->out_of_stock_until)->toBeNull();
});
