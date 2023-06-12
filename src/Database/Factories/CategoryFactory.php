<?php

namespace Igniter\Cart\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = \Igniter\Cart\Models\Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomDigit(),
            'status' => $this->faker->boolean(),
        ];
    }
}
