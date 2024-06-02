<?php

namespace Igniter\Cart\Tests\Http\Middleware;

use Igniter\Cart\Facades\Cart;
use Igniter\Cart\Http\Middleware\CartMiddleware;
use Igniter\Local\Contracts\LocationInterface;
use Igniter\Local\Facades\Location;
use Igniter\User\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;

it('handles request with location', function() {
    $locationMock = Mockery::mock(LocationInterface::class);
    Location::shouldReceive('current')->andReturn($locationMock);
    Location::shouldReceive('getId')->andReturn(1);
    Cart::shouldReceive('instance')->with('location-1');

    $middleware = new CartMiddleware();
    $response = $middleware->handle(new Request(), function() {
        return true;
    });

    expect($response)->toBeTrue();
});

it('handles request without location', function() {
    Location::shouldReceive('current')->andReturn(null);
    Cart::expects('instance')->never();

    $middleware = new CartMiddleware();
    $response = $middleware->handle(new Request(), function() {
        return true;
    });

    expect($response)->toBeTrue();
});

it('terminates with abandoned cart and authenticated user', function() {
    config(['igniter-cart.abandonedCart' => true]);
    Auth::shouldReceive('check')->andReturn(true);
    Cart::shouldReceive('content')->andReturn(collect(['item']));
    Auth::shouldReceive('getUser')->andReturn((object)['getKey' => 1]);
    Cart::shouldReceive('store')->with(1);

    $middleware = new CartMiddleware();
    expect($middleware->terminate(new Request(), new Response()))->toBeNull();
});

it('terminates without abandoned cart', function() {
    config(['igniter-cart.abandonedCart' => false]);
    Auth::expects('check')->never();
    Cart::expects('store')->never();

    $middleware = new CartMiddleware();
    expect($middleware->terminate(new Request(), new Response()))->toBeNull();
});

it('terminates with abandoned cart but unauthenticated user', function() {
    config(['igniter-cart.abandonedCart' => true]);
    Auth::shouldReceive('check')->andReturn(false);
    Cart::expects('content')->never();

    $middleware = new CartMiddleware();
    expect($middleware->terminate(new Request(), new Response()))->toBeNull();
});

it('terminates with abandoned cart, authenticated user but empty cart', function() {
    config(['igniter-cart.abandonedCart' => true]);
    Auth::shouldReceive('check')->andReturn(true);
    Cart::shouldReceive('content')->andReturn(collect());
    Cart::expects('store')->never();

    $middleware = new CartMiddleware();
    expect($middleware->terminate(new Request(), new Response()))->toBeNull();
});
