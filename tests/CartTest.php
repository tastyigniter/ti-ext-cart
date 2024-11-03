<?php

namespace Igniter\Cart\Tests;

use Igniter\Cart\Cart;
use Igniter\Cart\CartConditions\Tax;
use Igniter\Cart\Models\Cart as CartModel;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;

beforeEach(function() {
    $this->cart = resolve(Cart::class);
});

it('adds item to the cart correctly', function() {
    $cartItem = $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    expect($this->cart->content())->toHaveCount(1)
        ->and($cartItem->id)->toBe(1);
});

it('adds item with options to the cart correctly', function() {
    $cartItem = $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
        'options' => [
            [
                'id' => 111,
                'name' => 'Size',
                'values' => [
                ],
            ],
            [
                'id' => 222,
                'name' => 'Topings',
                'price' => 2.00,
            ],
        ],
    ], 1);

    expect($this->cart->content())->toHaveCount(1)
        ->and($cartItem->options->count())->toEqual(2);
});

it('adds item with conditions to the cart correctly', function() {
    $condition = new Tax([
        'name' => 'VAT',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
    ]);

    Location::setModel(LocationModel::factory()->create());

    $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
        'conditions' => [$condition],
    ], 1);

    expect($this->cart->total())->toBe(10.00);
});

it('updates item in the cart correctly', function() {
    $cartItem = $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    $this->cart->update($cartItem->rowId, 2);

    expect($this->cart->get($cartItem->rowId)->qty)->toBe(2);
});

it('removes item from the cart correctly', function() {
    $cartItem = $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    $this->cart->remove($cartItem->rowId);

    expect($this->cart->content())->toBeEmpty();
});

it('applies conditions to the cart correctly', function() {
    $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    $condition = new Tax([
        'name' => 'VAT',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
    ]);
    $this->cart->loadCondition($condition);

    expect($this->cart->total())->toBe(10.00);
});

it('clears the cart correctly', function() {
    $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    $this->cart->destroy();

    expect($this->cart->content())->toBeEmpty();
});

it('searches the cart correctly', function() {
    $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    $searchResult = $this->cart->search(function($cartItem, $rowId) {
        return $cartItem->id === 1;
    });

    expect($searchResult)->toHaveCount(1);
});

it('stores the cart correctly', function() {
    $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    $this->cart->store('test');

    expect(CartModel::where('identifier', 'test')->exists())->toBeTrue();
});

it('restores the cart correctly', function() {
    $this->cart->add([
        'id' => 1,
        'name' => 'Test Item',
        'price' => 10.00,
    ], 1);

    $this->cart->store('test');
    $this->cart->destroy();

    $this->cart->restore('test');

    expect($this->cart->content())->toHaveCount(1);
});
