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
        if (config('cart.abandonedCart'))
            $this->storeUserCart();
    }

    protected function storeUserCart()
    {
        if (!Auth::check())
            return;

        if (Cart::content()->isEmpty())
            return;

        Cart::store(Auth::getUser()->getKey());
    }
}
