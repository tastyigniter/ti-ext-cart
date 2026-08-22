<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models\Concerns;

use Igniter\Cart\CartItemOption;
use Igniter\Cart\CartItemOptions;
use Igniter\Cart\CartItemOptionValue;
use Igniter\Cart\CartItemOptionValues;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Cart\Models\Order;

it('uses the stored historical option group name when the current option relation is deleted', function(): void {
    $order = Order::factory()->create();
    $menu = Menu::factory()->create();
    $option = MenuOption::factory()->create(['display_type' => 'checkbox']);
    $menuOption = $menu->menu_options()->create(['option_id' => $option->getKey()]);
    $optionValue = MenuOptionValue::factory()->create();
    $menuOptionValue = $menuOption->menu_option_values()->create([
        'option_value_id' => $optionValue->getKey(),
    ]);

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
                    'name' => 'Historical sauce choice',
                    'values' => CartItemOptionValues::make([
                        CartItemOptionValue::fromArray([
                            'id' => $menuOptionValue->getKey(),
                            'name' => 'Soy sauce',
                            'price' => 0,
                            'qty' => 1,
                        ]),
                    ]),
                ]),
            ]),
        ],
    ]);

    $menuOption->delete();

    $groupedOptions = $order->fresh()->getOrderMenusWithOptions()->first()->menu_options;

    expect($groupedOptions)
        ->toHaveKey('Historical sauce choice')
        ->not->toHaveKey('');
});
