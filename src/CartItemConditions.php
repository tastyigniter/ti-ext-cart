<?php

declare(strict_types=1);

namespace Igniter\Cart;

use Illuminate\Support\Collection;

class CartItemConditions extends Collection
{
    public function apply($price, CartItem $cartItem)
    {
        return $this
            ->reduce(function($total, CartCondition $condition) use ($cartItem) {
                return $condition->withTarget($cartItem)->calculate($total);
            }, $price);
    }
}
