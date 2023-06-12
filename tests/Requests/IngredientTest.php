<?php

namespace Tests\Requests;

use Igniter\Cart\Requests\IngredientRequest;

it('has rules for name input', function () {
    $rules = array_get((new IngredientRequest)->rules(), 'name');

    expect('required')->toBeIn($rules)
        ->and('between:2,255')->toBeIn($rules);
});

it('has rules for description input', function () {
    expect('min:2')->toBeIn(array_get((new IngredientRequest)->rules(), 'description'));
});
