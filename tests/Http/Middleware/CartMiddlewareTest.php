<?php

namespace Igniter\Cart\Tests\Http\Middleware;

use Igniter\Cart\Facades\Cart;
use Igniter\Cart\Http\Middleware\CartMiddleware;
use Igniter\Cart\Models\CartSettings;
use Igniter\Local\Contracts\LocationInterface;
use Igniter\Local\Facades\Location;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;

it('handles request with location', function() {
    $locationMock = Mockery::mock(LocationInterface::class);
    Location::shouldReceive('current')->andReturn($locationMock);
    Location::shouldReceive('getId')->andReturn(1);
    Cart::shouldReceive('instance')->with('location-1');

    $middleware = new CartMiddleware;
    $response = $middleware->handle(new Request, function() {
        return true;
    });

    expect($response)->toBeTrue();
});

it('handles request without location', function() {
    Location::shouldReceive('current')->andReturn(null);
    Cart::expects('instance')->never();

    $middleware = new CartMiddleware;
    $response = $middleware->handle(new Request, function() {
        return true;
    });

    expect($response)->toBeTrue();
});

it('terminates with abandoned cart and authenticated user', function() {
    CartSettings::set('abandoned_cart', true);

    $userMock = Mockery::mock(User::class);
    $userMock->shouldReceive('getKey')->andReturn(1);
    Auth::shouldReceive('check')->andReturn(true);
    Cart::shouldReceive('content')->andReturn(collect(['item']));
    Auth::shouldReceive('getUser')->andReturn($userMock);
    Cart::shouldReceive('store')->with(1);

    $middleware = new CartMiddleware;
    expect($middleware->terminate(new Request, new Response))->toBeNull();
});

it('terminates without abandoned cart', function() {
    CartSettings::set('abandoned_cart', false);
    Auth::expects('check')->never();
    Cart::expects('store')->never();

    $middleware = new CartMiddleware;
    expect($middleware->terminate(new Request, new Response))->toBeNull();
});

it('terminates with abandoned cart but unauthenticated user', function() {
    CartSettings::set('abandoned_cart', true);
    Auth::shouldReceive('check')->andReturn(false);
    Cart::expects('content')->never();

    $middleware = new CartMiddleware;
    expect($middleware->terminate(new Request, new Response))->toBeNull();
});

it('terminates with abandoned cart, authenticated user but empty cart', function() {
    CartSettings::set('abandoned_cart', true);
    Auth::shouldReceive('check')->andReturn(true);
    Cart::shouldReceive('content')->andReturn(collect());
    Cart::expects('store')->never();

    $middleware = new CartMiddleware;
    expect($middleware->terminate(new Request, new Response))->toBeNull();
});
