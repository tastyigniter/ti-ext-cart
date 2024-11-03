<?php

namespace Igniter\Cart\Tests\Models\Observers;

use Igniter\Cart\Models\Observers\OrderObserver;
use Igniter\Cart\Models\Order;
use Mockery;

beforeEach(function() {
    $this->observer = new OrderObserver();
    $this->order = Mockery::mock(Order::class)->makePartial();
});

it('fills hash, ip_address, and user_agent when creating', function() {
    request()->headers->set('REMOTE_ADDR', '127.0.0.1');
    request()->headers->set('User-Agent', 'test-agent');

    $this->order->shouldReceive('generateHash')->andReturn('test-hash');

    $this->order->shouldReceive('forceFill')->with([
        'hash' => 'test-hash',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test-agent',
    ])->once();

    $this->observer->creating($this->order);
});

it('handles missing user agent gracefully', function() {
    request()->headers->set('REMOTE_ADDR', '127.0.0.1');
    request()->headers->set('User-Agent', null);

    $this->order->shouldReceive('generateHash')->andReturn('test-hash');
    $this->order->shouldReceive('forceFill')->with([
        'hash' => 'test-hash',
        'ip_address' => '127.0.0.1',
        'user_agent' => null,
    ])->once();

    $this->observer->creating($this->order);
});
