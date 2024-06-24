<?php

namespace Igniter\Cart\Concerns;

trait ActsAsItemable
{
    /**
     * Get the instance to apply on a cart item.
     *
     * @param \Igniter\Cart\CartItem $cartItem
     * @return static
     */
    public function toItem()
    {
        return new static($this->toArray());
    }

    public static function isApplicableTo($cartItem) {}
}
