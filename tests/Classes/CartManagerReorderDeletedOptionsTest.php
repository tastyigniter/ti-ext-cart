<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\CartItemOption;
use Igniter\Cart\CartItemOptions;
use Igniter\Cart\CartItemOptionValue;
use Igniter\Cart\CartItemOptionValues;
use Igniter\Cart\Classes\CartConditionManager;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Cart\Models\Order;
use Igniter\Coupons\CartConditions\Coupon;
use Igniter\Local\Models\Location as LocationModel;
use Mockery;

beforeEach(function(): void {
    $this->location = LocationModel::factory()->create();
    resolve('location')->setModel($this->location);

    $conditionManager = Mockery::mock(CartConditionManager::class);
    $conditionManager->shouldReceive('listRegisteredConditions')->andReturn([
        [
            'name' => 'coupon',
            'label' => 'Coupon',
        ],
    ]);
    $conditionManager->shouldReceive('makeCondition')->andReturn(new Coupon([
        'label' => 'Coupon',
        'name' => 'coupon',
    ]));
    app()->instance(CartConditionManager::class, $conditionManager);

    $this->manager = new CartManager;
});

it('reports a deleted option group while restoring the remaining options', function(): void {
    $menu = Menu::factory()->create();
    $availableOption = MenuOption::factory()->create(['display_type' => 'checkbox']);
    $deletedOption = MenuOption::factory()->create(['display_type' => 'checkbox']);

    $availableMenuOption = $menu->menu_options()->create(['option_id' => $availableOption->getKey()]);
    $deletedMenuOption = $menu->menu_options()->create(['option_id' => $deletedOption->getKey()]);

    $availableValue = MenuOptionValue::factory()->create();
    $deletedValue = MenuOptionValue::factory()->create();
    $availableMenuOptionValue = $availableMenuOption->menu_option_values()->create([
        'option_value_id' => $availableValue->getKey(),
    ]);
    $deletedMenuOptionValue = $deletedMenuOption->menu_option_values()->create([
        'option_value_id' => $deletedValue->getKey(),
    ]);

    $order = Order::factory()->create();
    $order->addOrderMenus([
        (object)[
            'id' => $menu->getKey(),
            'name' => $menu->menu_name,
            'qty' => 1,
            'price' => $menu->menu_price,
            'subtotal' => $menu->menu_price,
            'comment' => '',
            'options' => CartItemOptions::make([
                CartItemOption::fromArray([
                    'id' => $availableMenuOption->getKey(),
                    'name' => $availableMenuOption->option_name,
                    'values' => CartItemOptionValues::make([
                        CartItemOptionValue::fromArray([
                            'id' => $availableMenuOptionValue->getKey(),
                            'name' => $availableMenuOptionValue->name,
                            'price' => 0,
                            'qty' => 1,
                        ]),
                    ]),
                ]),
                CartItemOption::fromArray([
                    'id' => $deletedMenuOption->getKey(),
                    'name' => $deletedMenuOption->option_name,
                    'values' => CartItemOptionValues::make([
                        CartItemOptionValue::fromArray([
                            'id' => $deletedMenuOptionValue->getKey(),
                            'name' => $deletedMenuOptionValue->name,
                            'price' => 0,
                            'qty' => 1,
                        ]),
                    ]),
                ]),
            ]),
        ],
    ]);

    $deletedMenuOption->delete();

    $notes = $this->manager->restoreWithOrderMenus($order->getOrderMenus());
    $restoredItem = $this->manager->getCart()->content()->first();

    expect($notes)->toContain(sprintf(
        lang('igniter.cart::default.alert_option_value_not_found'),
        $deletedMenuOption->option_name,
    ))
        ->and($restoredItem->options)->toHaveCount(1)
        ->and($restoredItem->options->first()->id)->toBe($availableMenuOption->getKey());
});

it('reports a deleted option value while restoring values that are still available', function(): void {
    $menu = Menu::factory()->create();
    $option = MenuOption::factory()->create(['display_type' => 'checkbox']);
    $menuOption = $menu->menu_options()->create(['option_id' => $option->getKey()]);

    $availableValue = MenuOptionValue::factory()->create();
    $deletedValue = MenuOptionValue::factory()->create();
    $availableMenuOptionValue = $menuOption->menu_option_values()->create([
        'option_value_id' => $availableValue->getKey(),
    ]);
    $deletedMenuOptionValue = $menuOption->menu_option_values()->create([
        'option_value_id' => $deletedValue->getKey(),
    ]);

    $order = Order::factory()->create();
    $order->addOrderMenus([
        (object)[
            'id' => $menu->getKey(),
            'name' => $menu->menu_name,
            'qty' => 1,
            'price' => $menu->menu_price,
            'subtotal' => $menu->menu_price,
            'comment' => '',
            'options' => CartItemOptions::make([
                CartItemOption::fromArray([
                    'id' => $menuOption->getKey(),
                    'name' => $menuOption->option_name,
                    'values' => CartItemOptionValues::make([
                        CartItemOptionValue::fromArray([
                            'id' => $availableMenuOptionValue->getKey(),
                            'name' => $availableMenuOptionValue->name,
                            'price' => 0,
                            'qty' => 1,
                        ]),
                        CartItemOptionValue::fromArray([
                            'id' => $deletedMenuOptionValue->getKey(),
                            'name' => $deletedMenuOptionValue->name,
                            'price' => 0,
                            'qty' => 1,
                        ]),
                    ]),
                ]),
            ]),
        ],
    ]);

    $deletedMenuOptionValue->delete();

    $notes = $this->manager->restoreWithOrderMenus($order->getOrderMenus());
    $restoredValues = $this->manager->getCart()->content()->first()->options->first()->values;

    expect($notes)->toContain(sprintf(
        lang('igniter.cart::default.alert_option_value_not_found'),
        $deletedMenuOptionValue->name,
    ))
        ->and($restoredValues)->toHaveCount(1)
        ->and($restoredValues->first()->id)->toBe($availableMenuOptionValue->getKey());
});

it('restores legacy keyed option data without explicit ids', function(): void {
    $menu = Menu::factory()->create();
    $option = MenuOption::factory()->create(['display_type' => 'checkbox']);
    $menuOption = $menu->menu_options()->create(['option_id' => $option->getKey()]);
    $optionValue = MenuOptionValue::factory()->create();
    $menuOptionValue = $menuOption->menu_option_values()->create([
        'option_value_id' => $optionValue->getKey(),
    ]);

    $order = Order::factory()->create();
    $order->menus()->create([
        'menu_id' => $menu->getKey(),
        'name' => $menu->menu_name,
        'quantity' => 1,
        'price' => $menu->menu_price,
        'subtotal' => $menu->menu_price,
        'comment' => '',
        'option_values' => [
            $menuOption->getKey() => [
                'name' => $menuOption->option_name,
                'values' => [
                    $menuOptionValue->getKey() => [
                        'name' => $menuOptionValue->name,
                        'price' => 0,
                        'qty' => 1,
                    ],
                ],
            ],
        ],
    ]);

    $notes = $this->manager->restoreWithOrderMenus($order->getOrderMenus());
    $restoredItem = $this->manager->getCart()->content()->first();

    expect($notes)->toBe([])
        ->and($restoredItem->options)->toHaveCount(1)
        ->and($restoredItem->options->first()->id)->toBe($menuOption->getKey())
        ->and($restoredItem->options->first()->values->first()->id)->toBe($menuOptionValue->getKey());
});
