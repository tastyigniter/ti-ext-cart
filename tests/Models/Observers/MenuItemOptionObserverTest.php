<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models\Observers;

use Igniter\Cart\Models\MenuItemOption;
use Igniter\Cart\Models\Observers\MenuItemOptionObserver;
use Mockery;

beforeEach(function(): void {
    $this->observer = new MenuItemOptionObserver;
    $this->menuItemOption = Mockery::mock(MenuItemOption::class)->makePartial();
});

it('restores purged values when saved', function(): void {
    $this->menuItemOption->shouldReceive('restorePurgedValues')->once();

    $this->observer->saved($this->menuItemOption);
});

it('adds menu option values when is_enabled is true', function(): void {
    $attributes = [
        'menu_option_values' => [
            ['is_enabled' => true, 'value' => 'Option 1'],
            ['is_enabled' => false, 'value' => 'Option 2'],
        ],
    ];

    $this->menuItemOption->shouldReceive('getAttributes')->andReturn($attributes);
    $this->menuItemOption->shouldReceive('addMenuOptionValues')
        ->with([['value' => 'Option 1']])
        ->once();

    $this->observer->saved($this->menuItemOption);
});

it('does not add menu option values when is_enabled is false', function(): void {
    $attributes = [
        'menu_option_values' => [
            ['is_enabled' => false, 'value' => 'Option 1'],
        ],
    ];

    $this->menuItemOption->shouldReceive('getAttributes')->andReturn($attributes);
    $this->menuItemOption->shouldReceive('addMenuOptionValues')
        ->with([])
        ->once();

    $this->observer->saved($this->menuItemOption);
});

it('handles empty menu_option_values gracefully', function(): void {
    $attributes = [
        'menu_option_values' => [],
    ];

    $this->menuItemOption->shouldReceive('getAttributes')->andReturn($attributes);
    $this->menuItemOption->shouldReceive('addMenuOptionValues')
        ->with([])
        ->once();

    $this->observer->saved($this->menuItemOption);
});
