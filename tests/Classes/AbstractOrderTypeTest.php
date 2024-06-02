<?php

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\Classes\AbstractOrderType;
use Igniter\Local\Classes\WorkingSchedule;
use Igniter\Local\Exceptions\WorkingHourException;
use Igniter\Local\Models\Location;

beforeEach(function () {
    $this->orderType = new class(new Location, ['code' => 'test', 'name' => 'Test']) extends AbstractOrderType {
        public function getOpenDescription(): string
        {
            // TODO: Implement getOpenDescription() method.
        }

        public function getOpeningDescription(string $format): string
        {
            // TODO: Implement getOpeningDescription() method.
        }

        public function getClosedDescription(): string
        {
            // TODO: Implement getClosedDescription() method.
        }

        public function getDisabledDescription(): string
        {
            // TODO: Implement getDisabledDescription() method.
        }

        public function isActive(): bool
        {
            // TODO: Implement isActive() method.
        }

        public function isDisabled(): bool
        {
            // TODO: Implement isDisabled() method.
        }
    };
});

it('gets code', function() {
    expect($this->orderType->getCode())->toBe('test');
});

it('gets label', function() {
    expect($this->orderType->getLabel())->toBe('Test');
});

it('gets interval', function() {
    expect($this->orderType->getInterval())->toBe(15);
});

it('gets lead time', function() {
    expect($this->orderType->getLeadTime())->toBe(15);
});

it('gets future days', function() {
    expect($this->orderType->getFutureDays())->toBe(0);
});

it('gets minimum future days', function() {
    expect($this->orderType->getMinimumFutureDays())->toBe(0);
});

it('gets minimum order total', function() {
    expect($this->orderType->getMinimumOrderTotal())->toBe(0.0);
});

it('gets schedule', function() {
    $this->expectException(WorkingHourException::class);

    expect($this->orderType->getSchedule())->toBeInstanceOf(WorkingSchedule::class);
});

it('gets schedule restriction', function() {
    expect($this->orderType->getScheduleRestriction())->toBe(0);
});
