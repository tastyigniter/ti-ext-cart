<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\MenuItemOptionValue;
use Igniter\Cart\Models\MenuOptionValue;

it('returns option name attribute', function() {
    $menuItemOptionValue = MenuItemOptionValue::factory()
        ->for(MenuOptionValue::factory(['name' => 'Option Value Name'])->create(), 'option_value')
        ->create();

    expect($menuItemOptionValue->name)->toBe('Option Value Name');
});

it('returns price attribute', function() {
    $menuItemOptionValue = MenuItemOptionValue::factory()
        ->for(MenuOptionValue::factory(['price' => 10.00])->create(), 'option_value')
        ->create();

    expect($menuItemOptionValue->price)->toBe(10.00);

    $menuItemOptionValue->override_price = 15.00;

    expect($menuItemOptionValue->price)->toBe(15.00);
});

it('checks if menu item option value is default', function() {
    $menuItemOptionValue = MenuItemOptionValue::factory()->create(['is_default' => 1]);

    expect($menuItemOptionValue->isDefault())->toBeTrue();
});

it('checks if menu item option value is not default', function() {
    $menuItemOptionValue = MenuItemOptionValue::factory()->create(['is_default' => 0]);

    expect($menuItemOptionValue->isDefault())->toBeFalse();
});

it('configures menu item option value model correctly', function() {
    $menuItemOptionValue = new MenuItemOptionValue;
    expect($menuItemOptionValue->getTable())->toBe('menu_item_option_values')
        ->and($menuItemOptionValue->getKeyName())->toBe('menu_option_value_id')
        ->and($menuItemOptionValue->getFillable())->toEqual([
            'menu_option_id',
            'option_value_id',
            'override_price',
            'priority',
            'is_default',
        ])
        ->and($menuItemOptionValue->timestamps)->toBeTrue()
        ->and($menuItemOptionValue->getAppends())->toEqual(['name', 'price'])
        ->and($menuItemOptionValue->getMorphClass())->toBe('menu_item_option_values');
});
