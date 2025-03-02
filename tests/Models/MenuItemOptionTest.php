<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\MenuItemOptionValue;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\MenuOptionValue;
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

it('returns option values with menu option values when they exist', function() {
    $menuOption = MenuOption::factory()->create(['display_type' => 'radio']);
    $menuOptionValue = MenuOptionValue::factory()->for($menuOption, 'option')->create(['price' => 10]);
    $menuItemOption = MenuItemOption::factory()->for($menuOption, 'option')->create();
    $menuItemOptionValue = MenuItemOptionValue::factory()
        ->for($menuItemOption, 'menu_option')
        ->create([
            'option_value_id' => $menuOptionValue->getKey(),
            'override_price' => null,
            'is_default' => true,
        ]);

    $result = $menuItemOption->getOptionValuesAttribute();
    $firstResult = $result->first();

    expect($result)->toBeCollection()
        ->and($firstResult->menu_option_value_id)->toBe($menuItemOptionValue->getKey())
        ->and($firstResult->menu_option_id)->toBe($menuItemOption->getKey())
        ->and($firstResult->option_value_id)->toBe($menuOptionValue->getKey())
        ->and($firstResult->price)->toBe(10.0)
        ->and($firstResult->override_price)->toBeNull()
        ->and($firstResult->is_default)->toBeTrue()
        ->and($firstResult->is_enabled)->toBeTrue();
});

it('returns option values with default values when menu option values do not exist', function() {
    $menuOption = MenuOption::factory()->create(['display_type' => 'radio']);
    $menuOptionValue = MenuOptionValue::factory()->for($menuOption, 'option')->create(['price' => 10]);
    $menuItemOption = MenuItemOption::factory()->for($menuOption, 'option')->create();

    $result = $menuItemOption->getOptionValuesAttribute();
    $firstResult = $result->first();

    expect($result)->toBeCollection()
        ->and($firstResult->menu_option_value_id)->toBeNull()
        ->and($firstResult->menu_option_id)->toBe($menuOption->getKey())
        ->and($firstResult->option_value_id)->toBe($menuOptionValue->getKey())
        ->and($result->first()->price)->toBe(10.0)
        ->and($result->first()->override_price)->toBeNull()
        ->and($result->first()->is_default)->toBeNull()
        ->and($result->first()->is_enabled)->toBeFalse();
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
        ->and($menuItemOption->timestamps)->toBeTrue()
        ->and($menuItemOption->relation)->toEqual([
            'hasMany' => [
                'menu_option_values' => [
                    \Igniter\Cart\Models\MenuItemOptionValue::class,
                    'foreignKey' => 'menu_option_id',
                    'delete' => true,
                ],
            ],
            'belongsTo' => [
                'menu' => [\Igniter\Cart\Models\Menu::class],
                'option' => [\Igniter\Cart\Models\MenuOption::class],
            ],
        ])
        ->and($menuItemOption->rules)->toEqual([
            ['menu_id', 'igniter.cart::default.menus.label_menu_id', 'required|integer'],
            ['option_id', 'igniter.cart::default.menus.label_option_id', 'required|integer'],
            ['priority', 'igniter.cart::default.menu_options.label_option', 'integer'],
            ['is_required', 'igniter.cart::default.menu_options.label_option_required', 'boolean'],
            ['min_selected', 'igniter.cart::default.menu_options.label_min_selected', 'integer|lte:max_selected'],
            ['max_selected', 'igniter.cart::default.menu_options.label_max_selected', 'integer|gte:min_selected'],
        ]);
});
