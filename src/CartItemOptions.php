<?php

namespace Igniter\Cart;

use Illuminate\Support\Collection;

class CartItemOptions extends Collection
{
    public function subtotal()
    {
        return $this->sum(function(CartItemOption $option) {
            return $option->subtotal();
        });
    }
}
