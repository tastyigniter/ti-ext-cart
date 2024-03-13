<?php

namespace Igniter\Cart\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = \Igniter\Cart\Models\Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => $this->faker->randomNumber(8),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'telephone' => $this->faker->phoneNumber,
            'location_id' => $this->faker->randomNumber(8),
            'address_id' => $this->faker->randomNumber(8),
            'total_items' => $this->faker->randomNumber(2),
            'cart' => '',
            'comment' => $this->faker->sentence,
            'payment' => 'Cash',
            'order_type' => 'delivery',
            'order_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'order_time' => $this->faker->time(),
            'order_total' => $this->faker->randomFloat(2, 10, 100),
            'status_id' => $this->faker->randomNumber(8),
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
        ];
    }
}
