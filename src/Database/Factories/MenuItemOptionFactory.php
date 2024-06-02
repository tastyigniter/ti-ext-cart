<?php

namespace Igniter\Cart\Database\Factories;

use Igniter\Cart\Models\MenuOption;
use Igniter\Flame\Database\Factories\Factory;

class MenuItemOptionFactory extends Factory
{
    protected $model = \Igniter\Cart\Models\MenuItemOption::class;

    public function definition(): array
    {
        return [
            'option_id' => MenuOption::factory(),
            'menu_id' => $this->faker->randomNumber(8),
            'is_required' => $this->faker->boolean,
        ];
    }
}
