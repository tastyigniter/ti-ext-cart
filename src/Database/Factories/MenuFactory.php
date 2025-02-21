<?php

declare(strict_types=1);

namespace Igniter\Cart\Database\Factories;

use Igniter\Cart\Models\Menu;
use Igniter\Flame\Database\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'menu_name' => $this->faker->sentence(2),
            'menu_price' => $this->faker->randomFloat(null, 0, 100),
            'minimum_qty' => 1,
            'menu_status' => 1,
        ];
    }
}
