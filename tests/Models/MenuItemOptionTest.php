<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\MenuOption;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;

it('returns option name attribute', function() {
    $menuItemOption = MenuItemOption::factory()
        ->for(MenuOption::factory()->create([
            'option_name' => 'Option Name',
        ]), 'option')
        ->create();

    expect($menuItemOption)->option_name->toBe('Option Name');
});

it('returns display type attribute', function() {
    $menuItemOption = MenuItemOption::factory()
        ->for(MenuOption::factory()->create([
            'display_type' => 'radio',
        ]), 'option')
        ->create();

    expect($menuItemOption)->display_type->toBe('radio');
});

it('checks if menu item option is required', function() {
    $menuItemOption = MenuItemOption::factory()->create([
        'is_required' => true,
    ]);

    expect($menuItemOption->isRequired())->toBeTrue();
});

it('checks if menu item option is not required', function() {
    $menuItemOption = MenuItemOption::factory()->create([
        'is_required' => false,
    ]);

    expect($menuItemOption->isRequired())->toBeFalse();
});

it('checks if menu item option values are added correctly', function() {
    $menuItemOption = MenuItemOption::factory()->create();

    expect($menuItemOption->addMenuOptionValues([
        ['option_value_id' => 1, 'override_price' => 10.00],
        ['option_value_id' => 1, 'override_price' => 15.00],
        ['option_value_id' => 1, 'override_price' => 20.00],
    ]))->toBe(3);
});

it('adds values to menu item options on save correctly', function() {
    $menuItemOption = MenuItemOption::factory()->create();

    $menuItemOption->menu_option_values = [
        ['option_value_id' => 1, 'is_enabled' => 1, 'override_price' => 10.00],
        ['option_value_id' => 1, 'is_enabled' => 1, 'override_price' => 15.00],
        ['option_value_id' => 1, 'is_enabled' => 1, 'override_price' => 20.00],
    ];

    $menuItemOption->save();

    expect($menuItemOption->menu_option_values()->count())->toBe(3);
});

it('configures menu item option model correctly', function() {
    $menuItemOption = new MenuItemOption;

    expect(class_uses_recursive($menuItemOption))
        ->toHaveKey(Purgeable::class)
        ->toHaveKey(Validation::class)
        ->and($menuItemOption->getTable())->toBe('menu_item_options')
        ->and($menuItemOption->getKeyName())->toBe('menu_option_id')
        ->and($menuItemOption->getFillable())->toEqual([
            'option_id', 'menu_id', 'is_required', 'priority', 'min_selected', 'max_selected',
        ])
        ->and($menuItemOption->getAppends())->toEqual(['option_name', 'display_type'])
        ->and($menuItemOption->timestamps)->toBeTrue();
});
