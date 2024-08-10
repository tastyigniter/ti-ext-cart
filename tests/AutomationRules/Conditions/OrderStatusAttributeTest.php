<?php

use Igniter\Admin\Models\Status;
use Igniter\Automation\AutomationException;
use Igniter\Automation\Models\RuleCondition;
use Igniter\Cart\AutomationRules\Conditions\OrderStatusAttribute;

it('defines model attributes correctly', function() {
    $orderStatusAttribute = new OrderStatusAttribute;

    $attributes = $orderStatusAttribute->defineModelAttributes();

    expect($attributes)->toHaveKeys(['status_id', 'status_name', 'notify_customer']);
});

it('throws exception when no status in params', function() {
    $orderStatusAttribute = new OrderStatusAttribute;

    $this->expectException(AutomationException::class);

    $params = [];
    $orderStatusAttribute->isTrue($params);
});

it('evaluates isTrue correctly for status_id', function() {
    $status = Status::factory()->create();

    $orderStatusAttribute = new OrderStatusAttribute(new RuleCondition([
        'options' => [
            ['attribute' => 'status_id', 'operator' => 'is', 'value' => $status->getKey()],
        ],
    ]));

    $params = ['status' => $status];
    expect($orderStatusAttribute->isTrue($params))->toBeTrue();
});

it('evaluates isTrue correctly for status_name', function($attribute, $value) {
    $status = Status::factory()->create([$attribute => $value]);

    $orderStatusAttribute = new OrderStatusAttribute(new RuleCondition([
        'options' => [
            ['attribute' => $attribute, 'operator' => 'is', 'value' => $value],
        ],
    ]));

    $params = ['status' => $status];
    expect($orderStatusAttribute->isTrue($params))->toBeTrue();
})->with([
    ['status_name', 'Pending'],
    ['notify_customer', true],
]);
