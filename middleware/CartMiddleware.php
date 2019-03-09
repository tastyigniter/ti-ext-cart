<?php namespace Igniter\Cart\Middleware;

use Auth;
use Cart;

class CartMiddleware
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $this->storeUserCart();
    }

    protected function storeUserCart()
    {
        if (!config('cart.abandonedCart'))
            return;

        if (!Auth::check())
            return;

        if (Cart::content()->isEmpty())
            return;

        Cart::store(Auth::getUser()->getKey());
    }
}
