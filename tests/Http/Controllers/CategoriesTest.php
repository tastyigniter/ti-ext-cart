<?php

namespace Igniter\Cart\Tests\Http\Controllers;

use Igniter\Cart\Models\Category;

it('loads categories page', function() {
    actingAsSuperUser()
        ->get(route('igniter.cart.categories'))
        ->assertOk();
});

it('loads create category page', function() {
    actingAsSuperUser()
        ->get(route('igniter.cart.categories', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit category page', function() {
    $category = Category::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.categories', ['slug' => 'edit/'.$category->getKey()]))
        ->assertOk();
});

it('loads category preview page', function() {
    $category = Category::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.categories', ['slug' => 'preview/'.$category->getKey()]))
        ->assertOk();
});

it('creates category', function() {
    actingAsSuperUser()
        ->post(route('igniter.cart.categories', ['slug' => 'create']), [
            'Category' => [
                'name' => 'Created Category',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Category::where('name', 'Created Category')->exists())->toBeTrue();
});

it('updates category', function() {
    $category = Category::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.categories', ['slug' => 'edit/'.$category->getKey()]), [
            'Category' => [
                'name' => 'Updated Category',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Category::find($category->getKey()))->name->toBe('Updated Category');
});

it('updates category fixes broken tree', function() {
    $category = Category::factory()->create();
    $categoryMock = mock(Category::class)->makePartial();
    $categoryMock->shouldReceive('isBroken')->andReturnTrue();
    $categoryMock->shouldReceive('fixTree')->once();
    app()->instance(Category::class, $categoryMock);

    actingAsSuperUser()
        ->post(route('igniter.cart.categories', ['slug' => 'edit/'.$category->getKey()]), [
            'Category' => [
                'name' => 'Updated Category',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Category::find($category->getKey()))->name->toBe('Updated Category');
});

it('deletes category', function() {
    $category = Category::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.categories', ['slug' => 'edit/'.$category->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Category::find($category->getKey()))->toBeNull();
});
