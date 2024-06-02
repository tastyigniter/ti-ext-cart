<?php

namespace Igniter\Cart\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class MenuOptionValueFactory extends Factory
{
    protected $model = \Igniter\Cart\Models\MenuOptionValue::class;

    public function definition(): array
    {
        return [
            'option_id' => $this->faker->randomNumber(8),
            'name' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
