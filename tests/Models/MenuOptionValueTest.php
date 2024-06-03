<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\MenuOptionValue;

it('configures menu option value model correctly', function() {
    $menuOptionValue = new MenuOptionValue;

    expect($menuOptionValue->getTable())->toBe('menu_option_values')
        ->and($menuOptionValue->getKeyName())->toBe('option_value_id')
        ->and($menuOptionValue->getFillable())->toEqual([
            'option_id',
            'name',
            'price',
            'ingredients',
            'priority',
        ])
        ->and($menuOptionValue->sortable)->toEqual([
            'sortOrderColumn' => 'priority',
            'sortWhenCreating' => true,
        ])
        ->and($menuOptionValue->timestamps)->toBeFalse();
});
