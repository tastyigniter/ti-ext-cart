<?php

namespace Igniter\Cart\Tests\Models\Concerns;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOptionValue;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Cart\Models\Order;
use Igniter\Cart\Models\OrderMenu;
use Igniter\Cart\Models\OrderMenuOptionValue;
use Igniter\Cart\Models\Stock;

it('subtracts stock correctly', function() {
    $order = Order::factory()->create();

    $menu = Menu::factory()
        ->hasAttached($order->location)
        ->create();

    $menuStock = Stock::factory()
        ->for($order->location)
        ->for($menu, 'stockable')
        ->create(['quantity' => 10]);

    $menuOptionValue = MenuOptionValue::factory()->create();
    $menuItemOptionValue = MenuItemOptionValue::factory()->create([
        'option_value_id' => $menuOptionValue->getKey(),
    ]);
    $menuOptionStock = Stock::factory()
        ->for($order->location)
        ->for($menuOptionValue, 'stockable')
        ->create(['quantity' => 10]);

    $orderMenu = OrderMenu::create([
        'order_id' => $order->getKey(),
        'menu_id' => $menu->getKey(),
        'quantity' => 5,
    ]);

    $orderMenu->menu_options()->create([
        'order_id' => $order->getKey(),
        'order_menu_id' => $orderMenu->getKey(),
        'menu_option_value_id' => $menuItemOptionValue->getKey(),
        'quantity' => 5,
    ]);

    $order->subtractStock();

    $menuStock->refresh();
    $menuOptionStock->refresh();

    expect($menuStock->quantity)->toBe(5)
        ->and($menuOptionStock->quantity)->toBe(5);
});

it('gets order menus correctly', function() {
    $order = Order::factory()->create();
    $menu = Menu::factory()->create();

    $orderMenu = OrderMenu::create([
        'order_id' => $order->getKey(),
        'menu_id' => $menu->getKey(),
    ]);

    $orderMenus = $order->getOrderMenus();

    expect($orderMenus->first()->menu_id)->toBe($orderMenu->menu_id);
});

it('gets order menu options correctly', function() {
    $order = Order::factory()->create();
    $menu = Menu::factory()->create();
    $menuItemOptionValue = MenuItemOptionValue::factory()->create();

    $orderMenu = OrderMenu::create([
        'order_id' => $order->getKey(),
        'menu_id' => $menu->getKey(),
    ]);

    $orderMenuOptionValue = OrderMenuOptionValue::create([
        'order_id' => $order->getKey(),
        'order_menu_id' => $orderMenu->getKey(),
        'menu_option_value_id' => $menuItemOptionValue->getKey(),
    ]);

    expect($order->menu_options->first()->order_menu_id)->toBe($orderMenuOptionValue->order_menu_id);
});

it('gets order totals correctly', function() {
    $order = Order::factory()->create();

    $order->totals()->create([
        'code' => 'subtotal',
        'title' => 'Subtotal',
        'value' => 10.00,
    ]);

    $orderTotals = $order->getOrderTotals();

    expect($orderTotals->first())->code->toBe('subtotal');
});

it('adds order totals correctly', function() {
    $order = Order::factory()->create();

    $order->addOrderTotals([
        ['code' => 'subtotal', 'title' => 'Subtotal', 'value' => 10.00],
        ['code' => 'tax', 'title' => 'Tax', 'value' => 1.00],
        ['code' => 'total', 'title' => 'Total', 'value' => 11.00],
    ]);

    expect($order->totals()->count())->toBe(3);
});
