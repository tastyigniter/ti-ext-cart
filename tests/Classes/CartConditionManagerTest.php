<?php

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\Classes\CartConditionManager;
use Igniter\Cart\Tests\Classes\Fixtures\TestCartCondition;

it('makes condition', function() {
    $manager = new CartConditionManager();
    $manager->registerCondition(TestCartCondition::class, [
        'name' => 'testCartCondition',
        'label' => 'Test Cart Condition',
    ]);

    $condition = $manager->makeCondition(TestCartCondition::class, ['label' => 'Override Test Cart Condition']);

    expect($condition)->toBeInstanceOf(TestCartCondition::class)
        ->and($condition->getLabel())->toBe('Override Test Cart Condition');
});

it('throws exception for unregistered condition', function() {
    $manager = new CartConditionManager();

    expect(fn() => $manager->makeCondition('NonExistentClass'))->toThrow(\LogicException::class);
});

it('lists registered conditions', function() {
    $manager = new CartConditionManager();
    $manager->registerCondition(TestCartCondition::class, [
        'name' => 'testCartCondition',
        'label' => 'Test Cart Condition',
    ]);

    $conditions = $manager->listRegisteredConditions();

    expect($conditions)->toBeArray()
        ->and($conditions)->toHaveKey(TestCartCondition::class);
});

it('loads registered conditions', function() {
    $manager = new CartConditionManager();
    $manager->registerCallback(function($manager) {
        $manager->registerCondition(TestCartCondition::class, [
            'name' => 'testCartCondition',
            'label' => 'Test Cart Condition',
        ]);
    });

    $conditions = $manager->listRegisteredConditions();

    expect($conditions)->toBeArray()
        ->and($conditions)->toHaveKey(TestCartCondition::class)
        ->and($conditions[TestCartCondition::class]['label'])->toBe('Test Cart Condition');
});

it('registers conditions', function() {
    $manager = new CartConditionManager();
    $manager->registerConditions([
        TestCartCondition::class => [
            'name' => 'testCartCondition',
            'label' => 'Test Cart Condition',
        ],
    ]);

    $conditions = $manager->listRegisteredConditions();

    expect($conditions)->toBeArray()
        ->and($conditions)->toHaveKey(TestCartCondition::class);
});
