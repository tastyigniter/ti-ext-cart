<?php

namespace Igniter\Cart\Tests;

use Igniter\Cart\CartCondition;
use Igniter\Cart\CartContent;
use InvalidArgumentException;

it('validates rules correctly', function() {
    $traitObject = new class extends CartCondition
    {
        public function testValidate()
        {
            return $this->validate($this->getRules());
        }

        public function getRules()
        {
            return [
                '10 = 10',
                '10 == 10',
                '10 != 0',
                '0 < 10',
                '10 <= 10',
                '10 > 0',
                '10 >= 10',
                '10 ! 10',
            ];
        }
    };

    $valid = $traitObject->testValidate();

    expect($valid)->toBeFalse()
        ->and($traitObject->whenInvalid())->toBeNull();
});

it('calculates action value correctly', function() {
    $traitObject = new class extends CartCondition
    {
        public $testValue = 10;

        public function testProcessValue($subtotal)
        {
            return $this->processValue($subtotal);
        }

        public function getActions()
        {
            return [
                ['value' => '-10'],
                ['value' => '+10'],
                ['value' => '*10'],
                ['value' => '/10'],
                ['inclusive' => true, 'value' => '%10'],
                ['multiplier' => 'subtotal', 'value' => '0'],
                ['multiplier' => 'testValue', 'value' => '0'],
                ['multiplier' => 10, 'value' => '0'],
                ['max' => 10, 'value' => '+10'],
                [],
            ];
        }
    };

    $targetMock = mock(CartContent::class);
    $targetMock->shouldReceive('subtotal')->andReturn(10);
    $traitObject->setCartContent($targetMock);

    $total = $traitObject->testProcessValue(20);

    expect($total)->toEqual(10)
        ->and($traitObject->getCartContent())->toBe($targetMock);

    // For test coverage
    $traitObject->setMetaData('name', ['label' => 'Test Condition']);

    expect($traitObject->toJson())->toContain('Test Condition');

    $traitObject->removeMetaData();
});

it('throws exception when parse rule fails', function() {
    $traitObject = new class extends CartCondition
    {
        public function testParseRule($rule)
        {
            return $this->parseRule($rule);
        }
    };

    expect(fn() => $traitObject->testParseRule('20 === 20'))->toThrow(InvalidArgumentException::class);
});

it('throws exception when parse action fails', function() {
    $traitObject = new class extends CartCondition
    {
        public function testProcessValue($subtotal)
        {
            return $this->processValue($subtotal);
        }

        public function getActions()
        {
            return [['inclusive' => true]];
        }
    };

    expect(fn() => $traitObject->testProcessValue(20))->toThrow(InvalidArgumentException::class);
});
