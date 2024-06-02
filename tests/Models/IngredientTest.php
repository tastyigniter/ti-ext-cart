<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\Ingredient;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\System\Models\Concerns\Switchable;

it('returns description attribute', function() {
    $ingredient = Ingredient::factory()->create(['description' => '<p>Description</p>']);

    expect($ingredient->description)->toBe('Description');
});

it('counts menus', function() {
    $ingredient = Ingredient::factory()->create();
    $ingredient->menus()->saveMany(Menu::factory()->count(3)->make());

    expect($ingredient->count_menus)->toBe(3);
});

it('morphs to many menus', function() {
    $ingredient = new Ingredient();
    $relation = $ingredient->menus();

    expect($relation->getRelated())->toBeInstanceOf(Menu::class)
        ->and($relation->getTable())->toBe('ingredientables');
});

it('morphs to many menu option values', function() {
    $ingredient = new Ingredient();
    $relation = $ingredient->menu_option_values();

    expect($relation->getRelated())->toBeInstanceOf(MenuOptionValue::class)
        ->and($relation->getTable())->toBe('ingredientables');
});

it('scopes to ingredients with menus', function() {
    $menu = Menu::factory()->create();
    $menu->ingredients()->saveMany(Ingredient::factory()->count(3)->make(['status' => 1]));

    expect(Ingredient::whereHasMenus()->count())->toBe(3);
});

it('scopes to allergen ingredients', function() {
    Ingredient::factory()->count(3)->create(['is_allergen' => 0]);
    Ingredient::factory()->count(3)->create(['is_allergen' => 1]);

    expect(Ingredient::isAllergen()->count())->toBe(3);
});

it('configures ingredient model correctly', function() {
    $ingredient = new Ingredient;

    expect(class_uses_recursive($ingredient))
        ->toContain(HasMedia::class)
        ->toContain(Switchable::class)
        ->and($ingredient->getTable())->toBe('ingredients')
        ->and($ingredient->getKeyName())->toBe('ingredient_id')
        ->and($ingredient->getGuarded())->toBe([])
        ->and($ingredient->mediable())->toHaveKey('thumb')
        ->and($ingredient->timestamps)->toBeTrue();
});
