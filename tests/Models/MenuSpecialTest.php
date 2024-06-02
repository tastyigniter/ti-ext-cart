<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\MenuSpecial;

it('checks if menu special is active', function() {
    $menuSpecial = MenuSpecial::factory()->create();

    expect($menuSpecial->active())->toBeTrue();
});

it('checks if menu special is not active', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'special_status' => 0,
    ]);

    expect($menuSpecial->active())->toBeFalse();
});

it('checks if menu special is recurring', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'validity' => 'recurring',
    ]);

    expect($menuSpecial->isRecurring())->toBeTrue();
});

it('checks if menu special is not recurring', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'validity' => 'forever',
    ]);

    expect($menuSpecial->isRecurring())->toBeFalse();
});

it('checks if menu special is expired', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'validity' => 'period',
        'start_date' => now()->subDays(4),
        'end_date' => now()->subDay(),
    ]);

    expect($menuSpecial->isExpired())->toBeTrue();
});

it('checks if menu special is not expired', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'validity' => 'period',
        'start_date' => now()->subDays(4),
        'end_date' => now()->addDay(),
    ]);

    expect($menuSpecial->isExpired())->toBeFalse();
});

it('checks if menu special price is fixed amount', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'type' => 'F',
    ]);

    expect($menuSpecial->isFixed())->toBeTrue();
});

it('checks if menu special price is percentage', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'type' => 'P',
    ]);

    expect($menuSpecial->isFixed())->toBeFalse();
});

it('checks if menu special price is calculated correctly for fixed type', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'type' => 'F',
        'special_price' => 10.00,
    ]);

    expect($menuSpecial->getMenuPrice(20.00))->toBe(10.00);
});

it('checks if menu special price is calculated correctly for percentage type', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'type' => 'P',
        'special_price' => 10.00,
    ]);

    expect($menuSpecial->getMenuPrice(20.00))->toBe(18.00);
});

it('checks if days remaining for menu special is calculated correctly', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'validity' => 'period',
        'start_date' => now()->subDays(4),
        'end_date' => now()->addDays(5),
    ]);

    expect($menuSpecial->daysRemaining())->toContain('4 days');
});

it('checks if days remaining for menu special is zero when expired', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'validity' => 'period',
        'start_date' => now()->subDays(4),
        'end_date' => now()->subDay(),
    ]);

    expect($menuSpecial->daysRemaining())->toBe(0);
});

it('checks if days remaining for menu special is zero when validity is not period', function() {
    $menuSpecial = MenuSpecial::factory()->create([
        'validity' => 'forever',
    ]);

    expect($menuSpecial->daysRemaining())->toBe(0);
});

it('configures menu special model correctly', function() {
    $menuSpecial = new MenuSpecial;

    expect($menuSpecial->getTable())->toBe('menus_specials')
        ->and($menuSpecial->getKeyName())->toBe('special_id')
        ->and($menuSpecial->getFillable())->toEqual([
            'menu_id', 'start_date',
            'end_date', 'special_price', 'type',
            'validity', 'recurring_every',
            'recurring_from', 'recurring_to',
        ]);
});
