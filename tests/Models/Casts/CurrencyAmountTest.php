<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models\Casts;

use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\MenuItemOptionValue;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\System\Models\Currency;

function useCurrencyFormat(string $decimalSign, string $thousandSign): void
{
    Currency::factory()->create([
        'decimal_sign' => $decimalSign,
        'thousand_sign' => $thousandSign,
    ])->makeDefault();
    Currency::clearDefaultModels();
}

beforeEach(function(): void {
    Currency::clearDefaultModels();
});

afterEach(function(): void {
    Currency::clearDefaultModels();
});

it('stores a localized option value price normalized instead of truncated', function(): void {
    useCurrencyFormat(',', '.');
    $menuOption = MenuOption::factory()->create();

    $menuOption->values = [
        ['name' => 'Small', 'price' => '0,70', 'priority' => 1],
        ['name' => 'Large', 'price' => '1.234,50', 'priority' => 2],
    ];
    $menuOption->save();

    $prices = MenuOptionValue::where('option_id', $menuOption->getKey())
        ->orderBy('priority')->pluck('price')->all();

    expect($prices)->toBe([0.7, 1234.5]);
});

it('stores a localized menu item option override price normalized instead of truncated', function(): void {
    useCurrencyFormat(',', '.');
    $menuOptionValue = MenuOptionValue::factory()->create(['price' => 1.00]);

    $menuItemOptionValue = MenuItemOptionValue::factory()
        ->for(MenuItemOption::factory(), 'menu_option')
        ->for($menuOptionValue, 'option_value')
        ->create(['override_price' => '0,70']);

    expect($menuItemOptionValue->getAttributes()['override_price'])->toBe('0.70')
        ->and($menuItemOptionValue->fresh()->override_price)->toBe(0.7)
        ->and($menuItemOptionValue->fresh()->price)->toBe(0.7);
});

it('leaves machine-format and numeric amounts untouched', function(): void {
    useCurrencyFormat(',', '.');
    $menuOptionValue = new MenuOptionValue;

    $menuOptionValue->price = '11.50';
    expect($menuOptionValue->getAttributes()['price'])->toBe('11.50')
        ->and($menuOptionValue->price)->toBe(11.5);

    $menuOptionValue->price = 3;
    expect($menuOptionValue->getAttributes()['price'])->toBe(3)
        ->and($menuOptionValue->price)->toBe(3.0);
});

it('stores an empty amount as null', function(): void {
    $menuItemOptionValue = new MenuItemOptionValue;
    $menuItemOptionValue->override_price = '';

    expect($menuItemOptionValue->getAttributes()['override_price'])->toBeNull()
        ->and($menuItemOptionValue->override_price)->toBeNull();
});

it('fails validation for a comma decimal when the default currency uses a dot', function(): void {
    useCurrencyFormat('.', ',');
    $menuOptionValue = MenuOptionValue::factory()->make(['price' => '11,50']);

    expect($menuOptionValue->price)->toBe('11,50')
        ->and($menuOptionValue->validate())->toBeFalse()
        ->and($menuOptionValue->getErrors()->has('price'))->toBeTrue();
});

it('fails validation for a non-numeric amount instead of saving zero', function(): void {
    useCurrencyFormat(',', '.');
    $menuOptionValue = MenuOptionValue::factory()->make(['price' => 'free']);

    expect($menuOptionValue->validate())->toBeFalse()
        ->and($menuOptionValue->getErrors()->has('price'))->toBeTrue();
});
