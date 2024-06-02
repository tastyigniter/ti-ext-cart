<?php

namespace Igniter\Cart\Tests\Http\Requests;

use Igniter\Cart\Http\Requests\MenuRequest;

it('has required rule for inputs: menu_name, menu_price, categories.*, ingredients.*, mealtimes.*, minimum_qty', function() {
    $rules = (new MenuRequest)->rules();
    expect('required')->toBeIn(array_get($rules, 'menu_name'))
        ->and('required')->toBeIn(array_get($rules, 'menu_price'))
        ->and('required')->toBeIn(array_get($rules, 'categories.*'))
        ->and('required')->toBeIn(array_get($rules, 'ingredients.*'))
        ->and('required')->toBeIn(array_get($rules, 'mealtimes.*'))
        ->and('required')->toBeIn(array_get($rules, 'minimum_qty'));
});

it('has string rule for inputs: menu_name, menu_description, order_restriction.*', function() {
    $rules = (new MenuRequest)->rules();
    expect('string')->toBeIn(array_get($rules, 'menu_name'))
        ->and('string')->toBeIn(array_get($rules, 'menu_description'))
        ->and('string')->toBeIn(array_get($rules, 'order_restriction.*'));
});

it('has between:2,255 rule for menu_name input', function() {
    $rules = (new MenuRequest)->rules();

    expect('between:2,255')->toBeIn(array_get($rules, 'menu_name'));
});

it('has between:2,1028 rule for menu_description input', function() {
    $rules = (new MenuRequest)->rules();

    expect('between:2,1028')->toBeIn(array_get($rules, 'menu_description'));
});

it('has numeric rule for menu_price input', function() {
    $rules = (new MenuRequest)->rules();

    expect('numeric')->toBeIn(array_get($rules, 'menu_price'));
});

it('has min:0 rule for menu_price, menu_priority input', function() {
    $rules = (new MenuRequest)->rules();

    expect('min:0')->toBeIn(array_get($rules, 'menu_price'))
        ->and('min:0')->toBeIn(array_get($rules, 'menu_priority'));
});

it('has min:1 rule for minimum_qty input', function() {
    $rules = (new MenuRequest)->rules();

    expect('min:1')->toBeIn(array_get($rules, 'minimum_qty'));
});

it('has boolean rule for menu_status input', function() {
    $rules = (new MenuRequest)->rules();

    expect('boolean')->toBeIn(array_get($rules, 'menu_status'));
});

it('has sometimes rule for inputs: categories.*, ingredients.*, mealtimes.*, minimum_qty', function() {
    $rules = (new MenuRequest)->rules();
    expect('sometimes')->toBeIn(array_get($rules, 'categories.*'))
        ->and('sometimes')->toBeIn(array_get($rules, 'ingredients.*'))
        ->and('sometimes')->toBeIn(array_get($rules, 'mealtimes.*'))
        ->and('sometimes')->toBeIn(array_get($rules, 'minimum_qty'));
});

it('has integer rule for inputs: categories.*, ingredients.*, mealtimes.*, locations.*, minimum_qty, mealtime_id, menu_priority', function() {
    $rules = (new MenuRequest)->rules();
    expect('integer')->toBeIn(array_get($rules, 'categories.*'))
        ->and('integer')->toBeIn(array_get($rules, 'ingredients.*'))
        ->and('integer')->toBeIn(array_get($rules, 'mealtimes.*'))
        ->and('integer')->toBeIn(array_get($rules, 'locations.*'))
        ->and('integer')->toBeIn(array_get($rules, 'minimum_qty'))
        ->and('integer')->toBeIn(array_get($rules, 'mealtime_id'))
        ->and('integer')->toBeIn(array_get($rules, 'menu_priority'));
});

it('has nullable rule for inputs: order_restriction.*, mealtime_id', function() {
    $rules = (new MenuRequest)->rules();
    expect('nullable')->toBeIn(array_get($rules, 'order_restriction.*'))
        ->and('nullable')->toBeIn(array_get($rules, 'mealtime_id'));
});
