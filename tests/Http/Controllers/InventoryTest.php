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
