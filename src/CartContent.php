<?php

declare(strict_types=1);

namespace Igniter\Cart;

use Illuminate\Support\Collection;

class CartContent extends Collection
{
    public function quantity()
    {
        return $this->sum('qty');
    }

    public function subtotal()
    {
        return $this->sum(function(CartItem $cartItem) {
            return $cartItem->subtotal();
        });
    }

    public function subtotalWithoutConditions()
    {
        return $this->sum(function(CartItem $cartItem): float|int {
            return $cartItem->subtotalWithoutConditions();
        });
    }
}
