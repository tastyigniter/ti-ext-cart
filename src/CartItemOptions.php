<?php

declare(strict_types=1);

namespace Igniter\Cart;

use Illuminate\Support\Collection;

class CartItemOptions extends Collection
{
    public function subtotal()
    {
        return $this->sum(function(CartItemOption $option): float|int {
            return $option->subtotal();
        });
    }
}
