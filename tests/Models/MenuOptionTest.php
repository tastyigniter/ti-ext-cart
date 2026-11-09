<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\MenuItemOptionValue;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location;

it('returns options with locations when locations are assigned', function(): void {
    LocationFacade::shouldReceive('currentOrAssigned')->andReturn([1, 2]);

    $result = MenuOption::getRecordEditorOptions();

    expect($result)->toBeCollection()
        ->and($result)->not()->toBeEmpty();
});

it('checks if menu option display type is select', function(): void {
    $menuOption = MenuOption::factory()->create(['display_type' => 'select']);

    expect($menuOption->isSelectDisplayType())->toBeTrue();
});

it('checks if menu option display type is not select', function(): void {
    $menuOption = MenuOption::factory()->create(['display_type' => 'radio']);

    expect($menuOption->isSelectDisplayType())->toBeFalse();
});

it('returns option values filtered by option_id', function(): void {
    MenuOptionValue::factory()->create(['option_id' => 123]);

    $result = MenuOption::getOptionValues(123);

    expect($result)->toBeCollection()
        ->and($result->count())->toBe(1);
});

it('checks if menu option values are added correctly', function(): void {
    $menuOption = MenuOption::factory()->create();

    expect($menuOption->addOptionValues([
        ['option_value_id' => 1, 'price' => 10.00],
        ['option_value_id' => 2, 'price' => 15.00],
        ['option_value_id' => 3, 'price' => 20.00],
    ]))->toBe(3);
});

it('adds values to menu options on save correctly', function(): void {
    $menuOption = MenuOption::factory()->create();

    $menuOption->values = [
        ['option_value_id' => 1, 'price' => 10.00],
        ['option_value_id' => 2, 'price' => 15.00],
        ['option_value_id' => 3, 'price' => 20.00],
    ];

    $menuOption->save();

    expect($menuOption->option_values()->count())->toBe(3);
});

function createOptionValueWithOverrides(): array
{
    $menuOption = MenuOption::factory()->create();
    $optionValue = MenuOptionValue::factory()->for($menuOption, 'option')->create(['name' => 'Large', 'price' => 10]);

    $overridden = MenuItemOptionValue::factory()
        ->for(MenuItemOption::factory()->for($menuOption, 'option')->create(), 'menu_option')
        ->create(['option_value_id' => $optionValue->getKey(), 'override_price' => 12]);

    $inherited = MenuItemOptionValue::factory()
        ->for(MenuItemOption::factory()->for($menuOption, 'option')->create(), 'menu_option')
        ->create(['option_value_id' => $optionValue->getKey(), 'override_price' => null]);

    return [$menuOption, $optionValue, $overridden, $inherited];
}

it('keeps menu item override prices when option value price changes by default', function(): void {
    [$menuOption, $optionValue, $overridden, $inherited] = createOptionValueWithOverrides();

    $menuOption->addOptionValues([
        ['option_value_id' => $optionValue->getKey(), 'name' => 'Large', 'price' => 15],
    ]);

    expect($optionValue->fresh()->price)->toBe(15.0)
        ->and($overridden->fresh()->override_price)->toBe(12.0)
        ->and($inherited->fresh()->override_price)->toBeNull();
});

it('updates menu item override prices when option value price changes and update is requested', function(): void {
    [$menuOption, $optionValue, $overridden, $inherited] = createOptionValueWithOverrides();

    $menuOption->addOptionValues([
        ['option_value_id' => $optionValue->getKey(), 'name' => 'Large', 'price' => 15],
    ], updateMenuPrices: true);

    expect($optionValue->fresh()->price)->toBe(15.0)
        ->and($overridden->fresh()->override_price)->toBe(15.0)
        ->and($inherited->fresh()->override_price)->toBeNull();
});

it('updates menu item override prices for all changed values when saving with update menu prices enabled', function(): void {
    [$menuOption, $optionValue, $overridden, $inherited] = createOptionValueWithOverrides();
    $otherValue = MenuOptionValue::factory()->for($menuOption, 'option')->create(['name' => 'Small', 'price' => 5]);
    $otherOverride = MenuItemOptionValue::factory()
        ->for(MenuItemOption::factory()->for($menuOption, 'option')->create(), 'menu_option')
        ->create(['option_value_id' => $otherValue->getKey(), 'override_price' => 6]);

    $menuOption->values = [
        ['option_value_id' => $optionValue->getKey(), 'name' => 'Large', 'price' => 15],
        ['option_value_id' => $otherValue->getKey(), 'name' => 'Small', 'price' => 7],
    ];
    $menuOption->update_menu_prices = '1';
    $menuOption->save();

    expect($overridden->fresh()->override_price)->toBe(15.0)
        ->and($otherOverride->fresh()->override_price)->toBe(7.0)
        ->and($inherited->fresh()->override_price)->toBeNull()
        ->and($menuOption->fresh()->getAttributes())->not->toHaveKey('update_menu_prices');
});

it('keeps menu item override prices when saving with update menu prices disabled', function(): void {
    [$menuOption, $optionValue, $overridden] = createOptionValueWithOverrides();

    $menuOption->values = [
        ['option_value_id' => $optionValue->getKey(), 'name' => 'Large', 'price' => 15],
    ];
    $menuOption->update_menu_prices = '0';
    $menuOption->save();

    expect($overridden->fresh()->override_price)->toBe(12.0);
});

it('does not update menu item override prices when option value price is unchanged', function(): void {
    [$menuOption, $optionValue, $overridden] = createOptionValueWithOverrides();

    $menuOption->addOptionValues([
        ['option_value_id' => $optionValue->getKey(), 'name' => 'Large Renamed', 'price' => 10],
    ], updateMenuPrices: true);

    expect($overridden->fresh()->override_price)->toBe(12.0);
});

it('does not update override prices of other option values', function(): void {
    [$menuOption, $optionValue] = createOptionValueWithOverrides();
    $otherValue = MenuOptionValue::factory()->for($menuOption, 'option')->create(['name' => 'Small', 'price' => 5]);
    $otherOverride = MenuItemOptionValue::factory()
        ->for(MenuItemOption::factory()->for($menuOption, 'option')->create(), 'menu_option')
        ->create(['option_value_id' => $otherValue->getKey(), 'override_price' => 6]);

    $menuOption->addOptionValues([
        ['option_value_id' => $optionValue->getKey(), 'name' => 'Large', 'price' => 15],
        ['option_value_id' => $otherValue->getKey(), 'name' => 'Small', 'price' => 5],
    ], updateMenuPrices: true);

    expect($otherOverride->fresh()->override_price)->toBe(6.0);
});

it('attaches menu option to menu', function(): void {
    $menu = Menu::factory()->create();
    $menuOption = MenuOption::factory()
        ->has(MenuOptionValue::factory()->count(3), 'option_values')
        ->create();

    $menuOption->attachRecordTo($menu);

    $menuItemOption = MenuItemOption::where('menu_id', $menu->menu_id)
        ->where('option_id', $menuOption->option_id)
        ->first();

    expect($menuItemOption)->toBeInstanceOf(MenuItemOption::class)
        ->and($menuItemOption->menu_option_values()->count())->toBe(3);
});

it('detaches location from menu option on delete', function(): void {
    $location = Location::factory()->create();
    $menuOption = MenuOption::factory()
        ->hasAttached($location)
        ->create();

    expect($menuOption->locations()->count())->toBe(1);

    $menuOption->delete();

    expect($menuOption->locations()->count())->toBe(0);
});

it('configures menu option model correctly', function(): void {
    $menuOption = new MenuOption;

    expect($menuOption->getTable())->toBe('menu_options')
        ->and($menuOption->getKeyName())->toBe('option_id')
        ->and($menuOption->getFillable())->toEqual([
            'option_id',
            'option_name',
            'display_type',
        ])
        ->and($menuOption->timestamps)->toBeTrue()
        ->and($menuOption->getMorphClass())->toBe('menu_options')
        ->and($menuOption->getPurgeableAttributes())->toEqual(['values', 'update_menu_prices']);
});

it('applies search filter to option name', function(): void {
    expect(MenuOption::query()->applySearch('cheese')->toSql())
        ->toContain('option_name')
        ->toContain('like');
});

it('skips search filter when search is empty', function(): void {
    expect(MenuOption::query()->applySearch('')->toSql())
        ->not->toContain('like');
});
