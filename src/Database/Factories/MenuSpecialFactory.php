<?php

namespace Igniter\Cart\Database\Factories;

use Igniter\Cart\Models\Menu;
use Igniter\Flame\Database\Factories\Factory;

class MenuSpecialFactory extends Factory
{
    protected $model = \Igniter\Cart\Models\MenuSpecial::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'special_price' => $this->faker->randomFloat(),
            'type' => 'F',
            'validity' => 'forever',
            'special_status' => 1,
        ];
    }
}
