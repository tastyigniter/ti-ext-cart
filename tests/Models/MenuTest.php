<?php

namespace Igniter\Cart\Tests\Models;

use Carbon\Carbon;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Concerns\Stockable;
use Igniter\Cart\Models\Ingredient;
use Igniter\Cart\Models\Mealtime;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\MenuSpecial;
use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\System\Models\Concerns\Switchable;
use Illuminate\Support\Facades\DB;

it('returns menu_price_from attribute', function() {
    $menu = Menu::factory()->create(['menu_price' => 10.00]);

    expect($menu->menu_price_from)->toBe(10.00);
});

it('return minimum_qty attribute', function() {
    $menu = Menu::factory()->create(['minimum_qty' => 2]);

    expect($menu->minimum_qty)->toBe(2);
});

it('checks if menu is available', function() {
    $menu = Menu::factory()
        ->has(Mealtime::factory()->state([
            'start_time' => '10:00:00',
            'end_time' => '20:00:00',
        ]), 'mealtimes')
        ->create();

    $datetime = Carbon::createFromTime(12);

    expect($menu->isAvailable($datetime))->toBeTrue();
});

it('checks if menu is not available', function() {
    $menu = Menu::factory()
        ->has(Mealtime::factory()->state([
            'start_time' => '10:00:00',
            'end_time' => '20:00:00',
        ]), 'mealtimes')
        ->create();

    $datetime = Carbon::createFromTime(8);

    expect($menu->isAvailable($datetime))->toBeFalse();
});

it('checks if menu is special', function() {
    $menu = Menu::factory()
        ->has(MenuSpecial::factory()->state(['special_status' => 1]), 'special')
        ->create();

    expect($menu->isSpecial())->toBeTrue();
});

it('checks if menu is not special', function() {
    $menu = Menu::factory()->create();

    expect($menu->isSpecial())->toBeFalse();
});

it('check quantity is above minimum quantity', function() {
    $menu = Menu::factory()->create(['minimum_qty' => 2]);

    expect($menu->checkMinQuantity(3))->toBeTrue();
});

it('checks if menu has order type restriction', function() {
    $menu = Menu::factory()->create([
        'order_restriction' => ['delivery'],
    ]);

    expect($menu->hasOrderTypeRestriction('delivery'))->toBeFalse();
});

it('checks if menu does not have order type restriction', function() {
    $menu = Menu::factory()->create([
        'order_restriction' => ['delivery'],
    ]);

    expect($menu->hasOrderTypeRestriction('collection'))->toBeTrue();
});

it('has many menu_options', function() {
    $menu = new Menu();
    $relation = $menu->menu_options();

    expect($relation->getRelated())->toBeInstanceOf(MenuItemOption::class);
});

it('has one special', function() {
    $menu = new Menu();
    $relation = $menu->special();

    expect($relation->getRelated())->toBeInstanceOf(MenuSpecial::class)
        ->and($relation->getForeignKeyName())->toBe('menu_id');
});

it('belongs to many categories', function() {
    $menu = new Menu();
    $relation = $menu->categories();

    expect($relation->getRelated())->toBeInstanceOf(Category::class)
        ->and($relation->getTable())->toBe('menu_categories');
});

it('belongs to many mealtimes', function() {
    $menu = new Menu();
    $relation = $menu->mealtimes();

    expect($relation->getRelated())->toBeInstanceOf(Mealtime::class)
        ->and($relation->getTable())->toBe('menu_mealtimes');
});

it('morphs to many allergens', function() {
    $menu = new Menu();
    $relation = $menu->allergens();

    expect($relation->getMorphType())->toBe('ingredientable_type')
        ->and($relation->getRelated())->toBeInstanceOf(Ingredient::class);
});

it('morphs to many ingredients', function() {
    $menu = new Menu();
    $relation = $menu->ingredients();

    expect($relation->getMorphType())->toBe('ingredientable_type')
        ->and($relation->getRelated())->toBeInstanceOf(Ingredient::class);
});

it('morphs to many locations', function() {
    $menu = new Menu();
    $relation = $menu->locations();

    expect($relation->getMorphType())->toBe('locationable_type')
        ->and($relation->getRelated())->toBeInstanceOf(Location::class);
});

it('applies filters to query builder', function() {
    DB::table('menus')->update(['menu_status' => 0, 'menu_priority' => 10]);

    $location = Location::factory()->create();
    $category = Category::factory()->create(['status' => 1]);

    $category->menus()->saveMany(
        Menu::factory()
            ->hasAttached($location)
            ->count(2)
            ->create([
                'menu_status' => 1,
                'menu_priority' => 1,
                'order_restriction' => ['delivery'],
            ])
    );

    $menu = Menu::factory()
        ->hasAttached($location)
        ->create([
            'menu_name' => 'Location menu',
            'menu_status' => 1,
            'menu_priority' => 0,
        ]);

    $query = Menu::query()->applyFilters([
        'enabled' => 1,
        'location' => $location->getKey(),
        'sort' => 'menu_priority asc',
    ]);

    expect($query->count())->toBe(3)
        ->and($query->first())->menu_name->toBe($menu->menu_name);

    $query = Menu::query()->applyFilters([
        'enabled' => 1,
        'group' => 'categories.category_id',
        'category' => $category->getKey(),
        'orderType' => 'delivery',
    ]);

    expect($query->count())->toBe(2);

    $query = Menu::query()->applyFilters([
        'enabled' => 1,
        'search' => 'Location menu',
    ]);

    expect($query->count())->toBe(1)
        ->and($query->first())->menu_name->toBe($menu->menu_name);
});

it('adds menu options to menu on save correctly', function() {
    $menu = Menu::factory()->create();

    $menu->menu_options = [
        ['option_id' => 1, 'priority' => 1],
        ['option_id' => 2, 'priority' => 2],
    ];

    $menu->save();

    expect($menu->menu_options()->count())->toBe(2);
});

it('adds special to menu on save correctly', function() {
    $menu = Menu::factory()->create();

    $menu->special = [
        'special_id' => 1,
        'date_start' => '2021-01-01',
        'date_end' => '2021-01-31',
    ];

    $menu->save();

    expect($menu->special()->count())->toBe(1);
});

it('detaches relations from menu on delete', function() {
    $menu = Menu::factory()
        ->has(Category::factory()->count(2), 'categories')
        ->has(Mealtime::factory()->count(2), 'mealtimes')
        ->hasAttached(Ingredient::factory()->count(2))
        ->hasAttached(Location::factory()->count(2))
        ->create();

    expect($menu->categories()->count())->toBe(2)
        ->and($menu->mealtimes()->count())->toBe(2)
        ->and($menu->ingredients()->count())->toBe(2)
        ->and($menu->locations()->count())->toBe(2);

    $menu->delete();

    expect($menu->categories()->count())->toBe(0)
        ->and($menu->mealtimes()->count())->toBe(0)
        ->and($menu->ingredients()->count())->toBe(0)
        ->and($menu->locations()->count())->toBe(0);
});

it('configures menu model correctly', function() {
    $menu = new Menu();

    expect(class_uses_recursive($menu))
        ->toContain(HasMedia::class)
        ->toContain(Locationable::class)
        ->toContain(Purgeable::class)
        ->toContain(Stockable::class)
        ->toContain(Switchable::class)
        ->and($menu->getTable())->toBe('menus')
        ->and($menu->getKeyName())->toBe('menu_id')
        ->and($menu->timestamps)->toBeTrue()
        ->and($menu->getGuarded())->toBe([]);
});
