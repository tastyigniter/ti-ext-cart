<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\OrderMenu;

it('configures order menu model correctly', function() {
    $orderMenu = new OrderMenu;
    expect($orderMenu->getTable())->toBe('order_menus')
        ->and($orderMenu->getKeyName())->toBe('order_menu_id')
        ->and($orderMenu->getGuarded())->toBe([]);
});
