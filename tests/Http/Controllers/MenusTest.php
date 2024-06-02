<?php

namespace Igniter\Cart\Tests\Http\Controllers;

use Igniter\Cart\Models\Menu;

it('loads menus page', function() {
    actingAsSuperUser()
        ->get(route('igniter.cart.menus'))
        ->assertOk();
});

it('loads create menu page', function() {
    actingAsSuperUser()
        ->get(route('igniter.cart.menus', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit menu page', function() {
    $menu = Menu::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.menus', ['slug' => 'edit/'.$menu->getKey()]))
        ->assertOk();
});

it('loads menu preview page', function() {
    $menu = Menu::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.cart.menus', ['slug' => 'edit/'.$menu->getKey()]))
        ->assertOk();
});

it('updates menu', function() {
    $menu = Menu::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.menus', ['slug' => 'edit/'.$menu->getKey()]), [
            'Menu' => [
                'menu_name' => 'Updated Menu',
                'menu_price' => 10.00,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Menu::find($menu->getKey()))->menu_name->toBe('Updated Menu');
});

it('deletes menu', function() {
    $menu = Menu::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.cart.menus', ['slug' => 'edit/'.$menu->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Menu::find($menu->getKey()))->toBeNull();
});
