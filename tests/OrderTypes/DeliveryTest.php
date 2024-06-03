<?php

namespace Igniter\Cart\Tests\OrderTypes;

use Igniter\Cart\OrderTypes\Delivery;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;

beforeEach(function() {
    $location = LocationModel::factory()->create();
    $this->settings = $location->settings()->create([
        'item' => LocationModel::DELIVERY,
        'data' => ['lead_time' => 60],
    ]);
    $this->delivery = new Delivery($location, [
        'code' => LocationModel::DELIVERY, 'name' => 'Delivery',
    ]);
});

it('returns open description correctly', function() {
    expect($this->delivery->getOpenDescription())->toContain(60);
});

it('returns opening description correctly', function() {
    expect($this->delivery->getOpeningDescription('ddd hh:mm a'))->toContain('12:00 am');
});

it('returns closed description correctly', function() {
    expect($this->delivery->getClosedDescription())->toBe(sprintf(
        lang('igniter.cart::default.text_delivery_time_info'),
        lang('igniter.local::default.text_is_closed')
    ));
});

it('returns disabled description correctly', function() {
    expect($this->delivery->getDisabledDescription())->toBe(lang('igniter.local::default.text_delivery_is_disabled'));
});

it('returns active status correctly', function() {
    Location::shouldReceive('orderType')->once()->andReturn('delivery');

    expect($this->delivery->isActive())->toBeTrue();
});

it('returns disabled status correctly', function() {
    $this->settings->update([
        'item' => LocationModel::DELIVERY,
        'data' => ['is_enabled' => 0],
    ]);

    expect($this->delivery->isDisabled())->toBeTrue();
});
