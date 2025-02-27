<?php

declare(strict_types=1);

namespace Igniter\Cart;

use Override;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use InvalidArgumentException;

class CartItemOptionValue implements Arrayable, Jsonable
{
    /**
     * The ID of the cart item option value.
     *
     * @var int|string
     */
    public $id;

    /**
     * The name of the cart item option value.
     *
     * @var string
     */
    public $name;

    /**
     * The quantity for this cart item option value.
     *
     * @var int|float
     */
    public $qty = 1;

    /**
     * The price of the cart item option value.
     *
     * @var float
     */
    public $price;

    /**
     * CartItem constructor.
     */
    public function __construct(int|string $id, string $name, float $price)
    {
        if ($id === 0 || strlen((string)$id) < 1) {
            throw new InvalidArgumentException('Please supply a valid cart item option value identifier.');
        }

        if (strlen($name) < 1) {
            throw new InvalidArgumentException('Please supply a valid cart item option value name.');
        }

        if ($price < 0) {
            throw new InvalidArgumentException('Please supply a valid cart item option value price.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * Returns the formatted price of this cart item option value
     */
    public function price(): float
    {
        return $this->price;
    }

    /**
     * Returns the subtotal.
     * Subtotal is price for whole CartItem with options
     */
    public function subtotal(): int|float
    {
        return $this->qty * $this->price;
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param int|float $qty
     */
    public function setQuantity($qty): void
    {
        if (!is_numeric($qty)) {
            throw new InvalidArgumentException('Please supply a valid item option quantity.');
        }

        $this->qty = $qty;
    }

    /**
     * Update the cart item option value from an array.
     */
    public function updateFromArray(array $attributes): void
    {
        $this->id = array_get($attributes, 'id', $this->id);
        $this->name = array_get($attributes, 'name', $this->name);
        $this->price = array_get($attributes, 'price', $this->price);
        $this->qty = array_get($attributes, 'qty', $this->qty);
    }

    /**
     * Create a new instance from the given array.
     */
    public static function fromArray(array $attributes): self
    {
        $instance = new self(
            $attributes['id'],
            $attributes['name'],
            $attributes['price'],
        );

        $instance->qty = array_get($attributes, 'qty', $instance->qty);

        return $instance;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    #[Override]
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'qty' => $this->qty,
            'price' => $this->price,
            'subtotal' => $this->subtotal(),
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    #[Override]
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
