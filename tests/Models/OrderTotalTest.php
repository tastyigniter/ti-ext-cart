<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\OrderTotal;

it('configures order total model correctly', function() {
    $orderTotal = new OrderTotal;
    expect($orderTotal->getTable())->toBe('order_totals')
        ->and($orderTotal->getKeyName())->toBe('order_total_id')
        ->and($orderTotal->getGuarded())->toBe([]);
});
