<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models;

use Carbon\Carbon;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Concerns\Stockable;
use Igniter\Cart\Models\Ingredient;
use Igniter\Cart\Models\Mealtime;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\MenuSpecial;
use Igniter\Cart\Models\Scopes\MenuScope;
use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\System\Models\Concerns\Switchable;
use Illuminate\Support\Facades\Event;

it('returns menu_price_from attribute', function(): void {
    $menu = Menu::factory()->create(['menu_price' => 10.00]);

    expect($menu->menu_price_from)->toBe(10.00);
});

it('returns menu_price_from attribute with menu options', function(): void {
    $menu = Menu::factory()->create(['menu_price' => 10.00]);
    $menuItemOption = $menu->menu_options()->create(['option_id' => 1]);
    $menuItemOption->menu_option_values()->create([
        'option_value_id' => 1,
        'override_price' => 5.00,
    ]);

    expect($menu->menu_price_from)->toBe(5.00);
});

it('return minimum_qty attribute', function(): void {
    $menu = Menu::factory()->create(['minimum_qty' => 2]);

    expect($menu->minimum_qty)->toBe(2);
});

it('returns true when menu has options', function(): void {
    $menu = Menu::factory()->create();
    $menu->menu_options()->create(['option_id' => 1]);

    expect($menu->hasOptions())->toBeTrue();
});

it('adds menu allergens when allergen ids are provided', function(): void {
    $menu = Menu::factory()->create();

    $result = $menu->addMenuAllergens([1, 2, 3]);

    expect($result)->toBeNull();
});

it('adds menu categories when menu exists', function(): void {
    $menu = Menu::factory()->create();

    $result = $menu->addMenuCategories([1, 2, 3]);

    expect($result)->toBeNull();
});

it('adds menu ingredients when menu exists', function(): void {
    $menu = Menu::factory()->create();

    $result = $menu->addMenuIngredients([1, 2, 3]);

    expect($result)->toBeNull();
});

it('adds menu mealtimes when menu exists', function(): void {
    $menu = Menu::factory()->create();

    $result = $menu->addMenuMealtimes([1, 2, 3]);

    expect($result)->toBeNull();
});

it('checks if menu is available', function(): void {
    $this->travelTo(Carbon::createFromTime(12)->toDateTimeString());

    $menu = Menu::factory()
        ->has(Mealtime::factory()->state([
            'start_time' => '10:00:00',
            'end_time' => '20:00:00',
        ]), 'mealtimes')
        ->create();

    expect($menu->isAvailable())->toBeTrue();
});

it('checks if menu is not available', function(): void {
    $menu = Menu::factory()
        ->has(Mealtime::factory()->state([
            'start_time' => '10:00:00',
            'end_time' => '20:00:00',
        ]), 'mealtimes')
        ->create();

    $datetime = Carbon::createFromTime(8)->toDateTimeString();

    expect($menu->isAvailable($datetime))->toBeFalse();
});

it('checks if menu is not available when it has disabled ingredient', function(): void {
    $menu = Menu::factory()
        ->has(Mealtime::factory()->state([
            'start_time' => '10:00:00',
            'end_time' => '20:00:00',
        ]), 'mealtimes')
        ->has(Ingredient::factory()->state([
            'status' => false,
        ]), 'ingredients')
        ->create();

    $datetime = Carbon::createFromTime(12);

    expect($menu->isAvailable($datetime))->toBeFalse();
});

it('checks if menu is not available when event returns false', function(): void {
    $menu = Menu::factory()
        ->has(Mealtime::factory()->state([
            'start_time' => '10:00:00',
            'end_time' => '20:00:00',
        ]), 'mealtimes')
        ->create();

    Event::listen('admin.menu.isAvailable', function($datetime, $menu): false {
        return false;
    });

    $datetime = Carbon::createFromTime(12);

    expect($menu->isAvailable($datetime))->toBeFalse();
});

it('checks if menu is special', function(): void {
    $menu = Menu::factory()
        ->has(MenuSpecial::factory()->state(['special_status' => 1]), 'special')
        ->create();

    expect($menu->isSpecial())->toBeTrue();
});

it('checks if menu is not special', function(): void {
    $menu = Menu::factory()->create();

    expect($menu->isSpecial())->toBeFalse();
});

it('check quantity is above minimum quantity', function(): void {
    $menu = Menu::factory()->create(['minimum_qty' => 2]);

    expect($menu->checkMinQuantity(3))->toBeTrue();
});

it('checks if menu has order type restriction', function(): void {
    $menu = Menu::factory()->create([
        'order_restriction' => ['delivery'],
    ]);

    expect($menu->hasOrderTypeRestriction('delivery'))->toBeFalse();
});

it('checks if menu does not have order type restriction', function(): void {
    $menu = Menu::factory()->create([
        'order_restriction' => ['delivery'],
    ]);

    expect($menu->hasOrderTypeRestriction('collection'))->toBeTrue();
});

it('returns special price when menu is special', function(): void {
    $menu = mock(Menu::class)->makePartial();
    $menu->shouldReceive('isSpecial')->andReturn(true);
    $menu->shouldReceive('extendableGet')->with('menu_price')->andReturn(100);
    $menu->shouldReceive('extendableGet')->with('special')->andReturnSelf();
    $menu->shouldReceive('getMenuPrice')->with(100)->andReturn(80);

    $result = $menu->getBuyablePrice();

    expect($result)->toBe(80);
});

it('has many menu_options', function(): void {
    $menu = new Menu;
    $relation = $menu->menu_options();

    expect($relation->getRelated())->toBeInstanceOf(MenuItemOption::class);
});

it('has one special', function(): void {
    $menu = new Menu;
    $relation = $menu->special();

    expect($relation->getRelated())->toBeInstanceOf(MenuSpecial::class)
        ->and($relation->getForeignKeyName())->toBe('menu_id');
});

it('belongs to many categories', function(): void {
    $menu = new Menu;
    $relation = $menu->categories();

    expect($relation->getRelated())->toBeInstanceOf(Category::class)
        ->and($relation->getTable())->toBe('menu_categories');
});

it('belongs to many mealtimes', function(): void {
    $menu = new Menu;
    $relation = $menu->mealtimes();

    expect($relation->getRelated())->toBeInstanceOf(Mealtime::class)
        ->and($relation->getTable())->toBe('menu_mealtimes');
});

it('morphs to many allergens', function(): void {
    $menu = new Menu;
    $relation = $menu->allergens();

    expect($relation->getMorphType())->toBe('ingredientable_type')
        ->and($relation->getRelated())->toBeInstanceOf(Ingredient::class);
});

it('morphs to many ingredients', function(): void {
    $menu = new Menu;
    $relation = $menu->ingredients();

    expect($relation->getMorphType())->toBe('ingredientable_type')
        ->and($relation->getRelated())->toBeInstanceOf(Ingredient::class);
});

it('morphs to many locations', function(): void {
    $menu = new Menu;
    $relation = $menu->locations();

    expect($relation->getMorphType())->toBe('locationable_type')
        ->and($relation->getRelated())->toBeInstanceOf(Location::class);
});

it('adds menu options to menu on save correctly', function(): void {
    $menu = Menu::factory()->create();

    $menu->setRelation('menu_options', [
        ['option_id' => 1, 'priority' => 1],
        ['option_id' => 2, 'priority' => 2],
    ]);

    $menu->save();

    expect($menu->menu_options()->count())->toBe(2);
});

it('adds special to menu on save correctly', function(): void {
    $menu = Menu::factory()->create();

    $menu->setRelation('special', [
        'special_id' => 1,
        'date_start' => '2021-01-01',
        'date_end' => '2021-01-31',
    ]);

    $menu->save();

    expect($menu->special()->count())->toBe(1);
});

it('detaches relations from menu on delete', function(): void {
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

it('applies filters to query builder', function(): void {
    $query = Menu::query()->applyFilters([
        'enabled' => 1,
        'location' => 1,
        'sort' => 'menu_priority asc',
    ]);

    expect($query->toSql())
        ->toContain('`menus`.`menu_status` = ?')
        ->toContain('`locationables`.`location_id` in (?)')
        ->toContain('order by `menu_priority` asc');
});

it('configures menu model correctly', function(): void {
    $menu = new Menu;

    expect(class_uses_recursive($menu))
        ->toContain(HasMedia::class)
        ->toContain(Locationable::class)
        ->toContain(Purgeable::class)
        ->toContain(Stockable::class)
        ->toContain(Switchable::class)
        ->and($menu->getTable())->toBe('menus')
        ->and($menu->getKeyName())->toBe('menu_id')
        ->and($menu->timestamps)->toBeTrue()
        ->and($menu->getGuarded())->toBe([])
        ->and($menu->getMorphClass())->toBe('menus')
        ->and($menu->getGlobalScopes())->toHaveKey(MenuScope::class);
});
