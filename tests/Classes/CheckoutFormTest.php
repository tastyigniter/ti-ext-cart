<?php

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\Classes\CheckoutForm;

it('initializes with default config', function() {
    $checkoutForm = new CheckoutForm();

    expect($checkoutForm->config)->toBeArray()
        ->and($checkoutForm->config)->toBeEmpty();
});

it('fills config from provided array', function() {
    $config = ['fields' => [], 'model' => 'TestModel'];
    $checkoutForm = new CheckoutForm($config);

    $checkoutForm->initialize();

    expect($checkoutForm->config['fields'])->toBeArray()
        ->and($checkoutForm->config['model'])->toEqual('TestModel');
});

it('returns validation rules with prefixed keys', function() {
    $config = ['rules' => ['name' => 'required']];
    $checkoutForm = new CheckoutForm($config);

    $checkoutForm->initialize();
    $rules = $checkoutForm->validationRules();

    expect($rules)->toHaveKey('fields.name')
        ->and($rules['fields.name'])->toEqual('required');
});

it('returns validation messages with prefixed keys', function() {
    $config = ['messages' => ['name.required' => 'Name is required']];
    $checkoutForm = new CheckoutForm($config);

    $checkoutForm->initialize();
    $messages = $checkoutForm->validationMessages();

    expect($messages)->toHaveKey('fields.name.required')
        ->and($messages['fields.name.required'])->toEqual('Name is required');
});

it('returns validation attributes with prefixed keys', function() {
    $config = ['fields' => ['name' => ['label' => 'Name']]];
    $checkoutForm = new CheckoutForm($config);

    $checkoutForm->initialize();
    $attributes = $checkoutForm->validationAttributes();

    expect($attributes)->toHaveKey('fields.name')
        ->and($attributes['fields.name'])->toEqual('Name');
});

it('defines form fields correctly', function() {
    $config = ['fields' => ['name' => ['label' => 'Name', 'type' => 'text']]];
    $checkoutForm = new CheckoutForm($config);

    $checkoutForm->initialize();

    $allFields = $checkoutForm->getFields();

    expect($allFields)->toHaveKey('name')
        ->and($allFields['name']->label)->toEqual('Name')
        ->and($allFields['name']->type)->toEqual('text');
});
