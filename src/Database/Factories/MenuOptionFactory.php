<?php

namespace Igniter\Cart\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class MenuOptionFactory extends Factory
{
    protected $model = \Igniter\Cart\Models\MenuOption::class;

    public function definition(): array
    {
        return [
            'option_name' => $this->faker->word,
            'display_type' => $this->faker->randomElement(['select', 'radio', 'checkbox', 'quantity']),
        ];
    }
}
