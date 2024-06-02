<?php

namespace Tests\Models;

use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Scopes\CategoryScope;
use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Traits\HasPermalink;
use Igniter\Flame\Database\Traits\NestedTree;
use Igniter\Flame\Database\Traits\Sortable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\Scopes\LocationableScope;
use Igniter\System\Models\Concerns\Switchable;
use Illuminate\Support\Facades\DB;

it('returns enabled categories dropdown options', function() {
    $count = Category::count();

    Category::factory()->count(3)->create(['status' => 0]);

    expect(Category::getDropdownOptions()->all())->toHaveCount($count);
});

it('returns description attribute', function() {
    $category = Category::factory()->create(['description' => '<p>Description</p>']);

    expect($category->description)->toBe('Description');
});

it('counts menus', function() {
    $category = Category::factory()->create();
    $category->menus()->saveMany(Menu::factory()->count(3)->make());

    expect($category->count_menus)->toBe(3);
});

it('belongs to parent category', function() {
    $category = new Category();
    $relation = $category->parent_cat();

    expect($relation->getRelated())->toBeInstanceOf(Category::class)
        ->and($relation->getForeignKeyName())->toBe('parent_id')
        ->and($relation->getOwnerKeyName())->toBe('category_id');
});

it('belongs to many menus', function() {
    $category = new Category();
    $relation = $category->menus();

    expect($relation->getRelated())->toBeInstanceOf(Menu::class)
        ->and($relation->getTable())->toBe('menu_categories');
});

it('morphs to many locations', function() {
    $category = new Category();
    $relation = $category->locations();

    expect($relation->getMorphType())->toBe('locationable_type')
        ->and($relation->getRelated())->toBeInstanceOf(Location::class);
});

it('generates permalink slug', function() {
    $category = Category::factory()->create(['name' => 'Category Name']);

    expect($category->permalink_slug)->toBe('category-name');
});

it('scopes to categories with menus', function() {
    $menu = Menu::factory()->create();
    $menu->categories()->saveMany(Category::factory()->count(3)->make(['status' => 1]));

    expect(Category::whereHasMenus()->count())->toBe(3);
});

it('applies filters to query builder', function() {
    DB::table('categories')->update(['status' => 0, 'priority' => 10]);

    $location = Location::factory()->create();

    Category::factory()
        ->count(2)
        ->hasAttached($location)
        ->create(['status' => 1, 'priority' => 1]);

    $category = Category::factory()
        ->hasAttached($location)
        ->create([
            'name' => 'Location category',
            'status' => 1,
            'priority' => 0,
        ]);

    $query = Category::query()->applyFilters([
        'enabled' => 1,
        'location' => $location->getKey(),
        'sort' => 'priority desc',
    ]);

    expect($query->count())->toBe(3)
        ->and($query->first())->name->toBe($category->name);

    $query = Category::query()->applyFilters([
        'search' => 'Location category',
    ]);

    expect($query->count())->toBe(1)
        ->and($query->first())->name->toBe($category->name);
});

it('configures category model correctly', function() {
    $category = new Category();

    expect(class_uses_recursive($category))
        ->toContain(HasMedia::class)
        ->toContain(HasPermalink::class)
        ->toContain(Locationable::class)
        ->toContain(NestedTree::class)
        ->toContain(Sortable::class)
        ->toContain(Switchable::class)
        ->and($category->getTable())->toBe('categories')
        ->and($category->getKeyName())->toBe('category_id')
        ->and($category->timestamps)->toBeTrue()
        ->and($category->getGuarded())->toBe([])
        ->and($category->permalinkable)->toBe([
            'permalink_slug' => [
                'source' => 'name',
            ],
        ])
        ->and($category->mediable())->toHaveKey('thumb')
        ->and($category->getGlobalScopes())->toHaveKeys([
            CategoryScope::class,
            LocationableScope::class,
        ]);
});
